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
 * A task to query the index
 *
 * @package sfSearch
 * @subpackage Task
 * @author Carl Vondrick
 */
final class xfQueryTask extends xfBaseTask
{
  /**
   * Configures the task.
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('index', sfCommandArgument::REQUIRED, 'The index name to query'),
      new sfCommandArgument('query', sfCommandArgument::REQUIRED, 'The search query string')
    ));

    $this->addOptions(array(
      new sfCommandOption('limit', 'l', sfCommandOption::PARAMETER_REQUIRED, 'How many results to display', 3),
      new sfCommandOption('page', 'p', sfCommandOption::PARAMETER_REQUIRED, 'The page to display', 1),
      new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_NONE, 'If passed, displays more information')
    ));


    $this->namespace = 'search';
    $this->name = 'query';

    $this->briefDescription = 'Queries a search index';
    $this->detailedDescription = <<<EOF
The [search:query|INFO] task searches an index with the querystring
and returns all results.

  [./symfony search:query MySearch "search query"|INFO]

This task is useful for testing your index to determine exactly what it
is indexing. It will list every document by GUID that it matches and 
all its fields.

This task displays only the first 10 results by default.  To jump to the 
next page, pass the [--page|INFO] option:

  [./symfony search:query MySearch "search query" --page=2|INFO]

To change how many results are displayed on each run, pass the [--limit|INFO]
option:

  [./symfony search:query MySearch "search query" --limit=20 --page=2|INFO]

By default, the task will display as little as neccessary.  If you want to
display everything, then pass the [--verbose|INFO] option:

  [./symfony search:query MySearch "search query" --verbose|INFO]

This task is intended for debugging use.  If you are looking to integrate
search results into something else, please use the sfSearch API.

Fields that have asterisks * next to them are indexed, meaning they
will match search queries. Boosts for fields are indicated by yellow
Bnum where num is the boost.  For example, B2.00 represents a boost
of 2.00.
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

    $prefix = $this->formatter->format($arguments['index'], array('fg' => 'green', 'bold' => true)) . ' >> ';


    $index = new $index;
    $index->setLogger(new xfLoggerTask($this->dispatcher, $this->formatter));

    $parser = new xfParserLucene();
    $criteria = $parser->parse($arguments['query']);

    $this->log($prefix . 'Query ' . $this->formatter->format($criteria->toString(), array('fg' => 'blue', 'bold' => true)));

    $results = $index->find($criteria);
    $pager = new xfPager($results);
    $pager->setPerPage($options['limit']);
    $pager->setPage($options['page']);

    if ($pager->getNbResults() == 0)
    {
      $this->log($prefix . $this->formatter->format('No', array('fg' => 'red', 'bold' => true)) . ' results found.');

      return;
    }
    elseif ($pager->getNbResults() == 1)
    {
      $this->log($prefix . $this->formatter->format('1', array('fg' => 'blue', 'bold' => true)) . ' result found:');
    }
    else
    {
      $this->log($prefix . $this->formatter->format($pager->getNbResults(), array('fg' => 'blue', 'bold' => true)) . ' results found:');
    }

    if ($pager->getStartPosition() > 1)
    {
      $msg = ($pager->getStartPosition() - 1) . ' skipped';
      $this->log('');
      $this->log($this->formatter->format($msg, array('fg' => 'yellow')));
    }

    $count = $pager->getStartPosition();
    foreach ($pager->getResults() as $result)
    {
      $this->processHit($result, $count, $options['verbose']);

      $count++;
    }

    if (0 < $diff = $pager->getNbResults() - $pager->getEndPosition())
    {
      $msg = $diff . ' remaining';
      $this->log('');
      $this->log($this->formatter->format($msg, array('fg' => 'yellow')));
    }
  }

  /**
   * Processes a result
   *
   * @param xfDocumentHit $result
   * @param int $count
   * @param bool $verbose
   */
  private function processHit(xfDocumentHit $result, $count, $verbose = false)
  {
    $this->log('');
    $this->log($count . ') ' . $this->formatter->format($result->getDocument()->getGuid(), array('fg' => 'blue', 'bold' => true)) . ':');
    
    foreach ($result->getDocument()->getFields() as $field)
    {
      $this->processField($field, $verbose);
    }

    foreach ($result->getOptions() as $key => $value)
    {
      $this->outputRow($key, $value, 'red');
    }
  }

  /**
   * Processes a field
   *
   * @param xfFieldValue $field
   * @param bool $verbose
   */
  private function processField(xfFieldValue $field, $verbose = false)
  {
    $name = $field->getField()->getName();
    $value = $field->getValue();

    if (strlen($value) > 175 && !$verbose)
    {
      $value = rtrim(substr($value, 0, 175)) . $this->formatter->format('...', array('fg' => 'red', 'bold' => true));
    }

    if ($field->getField()->getBoost() != 1)
    {
      $value = $this->formatter->format('B' . number_format($field->getField()->getBoost(), 2), array('fg' => 'yellow', 'bold' => true)) . ' ' . $value;
    }

    $value = wordwrap($value, 66);
    $value = str_replace("\n", "\n" . str_repeat(' ', 17), $value);

    if ($field->getField()->getType() & xfField::INDEXED)
    {
      $name = '*' . $name;
    }

    $this->outputRow($name, $value, 'green');
  }

  /**
   * Outputs info in a list to the dispatcher.
   *
   * @param string $key
   * @param string|array $value
   */
  private function outputRow($key, $value, $color = 'green')
  {
    if (is_array($value))
    {
      $value = implode($value, ', ');
    }

    $value = str_replace(sfConfig::get('sf_root_dir') . '/', '', $value);

    // str_pad() doesn't like ansi formatting
    $padding = str_repeat(' ', 15 - strlen($key));

    $this->log($padding . $this->formatter->format($key, array('fg' => $color, 'bold' => true)) . ':' . ' ' . $value); 
  }
}
