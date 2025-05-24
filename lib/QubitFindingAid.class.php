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
 * Manage Finding Aid documents.
 *
 * @author David Juhasz <djjuhasz@gmail.com>
 */
class QubitFindingAid
{
    public const GENERATED_STATUS = 1;
    public const UPLOADED_STATUS = 2;

    private $format;
    private $homeDir;
    private $logger;
    private $resource;
    private $options;
    private $path;
    private $status;
    private $transcript;

    // Default options
    private $defaults = [
        'logger' => null,
    ];

    public function __construct(
        QubitInformationObject $resource,
        array $options = []
    ) {
        $this->setResource($resource);

        // Fill options array with default values if explicit $options not
        // passed
        $options = array_merge($this->defaults, $options);

        // Set logger (use default symfony logger if $option['logger'] is not
        // set)
        $this->setLogger($options['logger']);

        // Set Finding Aid home directory
        isset($options['homeDir']) ? $this->setHomeDir($options['homeDir']) : $this->setHomeDir(null);
    }

    /**
     * Set the finding aid's primary (top-level) information object.
     */
    public function setResource(QubitInformationObject $resource): void
    {
        // Make sure $resource is not the QubitInformationObject root
        if (QubitInformationObject::ROOT_ID === $resource->id) {
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
     * Set the home directory for finding aid files.
     */
    public function setHomeDir(?string $dir): void
    {
        if (isset($dir)) {
            $this->homeDir = $dir;

            return;
        }

        // Default finding aid home directory
        $this->homeDir = sfConfig::get('sf_web_dir').'/downloads/';
    }

    /**
     * Get the home directory for finding aid files.
     *
     * @param bool $absolute return absolute path if true (default), relative
     *                       path if false
     */
    public function getHomeDir(?bool $absolute = true): string
    {
        // Return absolute (filesystem) path
        if (true === $absolute) {
            return $this->homeDir;
        }

        // Return relative path to file
        return str_replace(sfConfig::get('sf_web_dir').'/', '', $this->homeDir);
    }

    /**
     * Get possible file names for the finding aid.
     *
     * @return array of possible filenames
     */
    public function getPossibleFilenames(): array
    {
        $filenames = [
            $this->resource->id.'.pdf',
            $this->resource->id.'.rtf',
        ];

        if (null !== $this->resource->slug) {
            $filenames[] = $this->resource->slug.'.pdf';
            $filenames[] = $this->resource->slug.'.rtf';
        }

        return $filenames;
    }

    /**
     * Set the path for the finding aid file.
     *
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Get the relative path for the finding aid file.
     *
     * If $this->path has no value, check the list of possible finding aid file
     * names for a matching file name on the filesystem.
     *
     * @return null|string path to finding aid file
     */
    public function getPath()
    {
        if (!isset($this->path)) {
            foreach (self::getPossibleFilenames() as $filename) {
                if (file_exists($this->getHomeDir().$filename)) {
                    // Set relative path to file
                    $this->path = $this->getHomeDir(false).$filename;
                }
            }
        }

        return $this->path;
    }

    /**
     * Get the Finding Aid file format.
     *
     * @return string file extension (e.g. "pdf", "rtf")
     */
    public function getFormat(): string
    {
        // If not set, try and determine the format from the file extension
        if (!isset($this->format) && !empty($this->getPath())) {
            $this->format = strtolower(
                pathinfo($this->getPath(), PATHINFO_EXTENSION)
            );
        }

        return $this->format;
    }

    /**
     * Set the Finding Aid status.
     *
     * @param $status either self::GENERATED_STATUS or self::UPLOADED_STATUS
     */
    public function setStatus(int $status): void
    {
        $allowed = [self::GENERATED_STATUS, self::UPLOADED_STATUS];

        if (!in_array($status, $allowed)) {
            throw new UnexpectedValueException(
                sprintf('Value must be one of (%s)', implode(', ', $allowed))
            );
        }

        $this->status = $status;
    }

    /**
     * Get the Finding Aid status.
     *
     * @return null|int self::GENERATED_STATUS, self::UPLOADED_STATUS or null
     */
    public function getStatus(): ?int
    {
        if (!isset($this->status)) {
            $this->status = $this->loadStatus();
        }

        return $this->status;
    }

    /**
     * Set transcript value.
     */
    public function setTranscript(string $text): void
    {
        $this->transcript = $text;
    }

    /**
     * Get transcript value.
     */
    public function getTranscript(): ?string
    {
        if (!isset($this->transcript)) {
            $this->transcript = $this->extractTranscript();
        }

        return $this->transcript;
    }

    /**
     * Save the Finding Aid data.
     */
    public function save()
    {
        // Save status to database
        $this->saveStatus();
        $esdata['status'] = $this->status;

        // Save the PDF transcript of uploaded finding aids
        if (self::UPLOADED_STATUS === $this->getStatus()) {
            $this->saveTranscript();
            $esdata['transcript'] = $this->transcript;
        }

        // Update Elasticsearch finding aid fields (status, transcript)
        QubitSearch::getInstance()->partialUpdate(
            $this->resource, ['findingAid' => $esdata]
        );
    }

    /**
     * Delete the finding aid file and data.
     *
     * @return true on success
     */
    public function delete(): ?bool
    {
        $this->logger->info(
            sprintf('Deleting finding aid (%s)...', $this->resource->slug)
        );

        if (!empty($this->getPath()) && file_exists($this->getPath())) {
            unlink($this->getPath());
        }

        // Delete 'findingAidTranscript' property if it exists
        $criteria = new Criteria();
        $criteria->add(QubitProperty::OBJECT_ID, $this->resource->id);
        $criteria->add(QubitProperty::NAME, 'findingAidTranscript');
        $criteria->add(
            QubitProperty::SCOPE,
            'Text extracted from finding aid PDF file text layer using'.
            ' pdftotext'
        );

        if (null !== $property = QubitProperty::getOne($criteria)) {
            $this->logger->info('Deleting finding aid transcript...');

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

        QubitSearch::getInstance()->partialUpdate(
            $this->resource, $partialData
        );

        $this->logger->info('Finding aid deleted successfully.');

        return true;
    }

    /**
     * Add finding aid data for an uploaded finding aid.
     *
     * @param $path to the uploaded finding aid file (the file should already be
     *              named correctly and be in the "downloads/" directory)
     *
     * @return true on success
     */
    public function upload(string $path): ?bool
    {
        $this->logger->info(
            sprintf('Uploading finding aid (%s)...', $this->resource->slug)
        );

        // Ensure 'downloads' directory exists
        Qubit::createDownloadsDirIfNeeded();

        // Set status
        $this->setStatus(self::UPLOADED_STATUS);
        $this->save();

        $this->logger->info(
            sprintf('Finding aid uploaded successfully: %s', $path)
        );

        return true;
    }

    /**
     * Extract PDF text.
     */
    public function extractTranscript(): string
    {
        $mimeType = 'application/'.$this->getFormat();

        if (!QubitDigitalObject::canExtractText($mimeType)) {
            $this->logger->info(
                sprintf(
                    'Can not extract finding aid text for mime type "%s"',
                    $mimeType
                )
            );

            return '';
        }

        $this->logger->info(
            sprintf('Extracting finding aid text from "%s"', $this->getPath())
        );

        $command = sprintf('pdftotext %s - 2> /dev/null', $this->getPath());
        exec($command, $output, $status);

        if (0 !== $status) {
            $this->logger->warning(
                'WARNING(PDFTOTEXT) Extracting finding aid text has failed'
            );

            return '';
        }

        if (0 === count($output)) {
            $this->logger->info(
                'No finding aid text found in document'
            );

            return '';
        }

        return implode(PHP_EOL, $output);
    }

    /**
     * Extract and save transcript text to `property` table.
     */
    public function saveTranscript(): void
    {
        // Truncate PDF text to <16MB to fit in `property.value` column
        $text = mb_strcut($this->getTranscript(), 0, 16777215);

        $this->logger->info(
            sprintf('Saving transcript (%u bytes)', mb_strlen($text))
        );

        // Search for existing transcript property
        $criteria = new Criteria();
        $criteria->add(QubitProperty::OBJECT_ID, $this->resource->id);
        $criteria->add(QubitProperty::NAME, 'findingAidTranscript');
        $criteria->add(
            QubitProperty::SCOPE,
            'Text extracted from finding aid PDF file text layer using'.
            ' pdftotext'
        );

        // Create a new transcript property, if one doesn't exist already
        if (null === $property = QubitProperty::getOne($criteria)) {
            $property = new QubitProperty();
            $property->objectId = $this->resource->id;
            $property->name = 'findingAidTranscript';
            $property->scope = 'Text extracted from finding aid PDF file text'.
                ' layer using pdftotext';
        }

        // Set transcript value and save property
        $property->setValue($text, ['sourceCulture' => true]);
        $property->indexOnSave = false;
        $property->save();
    }

    /**
     * Load status from the database.
     *
     * @return int self::GENERATED_STATUS, self::UPLOADED_STATUS or null
     */
    protected function loadStatus(): ?int
    {
        $criteria = new Criteria();
        $criteria->add(QubitProperty::OBJECT_ID, $this->resource->id);
        $criteria->add(QubitProperty::NAME, 'findingAidStatus');
        $property = QubitProperty::getOne($criteria);

        if (!isset($property)) {
            return null;
        }

        return $property->getValue(['sourceCulture' => true]);
    }

    /**
     * Save the Finding Aid Status to disk.
     */
    protected function saveStatus()
    {
        // Search for an existing 'findingAidStatus' property
        $criteria = new Criteria();
        $criteria->add(QubitProperty::OBJECT_ID, $this->resource->id);
        $criteria->add(QubitProperty::NAME, 'findingAidStatus');

        // Create a related 'findingAidStatus' QubitProperty if this resource
        // doesn't already have one
        if (null === $property = QubitProperty::getOne($criteria)) {
            $property = new QubitProperty();
            $property->objectId = $this->resource->id;
            $property->name = 'findingAidStatus';
        }

        // Set the status
        $property->setValue($this->status, ['sourceCulture' => true]);
        $property->indexOnSave = false;
        $property->save();
    }
}
