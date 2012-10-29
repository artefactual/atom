<?php

/*
 * This file is part of the AccesstoMemory (AtoM) software.
 *
 * AccesstoMemory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AccesstoMemory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AccesstoMemory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class qubitConfiguration extends sfApplicationConfiguration
{
  const
    VERSION = '1.3';

  public function responseFilterContent(sfEvent $event, $content)
  {
    ProjectConfiguration::getActive()->loadHelpers('Javascript');

    return str_ireplace('</head>', javascript_tag('jQuery.extend(Qubit, '.json_encode(array('relativeUrlRoot' => sfContext::getInstance()->request->getRelativeUrlRoot())).');').'</head>', $content);
  }

  public function configure()
  {
    $this->dispatcher->connect('response.filter_content', array($this, 'responseFilterContent'));
  }

  /**
   * @see sfApplicationConfiguration
   */
  public function getControllerDirs($moduleName)
  {
    if (!isset($this->cache['getControllerDirs'][$moduleName]))
    {
      $this->cache['getControllerDirs'][$moduleName] = array();

      // HACK Currently plugins only override application templates, not the
      // other way around
      foreach ($this->getPluginSubPaths('/modules/'.$moduleName.'/actions') as $dir)
      {
        $this->cache['getControllerDirs'][$moduleName][$dir] = false; // plugins
      }

      $this->cache['getControllerDirs'][$moduleName][sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/actions'] = false; // application
    }

    return $this->cache['getControllerDirs'][$moduleName];
  }

  /**
   * @see sfApplicationConfiguration
   */
  public function getDecoratorDirs()
  {
    $dirs = sfConfig::get('sf_decorator_dirs');
    $dirs[] = sfConfig::get('sf_app_template_dir');

    return $dirs;
  }

  /**
   * @see sfApplicationConfiguration
   */
  public function getTemplateDirs($moduleName)
  {
    // HACK Currently plugins only override application templates, not the
    // other way around
    $dirs = $this->getPluginSubPaths('/modules/'.$moduleName.'/templates');
    $dirs[] = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/templates';

    $dirs = array_merge($dirs, $this->getDecoratorDirs());

    return $dirs;
  }

  /**
   * @see sfProjectConfiguration
   */
  public function setRootDir($path)
  {
    parent::setRootDir($path);

    $this->setWebDir($path);
  }
}
