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

class QubitAclPermission extends BaseAclPermission
{
  public function check($userId, $objectId, $actionId, $parameters = array())
  {
    $user = QubitUser::getById($userId);

    if (
      ($userId == $this->userId || $user->hasGroup($this->groupId)) &&
      $objectId == $this->objectId &&
      $actionId == $this->actionId &&
      $this->evaluateConditional($parameters))
    {
      return $this->grantDeny;
    }
  }

  public function setRepository($repository)
  {
    if ($repository instanceof QubitRepository)
    {
      $this->setConstants(array('repository' => $repository->slug));
      $this->conditional = '%p[repository] == %k[repository]';
    }
    else if (null === $repository)
    {
      $this->setConstants(array('repository' => null));
      $this->conditional = null;
    }

    return $this;
  }

  public function setTaxonomy($taxonomy)
  {
    if ($taxonomy instanceof QubitTaxonomy)
    {
      $this->setConstants(array('taxonomy' => $taxonomy->slug));
      $this->conditional = '%p[taxonomy] == %k[taxonomy]';
    }
    else if (null === $taxonomy)
    {
      $this->setConstants(array('taxonomy' => null));
      $this->conditional = null;
    }

    return $this;
  }

  public function getRepository()
  {
    if (null !== $repository = $this->getConstants(array('name' => 'repository')))
    {
      $criteria = new Criteria;
      $criteria->add(QubitSlug::SLUG, $repository);
      $criteria->addJoin(QubitSlug::OBJECT_ID, QubitObject::ID);

      return QubitRepository::get($criteria)->__get(0);
    }
  }

  public function getConstants($options = array())
  {
    $value = null;

    if (null !== $constants = parent::__get('constants', $options))
    {
      $value = unserialize($constants);
    }

    if (isset($options['name']))
    {
      if (isset($value[$options['name']]))
      {
        $value = $value[$options['name']];
      }
      else
      {
        return; // Return null if key 'name' not set
      }
    }

    return $value;
  }

  public function setConstants($value, $options = array())
  {
    if (is_array($value))
    {
      $constants = array();
      if (parent::__isset('constants', $options))
      {
        $constants = unserialize(parent::__get('constants', $options));
      }

      foreach ($value as $key => $val)
      {
        if (null !== $val)
        {
          $constants[$key] = $val;
        }
        else if (isset($constants[$key]))
        {
          unset($constants[$key]); // Remove key if value is null
        }
      }

      $value = $constants;
    }

    if (is_array($value) && 0 < count($value))
    {
      parent::__set('constants', serialize($value), $options);
    }
    else
    {
      parent::__set('constants', null, $options);
    }

    return $this;
  }

  public function evaluateConditional($parameters)
  {
    // If no conditional specified, than always return true
    if (0 == strlen($conditional = $this->conditional))
    {
      return true;
    }

    $constants = unserialize($this->constants);

    // Substitute constants
    if (preg_match_all('/%k\[(\w+)\]/', $conditional, $matches))
    {
      foreach ($matches[1] as $match)
      {
        if (isset($constants[$match]))
        {
          if (is_string($constants[$match]))
          {
            $conditional = str_replace('%k['.$match.']', '\''.$constants[$match].'\'', $conditional);
          }
          else if (is_array($constants[$match]))
          {
            $conditional = str_replace('%k['.$match.']', '$constants[$match]', $conditional);
          }
        }
      }
    }

    // Substitute parameters
    if (preg_match_all('/%p\[(\w+)\]/', $conditional, $matches))
    {
      foreach ($matches[1] as $key)
      {
        if (array_key_exists($key, $parameters))
        {
          // For null parameters always grant and never deny privileges
          // e.g. creating a new info object: $repositoryId is null
          if (null === $parameters[$key])
          {
            if (0 == $this->grantDeny)
            {
              return false;
            }
            else
            {
              continue;
            }
          }

          $conditional = str_replace('%p['.$key.']', '\''.$parameters[$key].'\'', $conditional);

          // If any conditional evaluates false then return false
          if (!eval('return ('.$conditional.');'))
          {
            return false;
          }
        }
        else
        {
          continue; // Don't evaluate if paramater not passed
        }
      }
    }

    return true;
  }

  /*
   * Render grantDeny boolean as string
   *
   * @return string
   */
  public function renderAccess()
  {
    switch ($this->grantDeny)
    {
      case 1:
        $access = 'Grant';
        break;
      case null:
        $access = 'Inherit';
        break;
      default:
        $access = 'Deny';
    }

    return $access;
  }
}
