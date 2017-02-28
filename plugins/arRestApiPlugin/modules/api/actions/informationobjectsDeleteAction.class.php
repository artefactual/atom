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

class ApiInformationObjectsDeleteAction extends QubitApiAction
{
  protected function delete($request)
  {
    // Get slug so we can determine information object's ID
    $criteria = new Criteria;
    $criteria->add(QubitSlug::SLUG, $request->slug);

    $slug = QubitSlug::getOne($criteria);

    if (null !== $slug)
    {
      if (QubitInformationObject::ROOT_ID === (int)$slug->objectId)
      {
        throw new QubitApiForbiddenException;
      }

      $criteria = new Criteria;
      $criteria->add(QubitInformationObject::ID, $slug->objectId);

      if (null !== ($io = QubitInformationObject::getOne($criteria)))
      {
        $io->delete();
        return sfView::NONE;
      }
    }

    throw new QubitApi404Exception('Information object not found');
  }
}
