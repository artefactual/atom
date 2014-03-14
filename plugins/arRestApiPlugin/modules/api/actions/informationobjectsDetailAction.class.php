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

class ApiInformationObjectsDetailAction extends QubitApiAction
{
  protected function get($request)
  {
    try
    {
      $result = QubitSearch::getInstance()->index->getType('QubitInformationObject')->getDocument($this->request->id);
    }
    catch (\Elastica\Exception\NotFoundException $e)
    {
      throw new QubitApi404Exception('Information object not found');
    }

    $doc = $result->getData();
    $data = array();

    ProjectConfiguration::getActive()->loadHelpers('Qubit');

    $data['id'] = $result->getId();
    $data['title'] = get_search_i18n($doc, 'title');
    $data['level_of_description_id'] = (int)$doc['levelOfDescriptionId'];

    if (sfConfig::get('app_drmc_lod_artwork_record_id') == $doc['levelOfDescriptionId'])
    {
      $data['tms'] = array(
        'accessionNumber' => '1098.2005.a-c',
        'objectId' => '100620',
        'title' => 'Play Dead; Real Time',
        'year' => '2003',
        'artist' => 'Douglas Gordon',
        'classification' => 'Installation',
        'medium' => 'Three-channel video',
        'dimensions' => '19:11 min, 14:44 min. (on larger screens), 21:58 min. (on monitor). Minimum Room Size: 24.8m x 13.07m',
        'description' => 'Exhibition materials: 3 DVD and players, 2 projectors, 3 monitor, 2 screens. The complete work is a three-screen piece, consisting of '
      );
    }
    else
    {
      $data['dc'] = array(
        'identifier' => $doc['identifier'],
        'title' => get_search_i18n($doc, 'title')
        // 'description' => $io->getScopeAndContent(array('cultureFallback' => true))
        // 'subjects' => ['Elephants', 'Circus', 'Zoo', 'Animals'],
        // 'description' => 'Exhibition materials: 3 DVD and players, 2 projectors, 3 monitor, 2 screens. The complete work is a three-screen piece, consisting of one retro projection, one front projection and one monitor. See file for installation instructions. One monitor and two projections on screens 19.69 X 11.38 feet. Viewer must be able to walk around screens.',
        // 'type' => ['image'],
        // 'format' => 'You tell me',
        // 'source' => 'Somewhere',
        // 'language' => ['English'],
        // 'isLocatedAt' => ['MoMA'],
        // 'spatial' => ['New York'],
        // 'rights' => 'Many rights'
      );
    }

    return $data;
  }

  protected function post($request, $payload)
  {
    $io = $this->fetchInformationObjectOr404();

    // TODO: restrict to allowed fields
    foreach ($payload as $field => $value)
    {
      $field = lcfirst(sfInflector::camelize($field));
      $io->$field = $value;
    }

    $io->save();

    return $this->get($request);
  }

  protected function fetchInformationObjectOr404()
  {
    if (QubitInformationObject::ROOT_ID === (int)$this->request->id)
    {
      throw new QubitApi404Exception('Information object not found');
    }

    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::ID, $this->request->id);
    if (isset($this->request->level_id) && true === ctype_digit($this->request->level_id))
    {
      $criteria->add(QubitInformationObject::LEVEL_OF_DESCRIPTION_ID, $this->request->level_id);
    }

    if (null === $io = QubitInformationObject::getById($this->request->id))
    {
      throw new QubitApi404Exception('Information object not found');
    }

    return $io;
  }
}
