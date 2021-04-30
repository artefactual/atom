<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @author     Mike G <mikeg@artefactual.com>
 */
class arFindingAidJob extends arBaseJob
{
    public const GENERATED_STATUS = 1;
    public const UPLOADED_STATUS = 2;

    /**
     * @see arBaseJob::$requiredParameters
     */
    protected $extraRequiredParameters = ['objectId'];

    private $resource;
    private $appRoot;

    public function runJob($parameters)
    {
        $this->resource = QubitInformationObject::getById($parameters['objectId']);

        // Check that object exists and that it is not the root
        if (!isset($this->resource) || !isset($this->resource->parent)) {
            $this->error($this->i18n->__('Error: Could not find an information object with id: %1', ['%1' => $parameters['objectId']]));

            return false;
        }

        Qubit::createDownloadsDirIfNeeded();

        if (isset($parameters['delete']) && $parameters['delete']) {
            $result = $this->delete();
        } elseif (isset($parameters['uploadPath'])) {
            $result = $this->upload($parameters['uploadPath']);
        } else {
            $result = $this->generate();
        }

        if (!$result) {
            return false;
        }

        $this->job->setStatusCompleted();
        $this->job->save();

        return true;
    }

    public static function getStatus($id)
    {
        $sql = 'SELECT j.status_id as statusId FROM
            job j JOIN object o ON j.id = o.id
            WHERE j.name = ? AND j.object_id = ?
            ORDER BY o.created_at DESC';

        $ret = QubitPdo::fetchOne($sql, [get_class(), $id]);

        return $ret ? (int) $ret->statusId : null;
    }

    public static function getPossibleFilenames($id)
    {
        $filenames = [
            $id.'.pdf',
            $id.'.rtf',
        ];

        if (null !== $slug = QubitSlug::getByObjectId($id)) {
            $filenames[] = $slug->slug.'.pdf';
            $filenames[] = $slug->slug.'.rtf';
        }

        return $filenames;
    }

    public static function getFindingAidPathForDownload($id)
    {
        foreach (self::getPossibleFilenames($id) as $filename) {
            $path = 'downloads'.DIRECTORY_SEPARATOR.$filename;

            if (file_exists(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$path)) {
                return $path;
            }
        }

        return null;
    }

    public static function getFindingAidPath($id)
    {
        if (null !== $slug = QubitSlug::getByObjectId($id)) {
            $filename = $slug->slug;
        }

        if (!isset($filename)) {
            $filename = $id;
        }

        return 'downloads'.DIRECTORY_SEPARATOR.$filename.'.'.self::getFindingAidFormat();
    }

    public static function getFindingAidFormat()
    {
        if (null !== $setting = QubitSetting::getByName('findingAidFormat')) {
            $format = $setting->getValue(['sourceCulture' => true]);
        }

        return isset($format) ? $format : 'pdf';
    }

    private function generate()
    {
        $this->info($this->i18n->__('Generating finding aid (%1)...', ['%1' => $this->resource->slug]));

        $this->appRoot = rtrim(sfConfig::get('sf_root_dir'), '/');

        $eadFileHandle = tmpfile();
        $foFileHandle = tmpfile();

        if (!$eadFileHandle || !$foFileHandle) {
            $this->error($this->i18n->__('Failed to create temporary file.'));

            return false;
        }

        $eadFilePath = $this->getTmpFilePath($eadFileHandle);
        $foFilePath = $this->getTmpFilePath($foFileHandle);

        unlink($eadFilePath);

        $public = '';
        if (
            (null !== $setting = QubitSetting::getByName('publicFindingAid'))
            && $setting->getValue(['sourceCulture' => true])
        ) {
            $public = '--public';
        }

        // Call generate EAD task
        $slug = $this->resource->slug;
        $output = [];
        exec(PHP_BINARY." {$this->appRoot}/symfony export:bulk --single-slug=\"{$slug}\" {$public} {$eadFilePath} 2>&1", $output, $exitCode);

        if ($exitCode > 0) {
            $this->error($this->i18n->__('Exporting EAD has failed.'));
            $this->logCmdOutput($output, 'ERROR(EAD-EXPORT)');

            return false;
        }

        // Use XSL file selected in Finding Aid model setting
        $findingAidModel = 'inventory-summary';
        if (null !== $setting = QubitSetting::getByName('findingAidModel')) {
            $findingAidModel = $setting->getValue(['sourceCulture' => true]);
        }

        $eadXslFilePath = $this->appRoot.'/lib/task/pdf/ead-pdf-'.$findingAidModel.'.xsl';
        $saxonPath = $this->appRoot.'/lib/task/pdf/saxon9he.jar';

        // Crank the XML through XSL stylesheet and fix header / fonds URL
        $eadFileString = file_get_contents($eadFilePath);
        $eadFileString = $this->fixHeader($eadFileString, sfConfig::get('app_site_base_url', null));
        file_put_contents($eadFilePath, $eadFileString);

        // Replace {{ app_root }} placeholder var with the $this->appRoot value, and
        // return the temp XSL file path for Saxon processing
        $xslTmpPath = $this->renderXsl(
            $eadXslFilePath,
            ['app_root' => $this->appRoot]
        );

        // Transform EAD file with Saxon
        $pdfPath = sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.self::getFindingAidPath($this->resource->id);
        $cmd = sprintf("java -jar '%s' -s:'%s' -xsl:'%s' -o:'%s' 2>&1", $saxonPath, $eadFilePath, $xslTmpPath, $foFilePath);
        $this->info(sprintf('Running: %s', $cmd));
        $output = [];
        exec($cmd, $output, $exitCode);

        if ($exitCode > 0) {
            $this->error($this->i18n->__('Transforming the EAD with Saxon has failed.'));
            $this->logCmdOutput($output, 'ERROR(SAXON)');

            return false;
        }

        // Use FOP generated in previous step to generate PDF
        $cmd = sprintf("fop -r -q -fo '%s' -%s '%s' 2>&1", $foFilePath, self::getFindingAidFormat(), $pdfPath);
        $this->info(sprintf('Running: %s', $cmd));
        $output = [];
        exec($cmd, $output, $exitCode);

        if (0 != $exitCode) {
            $this->error($this->i18n->__('Converting the EAD FO to PDF has failed.'));
            $this->logCmdOutput($output, 'ERROR(FOP)');

            return false;
        }

        // Update or create 'findingAidStatus' property
        $criteria = new Criteria();
        $criteria->add(QubitProperty::OBJECT_ID, $this->resource->id);
        $criteria->add(QubitProperty::NAME, 'findingAidStatus');

        if (null === $property = QubitProperty::getOne($criteria)) {
            $property = new QubitProperty();
            $property->objectId = $this->resource->id;
            $property->name = 'findingAidStatus';
        }

        $property->setValue(self::GENERATED_STATUS, ['sourceCulture' => true]);
        $property->indexOnSave = false;
        $property->save();

        // Update ES document with finding aid status
        $partialData = [
            'findingAid' => [
                'status' => self::GENERATED_STATUS,
            ],
        ];

        QubitSearch::getInstance()->partialUpdate($this->resource, $partialData);

        $this->info($this->i18n->__('Finding aid generated successfully: %1', ['%1' => $pdfPath]));

        fclose($eadFileHandle); // Will delete the tmp file
        fclose($foFileHandle);

        return true;
    }

    private function renderXsl($filename, $vars)
    {
        // Get XSL file contents
        $content = file_get_contents($filename);

        // Replace placeholder vars (e.g. "{{ app_root }}")
        foreach ($vars as $key => $val) {
            $content = str_replace("{{ {$key} }}", $val, $content);
        }

        // Write contents to temp file for processing with Saxon
        $tmpFilePath = tempnam(sys_get_temp_dir(), 'ATM');
        file_put_contents($tmpFilePath, $content);

        return $tmpFilePath;
    }

    private function upload($path)
    {
        $this->info($this->i18n->__('Uploading finding aid (%1)...', ['%1' => $this->resource->slug]));

        // Update or create 'findingAidStatus' property
        $criteria = new Criteria();
        $criteria->add(QubitProperty::OBJECT_ID, $this->resource->id);
        $criteria->add(QubitProperty::NAME, 'findingAidStatus');

        if (null === $property = QubitProperty::getOne($criteria)) {
            $property = new QubitProperty();
            $property->objectId = $this->resource->id;
            $property->name = 'findingAidStatus';
        }

        $property->setValue(self::UPLOADED_STATUS, ['sourceCulture' => true]);
        $property->indexOnSave = false;
        $property->save();

        $partialData = [
            'findingAid' => [
                'transcript' => null,
                'status' => self::UPLOADED_STATUS,
            ],
        ];

        $this->info($this->i18n->__('Finding aid uploaded successfully: %1', ['%1' => $path]));

        // Extract finding aid transcript
        $mimeType = 'application/'.self::getFindingAidFormat();

        if (!QubitDigitalObject::canExtractText($mimeType)) {
            $message = $this->i18n->__('Could not obtain finding aid text.');
            $this->job->addNoteText($message);
            $this->info($message);
        } else {
            $this->info($this->i18n->__('Obtaining finding aid text...'));

            $command = sprintf('pdftotext %s - 2> /dev/null', $path);
            exec($command, $output, $status);

            if (0 != $status) {
                $message = $this->i18n->__('Obtaining the text has failed.');
                $this->job->addNoteText($message);
                $this->info($message);
                $this->logCmdOutput($output, 'WARNING(PDFTOTEXT)');
            } elseif (0 < count($output)) {
                $text = implode(PHP_EOL, $output);

                // Truncate PDF text to <64KB to fit in `property.value` column
                $text = mb_strcut($text, 0, 65535);

                // Update or create 'findingAidTranscript' property
                $criteria = new Criteria();
                $criteria->add(QubitProperty::OBJECT_ID, $this->resource->id);
                $criteria->add(QubitProperty::NAME, 'findingAidTranscript');
                $criteria->add(QubitProperty::SCOPE, 'Text extracted from finding aid PDF file text layer using pdftotext');

                if (null === $property = QubitProperty::getOne($criteria)) {
                    $property = new QubitProperty();
                    $property->objectId = $this->resource->id;
                    $property->name = 'findingAidTranscript';
                    $property->scope = 'Text extracted from finding aid PDF file text layer using pdftotext';
                }

                $property->setValue($text, ['sourceCulture' => true]);
                $property->indexOnSave = false;
                $property->save();

                // Update partial data with transcript
                $partialData['findingAid']['transcript'] = $text;
            }
        }

        // Update ES document with finding aid status and transcript
        QubitSearch::getInstance()->partialUpdate($this->resource, $partialData);

        return true;
    }

    private function delete()
    {
        $this->info($this->i18n->__('Deleting finding aid (%1)...', ['%1' => $this->resource->slug]));

        foreach (self::getPossibleFilenames($this->resource->id) as $filename) {
            $path = sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.'downloads'.DIRECTORY_SEPARATOR.$filename;

            if (file_exists($path)) {
                unlink($path);
            }
        }

        // Delete 'findingAidTranscript' property if it exists
        $criteria = new Criteria();
        $criteria->add(QubitProperty::OBJECT_ID, $this->resource->id);
        $criteria->add(QubitProperty::NAME, 'findingAidTranscript');
        $criteria->add(QubitProperty::SCOPE, 'Text extracted from finding aid PDF file text layer using pdftotext');

        if (null !== $property = QubitProperty::getOne($criteria)) {
            $this->info($this->i18n->__('Deleting finding aid transcript...'));

            $property->indexOnDelete = false;
            $property->delete();
        }

        // Delete 'findingAidStatus' property if it exists
        $criteria = new Criteria();
        $criteria->add(QubitProperty::OBJECT_ID, $this->resource->id);
        $criteria->add(QubitProperty::NAME, 'findingAidStatus');

        if (null !== $property = QubitProperty::getOne($criteria)) {
            $property->indexOnDelete = false;
            $property->delete();
        }

        // Update ES document removing finding aid status and transcript
        $partialData = [
            'findingAid' => [
                'transcript' => null,
                'status' => null,
            ],
        ];

        QubitSearch::getInstance()->partialUpdate($this->resource, $partialData);

        $this->info($this->i18n->__('Finding aid deleted successfully.'));

        return true;
    }

    private function logCmdOutput(array $output, $prefix = null)
    {
        if (empty($prefix)) {
            $prefix = 'ERROR: ';
        } else {
            $prefix = $prefix.': ';
        }

        foreach ($output as $line) {
            $this->error($prefix.$line);
        }
    }

    private function fixHeader($xmlString, $url = null)
    {
        // Apache FOP requires certain namespaces to be included in the XML in order to process it.
        $xmlString = preg_replace(
            '(<ead .*?>|<ead>)', '<ead xmlns:ns2="http://www.w3.org/1999/xlink" '
            .'xmlns="urn:isbn:1-931666-22-9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">',
            $xmlString,
            1
        );

        // TODO: Use new base url functionality in AtoM instead of doing this kludge
        if (null !== $url) {
            // Since we call the EAD generation from inside Symfony and not as part as a web request,
            // the url was returning symfony://weirdurlhere. We can get around this by passing the referring url into
            // the job as an option when the user clicks 'generate' and replace the url in the EAD manually.
            $xmlString = preg_replace('/<eadid(.*?)url=\".*?\"(.*?)>/', '<eadid$1url="'.$url.'"$2>', $xmlString, 1);
        }

        return $xmlString;
    }

    private function getTmpFilePath($handle)
    {
        $meta_data = stream_get_meta_data($handle);

        return $meta_data['uri'];
    }
}
