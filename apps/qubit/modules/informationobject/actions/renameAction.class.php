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

class InformationObjectRenameAction extends DefaultEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'title',
        'slug',
        'filename',
    ];

    // Allow modification of title, slug, and digital object filename
    public function execute($request)
    {
        parent::execute($request);

        if ('POST' == $this->request->getMethod()) {
            // Internationalization needed for flash messages
            ProjectConfiguration::getActive()->loadHelpers('I18N');

            $this->form->bind($request->getPostParameters());

            if ($this->form->isValid()) {
                $this->updateResource();

                // Let user know description was updated (and if slug had to be
                // adjusted)
                $message = __('Description updated.');

                $postedSlug = $this->form->getValue('slug');

                if (
                    (null !== $postedSlug)
                    && $this->resource->slug != $postedSlug
                ) {
                    $message .= ' '.__(
                        'Slug was adjusted to remove special characters or'.
                        ' because it has already been used for another'.
                        ' description.'
                    );
                }

                $this->getUser()->setFlash('notice', $message);

                $this->redirect(
                    [$this->resource, 'module' => 'informationobject']
                );
            }
        }
    }

    protected function earlyExecute()
    {
        $this->resource = $this->getRoute()->resource;

        // Check user authorization
        if (
            !QubitAcl::check($this->resource, 'update')
            && !$this->getUser()->hasGroup(QubitAclGroup::EDITOR_ID)
        ) {
            QubitAcl::forwardUnauthorized();
        }
    }

    protected function addField($name)
    {
        if (in_array($name, InformationObjectRenameAction::$NAMES)) {
            if ('filename' == $name) {
                $this->form->setDefault(
                    $name,
                    $this->resource->digitalObjectsRelatedByobjectId[0]->name
                );
            } else {
                $this->form->setDefault($name, $this->resource[$name]);
            }

            $this->form->setValidator($name, new sfValidatorString());
            $this->form->setWidget($name, new sfWidgetFormInput());
        }
    }

    private function updateResource()
    {
        $postedTitle = $this->form->getValue('title');
        $postedSlug = $this->form->getValue('slug');
        $postedFilename = $this->form->getValue('filename');

        // Update title, if title sent
        if (!empty($postedTitle)) {
            $this->resource->title = $postedTitle;
        }

        // Attempt to update slug if slug sent
        if (!empty($postedSlug)) {
            $slug = QubitSlug::getByObjectId($this->resource->id);

            // Get finding aid path before rename
            $findingAid = new QubitFindingAid($this->resource);
            $oldFindingAidPath = $findingAid->getPath();

            // Attempt to change slug if submitted slug's different than current
            // slug
            if ($postedSlug != $slug->slug) {
                $slug->slug = InformationObjectSlugPreviewAction::determineAvailableSlug(
                    $postedSlug, $this->resource->id
                );
                $slug->save();

                // Set $resource->slug so the new slug is used to generate the
                // new Finding Aid filename
                $this->resource->slug = $slug->slug;
                $this->renameFindingAid($oldFindingAidPath);
            }
        }

        // Update digital object filename, if filename sent
        if (
            (null !== $postedFilename)
            && 0 !== count($this->resource->digitalObjectsRelatedByobjectId)
        ) {
            // Parse filename so special characters can be removed
            $fileParts = pathinfo($postedFilename);
            $filename = QubitSlug::slugify($fileParts['filename']).'.'.
                QubitSlug::slugify($fileParts['extension']);

            $digitalObject = $this->resource->digitalObjectsRelatedByobjectId[0];

            // Rename master file
            $basePath = sfConfig::get('sf_web_dir').$digitalObject->path;
            $oldFilePath = $basePath.DIRECTORY_SEPARATOR.$digitalObject->name;
            $newFilePath = $basePath.DIRECTORY_SEPARATOR.$filename;
            rename($oldFilePath, $newFilePath);
            chmod($newFilePath, 0644);

            // Change name in database
            $digitalObject->name = $filename;
            $digitalObject->save();

            // Regenerate derivatives
            digitalObjectRegenDerivativesTask::regenerateDerivatives(
                $digitalObject, ['keepTranscript' => true]
            );
        }

        $this->resource->save();
        $this->resource->updateXmlExports();
    }

    /**
     * Rename the attached finding aid file when the description slug changes.
     *
     * @param string $filepath current finding aid file path
     */
    private function renameFindingAid(?string $filepath): void
    {
        // If this description has no finding aid attached, $filepath will be
        // null, and there is nothing to do
        if (empty($filepath)) {
            return;
        }

        // Generate a new finding aid path using the new $resource slug
        $newPath = QubitFindingAidGenerator::generatePath(
            $this->resource
        );

        $success = rename($filepath, $newPath);

        if (false === $success) {
            $message =
            $this->logMessage(
                sprintf(
                    'Finding aid document could not be renamed to match the'.
                    ' new slug (old=%s, new=%s)',
                    $filepath,
                    $newPath
                ),
                'warning'
            );
        }
    }
}
