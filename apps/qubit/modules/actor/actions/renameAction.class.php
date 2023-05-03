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

class ActorRenameAction extends DefaultEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'authorizedFormOfName',
        'slug',
        'filename',
    ];

    // Allow modification of authorized form of name, slug, and digital object filename
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
                    [$this->resource, 'module' => 'actor']
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
        if (in_array($name, ActorRenameAction::$NAMES)) {
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
        $postedName = $this->form->getValue('authorizedFormOfName');
        $postedSlug = $this->form->getValue('slug');
        $postedFilename = $this->form->getValue('filename');

        // Update authorized form of name, if name sent
        if (!empty($postedName)) {
            $this->resource->authorizedFormOfName = $postedName;
        }

        // Attempt to update slug, if slug sent
        if (!empty($postedSlug)) {
            $slug = QubitSlug::getByObjectId($this->resource->id);

            // Attempt to change slug if submitted slug's different than current
            // slug
            if ($postedSlug != $slug->slug) {
                $slug->slug = ActorSlugPreviewAction::determineAvailableSlug(
                $postedSlug, $this->resource->id
                );
                $slug->save();
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
    }
}
