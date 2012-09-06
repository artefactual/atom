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
 * Task to initialize an index
 *
 * @package sfSearch
 * @subpackage Task
 * @author Carl Vondrick
 */
final class xfInitIndexTask extends xfBaseTask
{
  /**
   * Configures the task.
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('index', sfCommandArgument::REQUIRED, 'The index name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('group', 'g', sfCommandOption::PARAMETER_NONE, 'Create a search group instead of an index'),
      new sfCommandOption('dir', null, sfCommandOption::PARAMETER_OPTIONAL, 'The directory to create the index in', 'lib/search'),
    ));

    $this->namespace = 'search';
    $this->name = 'init-index';

    $this->briefDescription = 'Initializes a skeleton search index.';
    $this->detailedDescription = <<<EOF
The [search:init-index|INFO] generates a sfSearch index:

  [./symfony search:init-index MySearch|INFO]

By default, the task creates an index named [%index%|COMMENT] in
the [lib/search|COMMENT] directory.  If you want to specify a different
directory, use the [--dir|COMMENT] option:
  
  [./symfony search:init-index --dir=lib/find MySearch|INFO]

However, it is recommended you put search indices in [lib/search|COMMENT]

The task can also generate index groups which combine multiple indices
together that share a common service registry.  Pass the [--group|COMMENT]
option to create an index group:
  
  [./symfony search:init-index --group MySearchGroup|INFO]

Keep in mind that you can always create search indices and groups through
manual file creation.
EOF;
  }

  /**
   * Initializes an index
   *
   * @param array $arguments
   * @param array $options
   */
  public function execute($arguments = array(), $options = array())
  {
    $index = $arguments['index'];

    if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $index))
    {
      throw new sfException('Index name must match start with a letter and contain only alphanumeric characters.');
    }

    $properties = parse_ini_file(sfConfig::get('sf_config_dir') . '/properties.ini', true);

    $project  = isset($properties['symfony']['name'])   ? $properties['symfony']['name']   : 'symfony';
    $author   = isset($properties['symfony']['author']) ? $properties['symfony']['author'] : 'Your name here';

    $singleContent = <<<INDEX
<?php

/**
 * $index search index.
 *
 * @package     $project
 * @subpackage  search
 * @author      $author
 * @version     SVN: \$Id: xfInitIndexTask.class.php 3071 2009-08-25 20:01:41Z jablko $
 */
class $index extends xfIndexSingle
{
  /**
   * Configures initial state of search index by setting a name.
   *
   * @see xfIndex
   */
  protected function initialize()
  {
    \$this->setName('$index');
  }

  /**
   * Configures the search index by setting up a search engine and service
   * registry.
   *
   * @see xfIndex
   */
  protected function configure()
  {
    // The ->configure() method setups the search index so it knows how to
    // behave.  You must setup a search engine and a search registry so the
    // index knows whats to index and how to index it.
    //
    // This method is analogous to ->configure() in sfForm.  In fact, it has the
    // same purpose and follows similar logic.
    //
    // Consider the following examples as you setup your index:
    //
    // Setup the backend engine:
    //
    //    \$this->setEngine(new MyEngine('...'));
    // 
    // Setup the services:
    //
    //    \$s1 = new xfService(new MyIdentifier('...'));
    //    \$s1->addBuilder(new MyBuilder(array(
    //                                        new xfField('foo', xfField::KEYWORD),
    //                                        new xfField('bar', xfField::TEXT)
    //                                  ));
    //    \$s1->addRetort(new xfRetortField);
    //    \$s1->addRetort(new xfRetortRoute('module/action?param=%foo%'));
    //
    //    \$this->getServiceRegistry()->register(\$s1);
    //
    // Repeat for each service you require.
    //
    // After you have configured the index, you should populate it.  Do this by
    // running the symfony task:
    //  
    //    $ ./symfony search:populate $index
    // 
    // For more information, please see the documentation included in the
    // sfSearch package.
  }
}
INDEX;

    $groupContent = <<<INDEX
<?php

/**
 * $index search group index.
 *
 * @package     $project
 * @subpackage  search
 * @author      $author
 * @version     SVN: \$Id: xfInitIndexTask.class.php 3071 2009-08-25 20:01:41Z jablko $
 */
class $index extends xfIndexGroup
{
  /**
   * Configures initial state of search group index by setting a name.
   *
   * @see xfIndex
   */
  protected function initialize()
  {
    \$this->setName('$index');
  }

  /**
   * Configures the search index by setting up a service registry and child indices.
   *
   * @see xfIndex
   */
  protected function configure()
  {
    // The ->configure() method setups the search index so it knows how to
    // behave.  You must setup a service registry and all child indices.
    //
    // This method is analogous to ->configure() in sfForm.  In fact, it has the
    // same purpose and follows similar logic.
    //
    // Consider the following examples as you setup your index:
    // 
    // Setup the services:
    //
    //    \$s1 = new xfService(new MyIdentifier('...'));
    //    \$s1->addBuilder(new MyBuilder(array(
    //                                        new xfField('foo', xfField::KEYWORD),
    //                                        new xfField('bar', xfField::TEXT)
    //                                  ));
    //    \$s1->addRetort(new xfRetortField);
    //    \$s1->addRetort(new xfRetortRoute('module/action?param=%foo%'));
    //
    //    \$this->getServiceRegistry()->register(\$s1);
    //
    // Repeat for each service you require.
    //
    // Setup child indices:
    //
    //    \$this->addIndex(new {$index}ChildOne, 'child1');
    //    \$this->addIndex(new {$index}ChildTwo, 'child2');
    //
    // After you have configured the index, you should populate it.  Do this by
    // running the symfony task:
    //  
    //    $ ./symfony search:populate $index
    // 
    // For more information, please see the documentation included in the
    // sfSearch package.
  }
}
INDEX;

    if (!is_readable(sfConfig::get('sf_root_dir') . '/' . $options['dir']))
    {
      $this->getFilesystem()->mkdirs(str_replace('/', DIRECTORY_SEPARATOR, $options['dir']));
    }

    $file = sfConfig::get('sf_root_dir') . '/' . $options['dir'] . '/' . $index . '.class.php';

    if (is_readable($file))
    {
      throw new sfException(sprintf('The index "%s" already exists in "%s"', $index, $file));
    }

    if ($options['group'])
    {
      $content = $groupContent;
    }
    else
    {
      $content = $singleContent;
    }

    $this->logSection('search', sprintf('Creating "%s" index skeleton', $file));
    file_put_contents($file, $content);
  }
}
