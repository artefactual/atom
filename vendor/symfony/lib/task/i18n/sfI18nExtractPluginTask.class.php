<?php

/*
 * Current known limitations:
 *   - Can only works with the default "messages" catalogue
 *   - For file backends (XLIFF and gettext), it only saves/deletes strings in the "most global" file
 */

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extracts i18n strings from php files.
 *
 * @package    symfony
 * @subpackage task
 * @author     David Juhasz <david@artefactual.com>
 * @version    SVN: $Id: sfI18nExtractTask.class.php 9883 2008-06-26 09:04:13Z FabianLange $
 */
class sfI18nExtractPluginTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('plugin', sfCommandArgument::REQUIRED, 'The plugin name'),
      new sfCommandArgument('culture', sfCommandArgument::REQUIRED, 'The target culture')
    ));

    $this->addOptions(array(

      // http://trac.symfony-project.org/ticket/8352
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', true),

      new sfCommandOption('display-new', null, sfCommandOption::PARAMETER_NONE, 'Output all new found strings'),
      new sfCommandOption('display-old', null, sfCommandOption::PARAMETER_NONE, 'Output all old strings'),
      new sfCommandOption('auto-save', null, sfCommandOption::PARAMETER_NONE, 'Save the new strings'),
      new sfCommandOption('auto-delete', null, sfCommandOption::PARAMETER_NONE, 'Delete old strings'),
    ));

    $this->namespace = 'i18n';
    $this->name = 'extractPlugin';
    $this->briefDescription = 'Extracts i18n strings for a plugin from php files';

    $this->detailedDescription = <<<EOF
The [i18n:extractPlugin sfPluginName|INFO] task extracts i18n strings from your project files
for the given plugin and target culture:

  [./symfony i18n:extractPlugin sfPluginName fr|INFO]

By default, the task only displays the number of new and old strings
it found in the current project.

If you want to display the new strings, use the [--display-new|COMMENT] option:

  [./symfony i18n:extractPlugin sfPluginName --display-new fr|INFO]

To save them in the i18n message catalogue, use the [--auto-save|COMMENT] option:

  [./symfony i18n:extractPlugin sfPluginName --auto-save fr|INFO]

If you want to display strings that are present in the i18n messages
catalogue but are not found in the plugin, use the 
[--display-old|COMMENT] option:

  [./symfony i18n:extractPlugin sfPluginName --display-old fr|INFO]

To automatically delete old strings, use the [--auto-delete|COMMENT] but
be careful, especially if you have translations for plugins as they will
appear as old strings but they are not:

  [./symfony i18n:extractPlugin sfPluginName --auto-delete fr|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $path = sfConfig::get('sf_plugins_dir').'/'.$arguments['plugin'];
    if (!is_dir($path))
    {
      throw new sfException('The plugin you specified is not valid.');
    }

    $this->logSection('i18n', sprintf('extracting i18n strings for the "%s" plugin', $arguments['plugin']));

    // get i18n configuration from factories.yml
    $config = sfFactoryConfigHandler::getConfiguration($this->configuration->getConfigPaths('config/factories.yml'));

    $class = $config['i18n']['class'];
    $params = $config['i18n']['param'];
    unset($params['cache']);

    $extract = new sfI18nPluginExtract(new $class($this->configuration, new sfNoCache(), $params), $arguments['culture'], array('path' => $path));

    $extract->extract();

    $this->logSection('i18n', sprintf('found "%d" new i18n strings', count($extract->getNewMessages())));
    $this->logSection('i18n', sprintf('found "%d" old i18n strings', count($extract->getOldMessages())));

    if ($options['display-new'])
    {
      $this->logSection('i18n', sprintf('display new i18n strings', count($extract->getOldMessages())));
      foreach ($extract->getNewMessages() as $message)
      {
        $this->log('               '.$message."\n");
      }
    }

    if ($options['auto-save'])
    {
      $this->logSection('i18n', 'saving new i18n strings');

      $extract->saveNewMessages();
    }

    if ($options['display-old'])
    {
      $this->logSection('i18n', sprintf('display old i18n strings', count($extract->getOldMessages())));
      foreach ($extract->getOldMessages() as $message)
      {
        $this->log('               '.$message."\n");
      }
    }

    if ($options['auto-delete'])
    {
      $this->logSection('i18n', 'deleting old i18n strings');

      $extract->deleteOldMessages();
    }
  }
}
