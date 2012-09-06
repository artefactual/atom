<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'xfBaseTask.class.php';

/**
 * Task to initialize a search module.
 *
 * @package sfSearch
 * @subpackage Task
 * @author Carl Vondrick
 */
final class xfInitInterfaceTask extends xfBaseTask
{
  /**
   * Configures the task.
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('module', sfCommandArgument::REQUIRED, 'The module name'),
      new sfCommandArgument('index', sfCommandArgument::REQUIRED, 'The index name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('theme', null, sfCommandOption::PARAMETER_REQUIRED, 'The theme name', 'default')
    ));

    $this->namespace = 'search';
    $this->name = 'init-interface';

    $this->briefDescription = 'Initializes a basic search module.';
    $this->detailedDescription = <<<EOF
The [search:init-interface|INFO] generates a sfSearch interface module:

  [./symfony search:init-interface frontend search MySearch|INFO]

The task creates a [%module%|COMMENT] module in the [%application%|COMMENT] application 
for the index class [%index%|COMMENT].

The created module is an empty one that inherits its actions and templates
from a runtime generated module in [%sf_app_cache_dir%/modules/auto%module%|COMMENT].
EOF;
  }

  /**
   * Rebuilds an index.
   *
   * @param array $arguments
   * @param array $options
   */
  public function execute($arguments = array(), $options = array())
  {
    $this->checkIndexExists($arguments['index']);

    $properties = parse_ini_file(sfConfig::get('sf_config_dir') . '/properties.ini', true);

    $constants = array(
      'PROJECT_NAME'  => isset($properties['symfony']['name']) ? $properties['symfony']['name'] : 'symfony',
      'APP_NAME'      => $arguments['application'],
      'MODULE_NAME'   => $arguments['module'],
      'INDEX_CLASS'   => $arguments['index'],
      'AUTHOR_NAME'   => isset($properties['symfony']['author']) ? $properties['symfony']['author'] : 'Your name here',
      'THEME'         => $options['theme'],
    );

    $moduleDir = sfConfig::get('sf_app_module_dir') . '/' . $arguments['module'];

    // create module structure
    $finder = sfFinder::type('any')->discard('.sf');
    
    foreach ($this->configuration->getGeneratorSkeletonDirs('sfSearchInterface', $options['theme']) as $dir)
    {
      if (is_dir($dir))
      {
        $this->getFilesystem()->mirror($dir, $moduleDir, $finder);
        break;
      }
    }

    $finder = sfFinder::type('file')->name('*.php', '*.yml');
    $this->getFilesystem()->replaceTokens($finder->in($moduleDir), '##', '##', $constants);
  }
}
