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
 * Digital Object edit component - Simplified version using metadata extraction plugin
 *
 * @author     david juhasz <david@artefactual.com>
 * Modified by Johan Pieterse to use arMetadataExtractionPlugin
 */
class ObjectAddDigitalObjectAction extends sfAction
{
    public function execute($request)
    {
        $this->form = new sfForm();
        $this->form
            ->getValidatorSchema()
            ->setOption('allow_extra_fields', true);

        $this->resource = $this->getRoute()->resource;

        // Get repository to test upload limits
        if ($this->resource instanceof QubitInformationObject) {
            $this->repository = $this->resource->getRepository([
                'inherit' => true,
            ]);
        } elseif ($this->resource instanceof QubitActor) {
            $this->repository = $this->resource->getMaintainingRepository();
        }

        // Check that object exists and that it is not the root
        if (!isset($this->resource) || !isset($this->resource->parent)) {
            $this->forward404();
        }

        // Assemble resource description
        sfContext::getInstance()
            ->getConfiguration()
            ->loadHelpers(['Qubit']);

        if ($this->resource instanceof QubitActor) {
            $this->resourceDescription = render_title($this->resource);
        } elseif ($this->resource instanceof QubitInformationObject) {
            $this->resourceDescription = '';

            if (isset($this->resource->identifier)) {
                $this->resourceDescription .=
                    $this->resource->identifier.' - ';
            }

            $this->resourceDescription .= render_title(
                new sfIsadPlugin($this->resource)
            );
        }

        // Check if already exists a digital object
        if (null !== ($digitalObject = $this->resource->getDigitalObject())) {
            // NARSSA/SITA JJP multiple object upload enabled
            // $this->redirect([$digitalObject, 'module' => 'digitalobject', 'action' => 'edit']);
        }

        // Check user authorization
        if (!QubitAcl::check($this->resource, 'update')) {
            QubitAcl::forwardUnauthorized();
        }

        // Check if uploads are allowed
        if (!QubitDigitalObject::isUploadAllowed()) {
            QubitAcl::forwardToSecureAction();
        }

        // Add form fields
        $this->addFields($request);

        // Process form
        if ($request->isMethod('post')) {
            $this->form->bind(
                $request->getPostParameters(),
                $request->getFiles()
            );
            if ($this->form->isValid()) {
                $this->processForm();

                $this->resource->save();

                if ($this->resource instanceof QubitInformationObject) {
                    $this->resource->updateXmlExports();
                }
                $this->redirect([$this->resource, 'module' => 'object']);
            }
        }
    }

    /**
     * Upload the asset selected by user and create a digital object with appropriate
     * representations.
     *
     * @return DigitalObjectEditAction this action
     */
    public function processForm()
    {
        $digitalObject = new QubitDigitalObject();

        if (null !== $this->form->getValue('file')) {
            $file = $this->form->getValue('file');
            $tempFilePath = $file->getTempName();
            $name = $file->getOriginalName();
            $content = file_get_contents($tempFilePath);

            // Set up digital object with assets
            $digitalObject->assets[] = new QubitAsset($name, $content);
            $digitalObject->usageId = QubitTerm::MASTER_ID;

            // Add digital object to resource
            $this->resource->digitalObjectsRelatedByobjectId[] = $digitalObject;
            
            // Save the parent resource first (creates the relationship)
            $this->resource->save();

            // Set objectId BEFORE saving
            $digitalObject->objectId = $this->resource->id;

            // Save the digital object so file exists on disk
            $digitalObject->save();
            
            // The plugin's event listener will automatically extract metadata
            // when the digital object is saved (via the 'digital_object.post_create' event)
            
            // However, if you want to manually trigger extraction:
            if (class_exists('arMetadataExtractor')) {
                $extractor = new arMetadataExtractor();
                $extractor->processDigitalObject($digitalObject);
            }
            
            // Also still run the embedded technical metadata extraction if available
            $this->appendEmbeddedTechMetadata($digitalObject);

        } elseif (null !== $this->form->getValue('url')) {
            // Catch errors trying to download remote resource
            try {
                $digitalObject->importFromURI($this->form->getValue('url'));
                $this->resource->digitalObjectsRelatedByobjectId[] = $digitalObject;
            } catch (sfException $e) {
                // Log download exception
                $this->logMessage($e->getMessage(), 'err');
            }
        }
    }

    protected function addFields($request)
    {
        // Single upload
        if (0 < count($request->getFiles())) {
            $this->form->setValidator('file', new sfValidatorFile());
        }

        $this->form->setWidget('file', new sfWidgetFormInputFile());

        // URL
        if (isset($request->url) && 'http://' != $request->url) {
            $this->form->setValidator('url', new QubitValidatorUrl());
        }

        $this->form->setDefault('url', 'http://');
        $this->form->setWidget('url', new sfWidgetFormInput());
    }

    /**
     * Append embedded technical metadata using arEmbeddedMetadataParser
     * This remains as a backup/additional method for technical metadata
     */
    private function appendEmbeddedTechMetadata($digitalObject)
    {
        try {
            if (
                class_exists('arEmbeddedMetadataParser', /* autoload */ true)
                && isset($digitalObject)
                && $digitalObject instanceof QubitDigitalObject
            ) {
                $absPath = method_exists($digitalObject, 'getAbsolutePath')
                    ? $digitalObject->getAbsolutePath()
                    : (string) $digitalObject->getPath();

                if ($absPath && is_readable($absPath)) {
                    // Extract metadata using the helper
                    $meta = arEmbeddedMetadataParser::extract($absPath);
                    
                    if (is_array($meta)) {
                        // Format and save summary
                        $summary = arEmbeddedMetadataParser::formatSummary($meta);
                        
                        if ('' !== $summary) {
                            $io = $this->resource;
                            
                            if ($io instanceof QubitInformationObject) {
                                $existing = (string) $io->physicalCharacteristics;
                                
                                // Remove existing Technical Metadata section
                                if ($existing && false !== strpos($existing, 'Technical Metadata:')) {
                                    $existing = preg_replace('/\n?Technical Metadata:.*\z/s', '', $existing);
                                    $existing = rtrim($existing);
                                }
                                
                                $io->physicalCharacteristics = $existing
                                    ? $existing."\n\n".$summary
                                    : $summary;
                                $io->save();
                                
                                error_log('Successfully saved technical metadata to physical characteristics');
                            }
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            error_log('Error in appendEmbeddedTechMetadata: '.$e->getMessage());
        }
    }
}
