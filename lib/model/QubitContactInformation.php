<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
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
    $string = ($this->getStreetAddress()) ? $this->getStreetAddress().'<br/>' : '';
    $string .= ($this->getCity()) ? $this->getCity() : '';
    $string .= ($this->getRegion()) ? ', '.$this->getRegion() : '';
    $string .= ($this->getCountryCode()) ? '<br/>'.$this->getCountryCode().'' : '';
    $string .= ($this->getPostalCode()) ? '   '.$this->getPostalCode(): '';
    $string .= ($this->getTelephone()) ? '<p>telephone number: '.$this->getTelephone().'<br/>' : '';
    $string .= ($this->getFax()) ? 'fax number: '.$this->getFax().'<br/>' : '';
    $string .= ($this->getEmail()) ? 'email: '.$this->getEmail().'<br/>' : '';
    $string .= ($this->getWebsite()) ? 'website: <a href="'.$this->getWebsite().'">'.$this->getWebsite().'</a>' : '';

    return $string;
  }
}
