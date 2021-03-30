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

class InformationObjectMultiFileUpdateAction extends sfAction
{
    protected $informationObjectList = [];
    protected $informationObjectOrignalTitles = [];

    public function execute($request)
    {
        $this->resource = $this->getRoute()->resource;

        // Check user authorization
        if (!QubitAcl::check($this->resource, 'update')) {
            QubitAcl::forwardUnauthorized();
        }

        // Check that object exists and that it is not the root
        if (!isset($this->resource) || !isset($this->resource->parent)) {
            $this->forward404();
        }

        if (isset($request->items)) {
            if (false === $this->items = explode(',', $request->items)) {
                $this->forward404();
            }

            foreach ($this->items as $slug) {
                if (null !== $io = QubitInformationObject::getBySlug($slug)) {
                    if (!QubitAcl::check($io, 'update')) {
                        continue;
                    }

                    // Child IOs should not be root and should be direct descendants of resource.
                    if (!isset($io->parent) || $io->parentId !== $this->resource->id) {
                        continue;
                    }

                    $this->informationObjectList[$slug] = $io;
                    $this->informationObjectOrignalTitles[$slug] = $io->title;
                } else {
                    continue;
                }
            }
        }

        $this->digitalObjectTitleForm = new DigitalObjectTitleUpdateForm(
            [],
            ['informationObjects' => $this->informationObjectList]
        );

        // Handle POST data (form submit)
        if ($request->isMethod('post')) {
            $this->digitalObjectTitleForm->bind($request->titles);

            if ($this->digitalObjectTitleForm->isValid()) {
                $this->updateDigitalObjectTitles();
                $this->redirect([$this->resource, 'module' => 'informationobject']);
            }
        }

        $this->populateDigitalObjectTitleForm();
    }

    /**
     * Populate the ui_label form with database values (localized).
     */
    protected function populateDigitalObjectTitleForm()
    {
        foreach ($this->digitalObjectTitleForm->getInformationObjects() as $io) {
            $this->digitalObjectTitleForm->setDefault($io->id, $io->getTitle(['cultureFallback' => true]));
        }
    }

    /**
     * Update ui_label db values with form values (localized).
     *
     * @return $this
     */
    protected function updateDigitalObjectTitles()
    {
        foreach ($this->digitalObjectTitleForm->getInformationObjects() as $informationObject) {
            if (null !== $title = $this->digitalObjectTitleForm->getValue($informationObject->id)) {
                // Test if title changed.
                if ($this->informationObjectOrignalTitles[$informationObject->id] !== $title) {
                    $informationObject->title = $title;
                    $informationObject->save();
                }
            }
        }
    }
}
