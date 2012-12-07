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

class QubitGenerator extends sfPropelAdminGenerator
{
  protected function setScaffoldingClassName($className)
  {
    $this->singularName = strtolower(substr($className, 0, 1)).substr($className, 1);
    $this->pluralName = $this->singularName.'s';
    $this->className = $className;
    $this->peerClassName = $className.'Peer';
  }

  public function getColumnEditTag($column, $params = array())
  {
    if ($column->isComponent())
    {
      $moduleName = $this->getModuleName();
      $componentName = $column->getName();
      if (false !== $pos = strpos($componentName, '/'))
      {
        $moduleName = substr($componentName, 0, $pos);
        $componentName = substr($componentName, $pos + 1);
      }

      return "get_component('$moduleName', '$componentName', array('type' => 'edit', '{$this->getSingularName()}' => \${$this->getSingularName()}))";
    }

    return parent::getColumnEditTag($column, $params);
  }
}
