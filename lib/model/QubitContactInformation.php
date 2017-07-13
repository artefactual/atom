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

class QubitContactInformation extends BaseContactInformation
{
  public function __toString()
  {
    return (string) $this->getContactType();
  }

  public function makePrimaryContact()
  {
    $criteria = new Criteria;
    $criteria->add(QubitContactInformation::ACTOR_ID, $this->actorId);
    $contacts = self::get($criteria);

    foreach ($contacts as $item)
    {
      if ($item->id == $this->id)
      {
        $item->primaryContact = true;
      }
      else
      {
        $item->primaryContact = false;
      }

      $item->save();
    }
  }

  public function getContactInformationString()
  {
    // TODO: This should be converted into a partial!
    sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
    $cf = array('cultureFallback' => true);

    $string = ($this->getStreetAddress($cf)) ? esc_specialchars($this->getStreetAddress($cf)).'<br/>' : '';
    $string .= ($this->getCity($cf)) ? esc_specialchars($this->getCity($cf)) : '';
    $string .= ($this->getRegion($cf)) ? ', '.esc_specialchars($this->getRegion($cf)) : '';
    $string .= ($this->getCountryCode($cf)) ? '<br/>'.esc_specialchars($this->getCountryCode($cf)) : '';
    $string .= ($this->getPostalCode($cf)) ? '   '.esc_specialchars($this->getPostalCode($cf)): '';

    return $string;
  }
}
