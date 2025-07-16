<?php

class arIiifPluginConfiguration extends sfPluginConfiguration
{
  // Plugin metadata for Web UI display
  public static $summary = 'Provides IIIF (International Image Interoperability Framework) Presentation API support for sharing digital images with enhanced interoperability and viewer integration.';
  public static $version = '1.0.0';

  public function initialize()
  {
    // Only initialize if plugin is enabled
    if ($this->isEnabled())
    {
      $enabledModules = sfConfig::get('sf_enabled_modules');
      $enabledModules[] = 'iiif';
      sfConfig::set('sf_enabled_modules', $enabledModules);

      // Register our routing hook so that, when the plugin is active,
      // /iiif/manifest/:slug is automatically available.
      $this->dispatcher->connect('routing.load_configuration', [$this, 'listenToRoutingLoadConfigurationEvent']);
    }
  }

  public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
  {
    /** @var sfRouting $routing */
    $routing = $event->getSubject();
    // Add the manifest route using slug
    $routing->prependRoute('iiif_manifest', new sfRoute(
      '/iiif/manifest/:slug',
      [
          'module' => 'iiif',
          'action' => 'manifest',
      ],
      [
          'slug' => '[^/]+', // Allow any characters except forward slash
      ]
    ));
  }

  /**
   * Check if this plugin is enabled via the plugin management system
   */
  private function isEnabled()
  {
    $enabledPlugins = QubitSetting::getByName('plugins');
    if ($enabledPlugins)
    {
      $pluginSettings = unserialize($enabledPlugins->value);
      return is_array($pluginSettings) && in_array('arIiifPlugin', $pluginSettings);
    }
    
    // Default to enabled if no plugin settings exist (backward compatibility)
    return false;
  }
}
