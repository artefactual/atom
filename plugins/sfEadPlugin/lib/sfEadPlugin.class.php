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

/**
 * @package    qubit
 * @subpackage sfEadPlugin
 * @author     David Juhasz <david@artefactual.com>
 * @version    svn: $Id: sfEadPlugin.class.php 11168 2012-03-14 18:18:01Z david $
 */
class sfEadPlugin
{
  public
    $resource;

  public function __construct(QubitInformationObject $resource)
  {
    $this->resource = $resource;
  }

  public function __get($name)
  {
    return $this->resource->$name;
  }

  public function renderEadId()
  {
    $countryCode = $mainAgencyCode = '';

    if (null !== $this->resource->getRepository(array('inherit' => true)))
    {
      if (null !== $country = $this->resource->getRepository(array('inherit' => true))->getCountryCode())
      {
        $countryCode = " countrycode=\"$country\"";
      }

      if (null !== $agency = $this->resource->getRepository(array('inherit' => true))->getIdentifier())
      {
        if (isset($country))
        {
          $agency = $country.'-'.$agency;
        }

        $mainAgencyCode = " mainagencycode=\"$agency\"";
      }
    }

    $url = url_for(array($this->resource, 'module' => 'informationobject', 'sf_format' => 'xml'), $absolute = true);

    return "<eadid$countryCode$mainAgencyCode url=\"$url\" encodinganalog=\"Identifier\">{$this->resource->identifier}</eadid>";
  }
}
