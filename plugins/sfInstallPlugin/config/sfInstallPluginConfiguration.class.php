<?php

/**
 * sfInstallPlugin configuration.
 *
 * @package     sfInstallPlugin
 * @subpackage  config
 * @author      Your name here
 */
class sfInstallPluginConfiguration extends sfPluginConfiguration
{
  public function applicationThrowException(sfEvent $event)
  {
    $context = sfContext::getInstance();

    // Check $event->getSubject() is PropelException?
    if ('sfInstallPlugin' != $context->request->module
      && !file_exists(sfConfig::get('sf_config_dir').'/config.php'))
    {
      $context->controller->redirect(array('module' => 'sfInstallPlugin', 'action' => 'index'));
    }
  }

  public function controllerChangeAction(sfEvent $event)
  {
    $controller = $event->getSubject();

    if ('sfInstallPlugin' != $event->module)
    {
      return;
    }

    $credential = $controller->getActionStack()->getLastEntry()->getActionInstance()->getCredential();
    if (sfContext::getInstance()->user->hasCredential($credential))
    {
      return;
    }

    $criteria = new Criteria;
    $criteria->add(QubitAclGroupI18n::NAME, $credential);
    $criteria->addJoin(QubitAclGroupI18n::ID, QubitAclGroup::ID);
    $criteria->addJoin(QubitAclGroup::ID, QubitAclUserGroup::GROUP_ID);
    $criteria->addJoin(QubitAclUserGroup::USER_ID, QubitUser::ID);

    // If for any reason the database can't be accessed, e.g.
    //  * config.php doesn't exist
    //  * config.php is misconfigured
    //  * the database is empty
    //
    //  - or if no user exists with the necessary credential, then grant access
    // to install actions
    //
    // This could only present a vulnerability if the database can't be
    // accessed, or if no user exists with the necessary credential.  If the
    // database can't be accessed, then it isn't vulneralbe.  The filesystem is
    // vulnerable, so we must be careful not to read or write anything
    // sensitive.  We erase the database, but it isn't vulnerable
    //
    // Previously we granted sessions access to install actions if config.php
    // was missing, because this suggests that someone can access to the
    // filesystem - but we didn't link a specific session with access to the
    // filesystem, like Gallery login.txt
    //
    // One vulnerability is that anyone who gains the necessary credential on
    // one site, and knows the database username and password of another site,
    // can erase that database.  To fix this, sessions should be bound to a key
    // stored in the database.  This is superior to,
    // http://trac.symfony-project.org/ticket/5683
    //
    // If one database can't be accessed, then anyone can reconfigure the
    // database username and password, but other databases are safe as long as
    // a user exists with the necessary credential
    //
    // Another vulnerability is that databases with incompatible schemas can be
    // erased.  To fix this, we must know the database username and password to
    // reconfigure it.  The currently configured database can be erased if it's
    // schema is incombatible, but this isn't a vulnerability
    try
    {
      if (1 > count(QubitUser::get($criteria)))
      {
        return;
      }
    }
    catch (PropelException $e)
    {
      return;
    }

    $event->getSubject()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

    throw new sfStopException;
  }

  public function routingLoadConfiguration(sfEvent $event)
  {
    $routing = $event->getSubject();

    $routing->insertRouteBefore('default', 'sfInstallPlugin/help', new sfRoute('http://accesstomemory.org/wiki/index.php?title=Installer_warnings', array('module' => 'sfInstallPlugin', 'action' => 'help')));

    // See QubitMetadataResource
  }

  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    // Enable sfInstallPlugin module
    $enabledModules = sfConfig::get('sf_enabled_modules');
    $enabledModules[] = 'sfInstallPlugin';
    sfConfig::set('sf_enabled_modules', $enabledModules);

    // Launch installer if config.php does not exist
    $this->dispatcher->connect('application.throw_exception', array($this, 'applicationThrowException'));

    // Restrict sfInstallPlugin usage
    $this->dispatcher->connect('controller.change_action', array($this, 'controllerChangeAction'));

    // Connect event listener to add routes
    $this->dispatcher->connect('routing.load_configuration', array($this, 'routingLoadConfiguration'));
  }
}
