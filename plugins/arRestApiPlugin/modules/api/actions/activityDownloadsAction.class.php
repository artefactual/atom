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

class ApiActivityDownloadsAction extends QubitApiAction
{
  protected function get($request)
  {
    $data = array();

    $results = $this->getResults();
    $data['results'] = $results['results'];

    return $data;
  }

  protected function getResults()
  {
    // TODO: get this to actually work
    $criteria = new Criteria;
    $criteria->add(QubitProperty::NAME, 'aip_file_download');
    //$criteria->add(QubitPropertyI18n::CULTURE, sfPropel::getDefaultCulture());

    $properties = QubitProperty::get($criteria);

    if (null !== $properties)
    {
      return array();
    }

    return $properties;
  }
}
