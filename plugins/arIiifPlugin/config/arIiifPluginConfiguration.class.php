<?php

/**
 * arIiifPlugin configuration.
 *
 * IIIF Image Carousel Plugin for AtoM
 *
 * @author     Your Name
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
