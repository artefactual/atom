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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.    See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Generate a Finding Aid.
 *
 * @author David Juhasz <djjuhasz@gmail.com>
 */
class QubitFindingAidGenerator
{
    public const XML_STANDARD = 'ead';

    protected const OASIS_CATALOG_PATH = 'data/xml/catalog.xml';
    protected const RESOLVER_PATH = 'vendor/resolver.jar';
    protected const SAXON_PATH = 'vendor/saxon-he-10.6.jar';

    protected $authLevel;
    protected $appRoot;
    protected $format;
    protected $logger;
    protected $resource;
    protected $options;
    protected $path;

    // Valid authorization levels
    protected static $authLevels = [
        'public',
        'private',
    ];

    // Valid finding aid file formats
    protected static $formats = [
        'pdf',
        'rtf',
    ];

    // Valid finding aid "models"
    protected static $models = [
        'inventory-summary',
        'full-details',
    ];

    // Default options
    protected static $defaults = [
        'appRoot' => null,
        'authLevel' => 'public',
        'format' => 'pdf',
        'logger' => null,
        'model' => 'inventory-summary',
    ];

    public function __construct(
        QubitInformationObject $resource,
        ?array $options = []
    ) {
        $this->setResource($resource);

        // Fill options array with default values if explicit $options not
        // passed
        $options = array_merge(self::$defaults, $options);

        $this->setAppRoot($options['appRoot']);
        $this->setAuthLevel($options['authLevel']);
        $this->setFormat($options['format']);
        $this->setLogger($options['logger']);
        $this->setModel($options['model']);
    }

    /**
     * Set the finding aid's primary (top-level) information object.
     */
    public function setResource(QubitInformationObject $resource): void
    {
        // Make sure $resource is not the QubitInformationObject root
        if (QubitInformationObject::ROOT_ID == $resource->id) {
            throw new UnexpectedValueException(
                sprintf(
                    'Invalid QubitInformationObject id: %s',
                    QubitInformationObject::ROOT_ID
                )
            );
        }

        $this->resource = $resource;
    }

    /**
     * Get the finding aid's primary information object.
     */
    public function getResource(): QubitInformationObject
    {
        return $this->resource;
    }

    /**
     * Set a logger, or use the default symfony logger.
     */
    public function setLogger(?sfLogger $logger): void
    {
        // Use the passed logger
        if (!empty($logger)) {
            $this->logger = $logger;

            return;
        }

        // Or default to the configured symfony logger
        $this->logger = sfContext::getInstance()->getLogger();
    }

    /**
     * Get the current logger.
     */
    public function getLogger(): ?sfLogger
    {
        return $this->logger;
    }

    /**
     * Validate $value against list of $allowedValues.
     */
    public function validateSetting(string $value, array $allowedValues): bool
    {
        if (!in_array($value, $allowedValues)) {
            throw new UnexpectedValueException(
                sprintf(
                    'Invalid value "%s", must be one of (%s)',
                    $value,
                    implode(', ', $allowedValues)
                )
            );
        }

        return true;
    }

    /**
     * Set the expected authorization level of the finding aid viewer.
     *
     * - "public" authorization hides some sensitive data
     * - "private" authorization includes all data
     */
    public function setAuthLevel(string $level): void
    {
        $this->validateSetting($level, self::$authLevels);
        $this->authLevel = $level;
    }

    /**
     * Get the authorization level.
     */
    public function getAuthLevel(): string
    {
        return $this->authLevel;
    }

    /**
     * Set the application root directory, or use symfony's sf_root_dir value.
     */
    public function setAppRoot(?string $appRoot): void
    {
        // If $appRoot is set, use the passed value
        if (!empty($appRoot)) {
            $this->appRoot = $appRoot;

            return;
        }

        // Default to the symfony sf_root_dir config setting, if not explicitly
        // set
        $this->appRoot = rtrim(sfConfig::get('sf_root_dir'), '/');
    }

    /**
     * Get the application root directory.
     */
    public function getAppRoot(): string
    {
        return $this->appRoot;
    }

    /**
     * Set the finding aid format (rtf, pdf).
     */
    public function setFormat(string $format): void
    {
        $this->validateSetting($format, self::$formats);
        $this->format = $format;
    }

    /**
     * Get the finding aid format.
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Set the finding aid model.
     */
    public function setModel(string $model): void
    {
        $this->validateSetting($model, self::$models);
        $this->model = $model;
    }

    /**
     * Get the finding aid model.
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Check if XML caching is enabled.
     */
    public function isXmlCachingEnabled(): bool
    {
        return sfConfig::get('app_cache_xml_on_save', false);
    }

    /**
     * Generate the finding aid document.
     */
    public function generate()
    {
        // Delete existing finding aid linked to this resource
        $findingAid = new QubitFindingAid($this->resource);

        if (null !== $findingAid->getPath()) {
            $this->logger->info(
                sprintf(
                    'Deleting existing finding aid (%s)',
                    $findingAid->getPath()
                )
            );

            $findingAid->delete();
        }

        $this->logger->info(
            sprintf(
                'Generating a %s finding aid (%s)...',
                $this->getAuthLevel(),
                $this->resource->slug
            )
        );

        // Ensure 'downloads' directory exists
        Qubit::createDownloadsDirIfNeeded();

        // Get the path to this resource's cached EAD file, if caching is
        // enabled
        $eadFilePath = $this->getEadCacheFilePath();

        // Generate an EAD file, if there is no cached EAD file
        if (empty($eadFilePath)) {
            $eadFilePath = $this->generateEadFile();
        }

        $foFilePath = $this->generateXslFoFile($eadFilePath);
        $this->path = $this->generateFindingAid($foFilePath);

        $findingAid = new QubitFindingAid($this->resource);
        $findingAid->setLogger($this->logger);
        $findingAid->setPath($this->path);
        $findingAid->setStatus(QubitFindingAid::GENERATED_STATUS);
        $findingAid->save();

        $this->logger->info(
            sprintf('Finding aid generated successfully: %s', $this->path)
        );

        // Delete temporary files
        unlink($foFilePath);

        // Only delete the EAD file if XML caching is disabled, so we don't
        // delete the cache file
        if (!$this->isXmlCachingEnabled()) {
            unlink($eadFilePath);
        }

        return $findingAid;
    }

    /**
     * Generate and cache an XML representation of $this->resource.
     */
    public function generateEadCacheFile(): string
    {
        $cacher = new QubitInformationObjectXmlCache(
            ['logger' => $this->logger]
        );

        $cacher->export($this->resource, self::XML_STANDARD);

        // Return cached file path
        return $cacher::resourceExportFilePath(
            $this->resource, self::XML_STANDARD
        );
    }

    /**
     * Get the path to the cached XML file for this resource.
     *
     * @return null|string path to cache file, null if there is none
     */
    public function getEadCacheFilePath(): ?string
    {
        // If XML caching is disabled, then don't look for a cache file
        if (!$this->isXmlCachingEnabled()) {
            return null;
        }

        $filepath = QubitInformationObjectXmlCache::resourceExportFilePath(
            $this->resource, self::XML_STANDARD
        );

        if (!empty($filepath) && file_exists($filepath)) {
            return $filepath;
        }

        // If a cached file doesn't exist already, attempt to generate one and
        // return the file path
        return $this->generateEadCacheFile();
    }

    /**
     * Get the correct XLST file path for the current Finding Aid model.
     */
    public function getXslFilePath(): string
    {
        return $this->getAppRoot().
            sprintf('/lib/task/pdf/ead-pdf-%s.xsl', $this->getModel());
    }

    /**
     * Get the absolute path to the OASIS XML catalog file.
     */
    public function getCatalogPath(): string
    {
        return $this->getAppRoot().'/'.self::OASIS_CATALOG_PATH;
    }

    /**
     * Get the absolute path to the XML-Commons resolver.jar.
     *
     * The XML-Commons resolver.jar is used by Saxon when generating finding
     * aids to resolve DTD and schema URIs to local files using an OASIS XML
     * Catalog.
     */
    public function getResolverPath(): string
    {
        return $this->getAppRoot().'/'.self::RESOLVER_PATH;
    }

    /**
     * Get the absolute path to the Saxon XLST and XQuery Processor jar.
     */
    public function getSaxonPath(): string
    {
        return $this->getAppRoot().'/'.self::SAXON_PATH;
    }

    /**
     * Generate an EAD XML file and return the file path.
     *
     * @return string Generated EAD XML file path
     */
    public function generateEadFile(): string
    {
        exportBulkBaseTask::includeXmlExportClassesAndHelpers();

        $options = [
            'public' => ('public' === $this->getAuthLevel()),
        ];

        try {
            // Print warnings/notices here too, as they are often important.
            $errLevel = error_reporting(E_ALL);

            $rawXml = exportBulkBaseTask::captureResourceExportTemplateOutput(
                $this->resource, 'ead', $options
            );
            $xml = Qubit::tidyXml($rawXml);

            error_reporting($errLevel);
        } catch (Exception $e) {
            throw new sfException($this->i18n->__(
                "Error generating EAD XML for '%1%.'",
                ['%1%' => $this->resource->slug]
            ));
        }

        $filename = exportBulkBaseTask::generateSortableFilename(
            $this->resource, 'xml', 'ead'
        );

        $filepath = sys_get_temp_dir().DIRECTORY_SEPARATOR.$filename;

        if (false === file_put_contents($filepath, $xml)) {
            throw new sfException(
                sprintf(
                    'ERROR (EAD-EXPORT): Couldn\'t write file: "%s"',
                    $filepath
                )
            );
        }

        // Log successful EAD generation
        $this->logger->info(sprintf('Generated EAD file "%s"', $filepath));

        return $filepath;
    }

    /**
     * Apache FOP requires certain namespaces to be included in the XML in order
     * to process it.
     */
    public function addEadNamespaces(string $filename, ?string $url = null): void
    {
        $content = file_get_contents($filename);

        $eadHeader = <<<'EOL'
<ead xmlns:ns2="http://www.w3.org/1999/xlink" xmlns="urn:isbn:1-931666-22-9"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
EOL;

        $content = preg_replace('(<ead .*?>|<ead>)', $eadHeader, $content, 1);

        file_put_contents($filename, $content);
    }

    /**
     * Generate an XSL-FO file and return the file path.
     *
     * @return string path to the generated file
     */
    public function generateXslFoFile(string $eadFilePath): string
    {
        // Replace {{ app_root }} placeholder var with the appRoot value, and
        // return the temp XSL file path for Saxon processing
        $xslTmpPath = $this->renderXsl(
            $this->getXslFilePath(),
            ['app_root' => $this->getAppRoot()]
        );

        // Add required namespaces to EAD header
        $this->addEadNamespaces($eadFilePath);

        // Get a temporary file path for the FO file
        $foFilePath = tempnam(sys_get_temp_dir(), 'ATM');

        $cmd = sprintf(
            "java -cp '%s:%s' net.sf.saxon.Transform -s:'%s' -xsl:'%s' -o:'%s' -catalog:'%s' 2>&1",
            $this->getSaxonPath(),
            $this->getResolverPath(),
            $eadFilePath,
            $xslTmpPath,
            $foFilePath,
            $this->getCatalogPath(),
        );

        $this->logger->info(sprintf('Running: %s', $cmd));

        exec($cmd, $output, $exitCode);

        if ($exitCode > 0) {
            $this->logger->err(
                'ERROR(SAXON): Transforming the EAD with Saxon has failed.'
            );

            throw new Exception('ERROR(SAXON): '.implode("\n", $output));
        }

        return $foFilePath;
    }

    /**
     * Replace XSL template variables at run time.
     *
     * @param string $filename path to XSL template file
     * @param array  $vars     template variables to replace
     *
     * @return string path to rendered XSL file
     */
    public function renderXsl(string $filename, array $vars): string
    {
        // Get XSL file contents
        $content = file_get_contents($filename);

        // Replace placeholder vars (e.g. "{{ app_root }}")
        foreach ($vars as $key => $val) {
            $content = str_replace("{{ {$key} }}", $val, $content);
        }

        // Write contents to temp file for processing with Saxon
        $tmpFilePath = tempnam(sys_get_temp_dir(), 'ATM');

        $this->logger->info(
            sprintf(
                "Rendering XSL template '%s' to '%s'",
                $filename,
                $tmpFilePath
            )
        );

        file_put_contents($tmpFilePath, $content);

        return $tmpFilePath;
    }

    /**
     * Generate the finding aid document with Apache FOP.
     *
     * @param string $foFilePath FO file path
     *
     * @return string Finding Aid path
     */
    public function generateFindingAid(string $foFilePath): string
    {
        $findingAidPath = sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.
            self::generatePath($this->resource, $this->getFormat());

        // Use FO file generated in previous step to generate finding aid
        $cmd = sprintf(
            "fop -r -q -fo '%s' -%s '%s' 2>&1",
            $foFilePath, $this->getFormat(),
            $findingAidPath
        );

        $this->logger->info(sprintf('Running: %s', $cmd));

        $output = [];

        exec($cmd, $output, $exitCode);

        if (0 != $exitCode) {
            $this->logger->err(
                sprintf(
                    'Converting the XSL-FO document to a %s file has failed.',
                    $this->getFormat()
                )
            );

            throw new Exception('ERROR(FOP): '.implode("\n", $output));
        }

        return $findingAidPath;
    }

    /**
     * Generate a path and file name for a finding aid for $resource.
     *
     * @return string file path for finding aid
     */
    public static function generatePath(
        QubitInformationObject $resource,
        ?string $format = null
    ): string {
        if (null === $format) {
            $format = self::getFormatSetting();
        }

        if (!in_array($format, self::$formats)) {
            throw sfException(
                sprintf(
                    "Invalid format '%s', must be one of (%s).",
                    $format,
                    self::$formats
                )
            );
        }

        $filename = $resource->slug;

        if (empty($filename)) {
            $filename = $resource->id;
        }

        return sprintf(
            'downloads'.DIRECTORY_SEPARATOR.'%s.%s',
            $filename,
            $format
        );
    }

    /**
     * Get the 'public finding aid' setting.
     *
     * @return string 'public' (default) or 'private' authorization level
     */
    public static function getPublicSetting(): string
    {
        $authLevel = 'public';
        $setting = QubitSetting::getByName('publicFindingAid');

        if (
            isset($setting)
            && '0' === $setting->getValue(['sourceCulture' => true])
        ) {
            $authLevel = 'private';
        }

        return $authLevel;
    }

    /**
     * Get the system setting for the format used to generate finding aids.
     *
     * @return string 'pdf' (default) or 'rtf' file format
     */
    public static function getFormatSetting(): string
    {
        if (null !== $setting = QubitSetting::getByName('findingAidFormat')) {
            $value = $setting->getValue(['sourceCulture' => true]);
        }

        return isset($value) ? $value : 'pdf';
    }

    /**
     * Get the system setting for the model used to generate finding aids.
     *
     * @return string 'inventory-summary' (default) or 'full-details'
     */
    public static function getModelSetting(): string
    {
        if (null !== $setting = QubitSetting::getByName('findingAidModel')) {
            $value = $setting->getValue(['sourceCulture' => true]);
        }

        return isset($value) ? $value : 'inventory-summary';
    }
}
