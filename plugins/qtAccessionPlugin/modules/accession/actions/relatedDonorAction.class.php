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

class AccessionRelatedDonorAction extends RelationIndexAction
{
  public function extraQueries($value)
  {
    if (null !== $contact = $this->resource->object->getPrimaryContact())
    {
      if (isset($contact->streetAddress))
      {
        $value['streetAddress'] = $contact->streetAddress;
      }

      if (isset($contact->region))
      {
        $value['region'] = $contact->region;
      }

      if (isset($contact->countryCode))
      {
        $value['countryCode'] = $contact->countryCode;
      }

      if (isset($contact->postalCode))
      {
        $value['postalCode'] = $contact->postalCode;
      }

      if (isset($contact->telephone))
      {
        $value['telephone'] = $contact->telephone;
      }

      if (isset($contact->email))
      {
        $value['email'] = $contact->email;
      }

      if (isset($contact->contactPerson))
      {
        $value['contactPerson'] = $contact->contactPerson;
      }
    }

    return $value;
  }
}
