<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A task to populate a index.
 *
 * @package sfSearch
 * @subpackage Task
 * @author Carl Vondrick
 */
final class xfPopulateTask extends xfBaseTask
{
  /**
   * Configures the task.
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('index', sfCommandArgument::REQUIRED, 'The index name to populate')));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
//      new sfCommandOption('optimize', 'o', sfCommandOption::PARAMETER_NONE, 'If passed, the index is optimized after population'),
      new sfCommandOption('verbose', 'v', sfCommandOption::PARAMETER_NONE, 'If passed, progress is displayed for each object indexed'),
      new sfCommandOption('skip', 'k', sfCommandOption::PARAMETER_REQUIRED, 'Only skip "actors" or "io" (information objects)')));

    $this->namespace = 'search';
    $this->name = 'populate';

    $this->briefDescription = 'Populates a search index';
    $this->detailedDescription = <<<EOF
The [search:populate|INFO] task empties, populates, and optimizes an index
in the current project:

  [./symfony search:populate MySearch|INFO]

This task may take quite a while to run, depending on your configuration.
Avoid running this task on a production site, as it's primary purpose is
for development and testing.
EOF;
  }

  /**
   * Populates an index.
   *
   * @param array $arguments
   * @param array $options
   */
  public function execute($arguments = array(), $options = array())
  {
    $index = $arguments['index'];

    $this->checkIndexExists($index);

    new sfDatabaseManager($this->configuration);

    $index = new $index;
    $index->setLogger(new xfLoggerTask($this->dispatcher, $this->formatter));
    $index->qubitPopulate($options);
    $index->getEngine()->commit();
    $index->optimize();
  }
}
