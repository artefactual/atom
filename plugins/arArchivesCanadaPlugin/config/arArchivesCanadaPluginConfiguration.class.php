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

class arArchivesCanadaPluginConfiguration extends sfPluginConfiguration
{
  public static
    $summary = 'Theme plugin, extension of arDominionPlugin.',
    $version = '0.0.1';

  public function contextLoadFactories(sfEvent $event)
  {
    $context = $event->getSubject();

    // Runtime less interpreter will be loaded if debug mode is enabled
    // Remember to avoid localStorage caching when dev machine is not localhost
    if ($context->getConfiguration()->isDebug())
    {
      $context->response->addJavaScript('/vendor/less.js');
      $context->response->addStylesheet('/plugins/arArchivesCanadaPlugin/css/main.less', 'last', array('rel' => 'stylesheet/less', 'type' => 'text/css', 'media' => 'all'));
    }
    else
    {
      $context->response->addStylesheet('/plugins/arArchivesCanadaPlugin/css/min.css', 'last', array('media' => 'all'));
    }
  }

  public function initialize()
  {
    $this->dispatcher->connect('context.load_factories', array($this, 'contextLoadFactories'));

    $decoratorDirs = sfConfig::get('sf_decorator_dirs');
    $decoratorDirs[] = $this->rootDir.'/templates';
    sfConfig::set('sf_decorator_dirs', $decoratorDirs);

    $moduleDirs = sfConfig::get('sf_module_dirs');
    $moduleDirs[$this->rootDir.'/modules'] = false;
    sfConfig::set('sf_module_dirs', $moduleDirs);
  }
}
