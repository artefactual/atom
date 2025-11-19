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
 *
 * arIiifPlugin configuration
 *
 * IIIF Image Carousel Plugin for AtoM
 * Modified by Johan Pieterse The Archive and Heritage Group <johan@theahg.co.za>
 */
 
class arIiifPluginConfiguration extends sfPluginConfiguration
{
  // Summary and version
  public static $summary = 'IIIF Image Carousel and Viewer plugin for AtoM';
  public static $version = '1.0.0';

  /**
   * Plugin installation hook.
   */
  public function contextLoadFactories()
  {
    // Nothing to do here for now
  }

  /**
   * Initialize plugin.
   *
   * @param sfEventDispatcher $dispatcher
   */
  public function initialize()
  {
    $this->dispatcher->connect('routing.load_configuration', [$this, 'listenToRoutingLoadConfigurationEvent']);
  }

  /**
   * Listen to routing.load_configuration event.
   *
   * @param sfEvent $event
   */
  public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
  {
    $routing = $event->getSubject();

    // Load plugin routing rules
    $routing->prependRoute('iiif_manifest', new sfRoute(
      '/iiif/:slug/manifest',
      ['module' => 'iiif', 'action' => 'manifest']
    ));

    $routing->prependRoute('iiif_object_manifest', new sfRoute(
      '/iiif/object/:id/manifest',
      ['module' => 'iiif', 'action' => 'objectManifest']
    ));

    $routing->prependRoute('iiif_canvas', new sfRoute(
      '/iiif/:slug/canvas/:canvas',
      ['module' => 'iiif', 'action' => 'canvas']
    ));
  }

  /**
   * Establish plugin version.
   */
  public static function getVersion()
  {
    return self::$version;
  }
}
