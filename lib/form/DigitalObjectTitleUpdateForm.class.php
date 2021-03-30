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
 * Settings module - generic form definition.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class DigitalObjectTitleUpdateForm extends sfForm
{
    public function configure()
    {
        // Build widgets and validators
        foreach ($this->getInformationObjects() as $informationObject) {
            $widgets[$informationObject->id] = new sfWidgetFormInput();
            $validators[$informationObject->id] = new sfValidatorString();
        }

        // Set them
        $this->setWidgets($widgets);
        $this->setValidators($validators);

        // Set decorator
        $decorator = new QubitWidgetFormSchemaFormatterList($this->widgetSchema);
        $this->widgetSchema->addFormFormatter('list', $decorator);
        $this->widgetSchema->setFormFormatterName('list');

        // Set wrapper text
        $this->widgetSchema->setNameFormat('titles[%s]');
    }

    public function setScope($scope)
    {
        $this->setOption('scope', $scope);

        return $this;
    }

    public function getScope()
    {
        return $this->getOption('scope');
    }

    public function setInformationObjects(array $objects)
    {
        $this->setOption('informationObjects', $objects);

        return $this;
    }

    public function getInformationObjects()
    {
        return $this->getOption('informationObjects');
    }
}
