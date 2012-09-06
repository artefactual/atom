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
 * A task to optimize an index.
 *
 * @package sfSearch
 * @subpackage Task
 * @author Carl Vondrick
 */
final class xfOptimizeTask extends xfBaseTask
{
  /**
   * Configures the task.
   */
  protected function configure()
  {
    $this->addArguments(array(new sfCommandArgument('index', sfCommandArgument::REQUIRED, 'The index name to optimize')));

    $this->namespace = 'search';
    $this->name = 'optimize';

    $this->briefDescription = 'Optimizes a search index';
    $this->detailedDescription = <<<EOF
The [search:optimizes|INFO] task optimizes the index in the current project:

  [./symfony search:optimize MySearch|INFO]

This task may take a while to run, depending on your configuration.
It is OK to run this task on a production site from time-to-time,
but searching may be offline or slower during this time.

Not all search engines have an optimization feature so this task
may do nothing.
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
    $index = $arguments['index'];

    $this->checkIndexExists($index);

    $index = new $index;
    $index->setLogger(new xfLoggerTask($this->dispatcher, $this->formatter));
    $index->optimize();
  }
}
