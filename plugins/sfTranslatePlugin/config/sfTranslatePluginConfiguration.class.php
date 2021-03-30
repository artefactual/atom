<?php

/**
 * sfTranslatePlugin configuration.
 *
 * @author      Your name here
 */
class sfTranslatePluginConfiguration extends sfPluginConfiguration
{
    public function contextLoadFactories(sfEvent $event)
    {
        $context = $event->getSubject();

        // Stop execution if user is not authenticated
        if (!$context->user->isAuthenticated()) {
            return;
        }

        $context->response->addJavaScript('/plugins/sfTranslatePlugin/js/l10n_client', 'last');
        $context->response->addStylesheet('/plugins/sfTranslatePlugin/css/l10n_client', 'last');
    }

    /**
     * @see sfPluginConfiguration
     */
    public function initialize()
    {
        $this->dispatcher->connect('context.load_factories', [$this, 'contextLoadFactories']);

        $enabledModules = sfConfig::get('sf_enabled_modules');
        $enabledModules[] = 'sfTranslatePlugin';
        sfConfig::set('sf_enabled_modules', $enabledModules);
    }
}
