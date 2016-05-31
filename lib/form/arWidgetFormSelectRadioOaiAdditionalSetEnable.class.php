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

class arWidgetFormSelectRadioOaiAdditionalSetEnable extends sfWidgetFormSelectRadio
{
  public function formatter($widget, $inputs)
  {
    $output = parent::formatter($widget, $inputs);

    $oaiAdditionalSetsEnabled = QubitSetting::getByName('oai_additional_sets_enabled');

    // If OAI additional sets are enabled, display a list of links to them
    if (isset($oaiAdditionalSetsEnabled) && intval($oaiAdditionalSetsEnabled->getValue(array('sourceCulture'=>true))))
    {
      foreach(QubitOai::getAdditionalOaiSets() as $set)
      {
        $setUrl = url_for('oai') .'?verb=ListRecords&metadataPrefix=oai_dc&set='. $set->setSpec();
        $output .= $this->renderContentTag('div', $this->renderContentTag('a', $set->getName(), array('href' => $setUrl)));
      }
    }

    return $output;
  }
}
