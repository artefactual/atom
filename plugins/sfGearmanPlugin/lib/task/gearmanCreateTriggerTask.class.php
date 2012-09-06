<?php

/**
 * Create MySQL trigger for model events
 *
 * @package     sfGearmanPlugin
 * @subpackage  task
 * @author      Benjamin VIELLARD <bicou@bicou.com>
 * @license     The MIT License
 * @version     SVN: $Id: gearmanCreateTriggerTask.class.php 32981 2011-09-02 08:10:19Z bicou $
 */
class gearmanCreateTriggerTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'backend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('queue', null, sfCommandOption::PARAMETER_REQUIRED, 'The gearman job queue name', '__mysql_trigger'),
      // add your own options here
    ));

    $this->namespace        = 'gearman';
    $this->name             = 'create-trigger';
    $this->briefDescription = 'Create gearman triggers';
    $this->detailedDescription = <<<EOF
The [gearman:create-trigger|INFO] task install MySQL triggers in database which send a task to a gearman server for every data modification.
Call it with:

  [php symfony gearman:create-trigger|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration);

    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    // loop through all models with a Gearmanable template
    // and install trigger if option is set to 'mysql'
    Doctrine::loadModels(sfConfig::get('sf_lib_dir').'/model');
    foreach (array_keys(Doctrine::getLoadedModelFiles()) as $model)
    {
      $table = Doctrine::getTable($model);
      if ($table->hasTemplate('Doctrine_Template_Gearmanable'))
      {
        $template = $table->getTemplate('Doctrine_Template_Gearmanable');
        if ($template->getOption('trigger') == 'mysql')
        {
          $this->logSection('model', $model);
          foreach ($template->getOption('events') as $event)
          {
            $this->installTrigger($model, $table, $event, $options['queue']);
          }
        }
      }
    }
  }

  protected function installTrigger($model, $table, $event, $queue)
  {
    $event = strtoupper($event);

    // skip non MySQL events
    if (!in_array($event, array('INSERT', 'UPDATE', 'DELETE')))
    {
      $this->log('Unsupported MySQL event "'.$event.'"');
      return;
    }

    $columns      = $table->getColumns();
    $table_name   = $table->getOption('tableName');
    $trigger_name = 'gearman_'.$table_name.'_'.$event;

    // first drop SQL trigger
    $drop_sql = 'DROP TRIGGER IF EXISTS '.$trigger_name;
    $this->connection->exec($drop_sql);

    // build the xql calls from model columns
    $columns_sql = array();
    foreach ($columns as $column_name => $column_definition)
    {
      $col = 'xql_element("'.$column_name.'", ';
      if (in_array($column_definition['type'], array('boolean', 'integer', 'float', 'decimal', 'string', 'timestamp', 'time', 'date', 'enum')))
      {
        // include values in xql for simple types
        switch ($event)
        {
          case 'INSERT':
            $col .= 'NEW.'.$column_name;
            break;

          case 'UPDATE':
            $col .= 'NEW.'.$column_name.', NEW.'.$column_name.' != OLD.'.$column_name.' AS modified';
            break;

          case 'DELETE':
            $col .= 'OLD.'.$column_name;
            break;
        }
      }
      else
      {
        // for complex types, replace values by NULL (the object will by in proxy state)
        $col .= 'NULL';

        // but still notify if column is modified
        if ($event == 'UPDATE')
          $col .= ', NEW.'.$column_name.' != OLD.'.$column_name.' AS modified';
      }
      $col .= ')';

      $columns_sql[] = $col;
    }

    $columns_sql = 'xql_element("columns", xql_forest('.implode(', ',$columns_sql).'))';

    // create trigger after event (launch a gearman background job)
    $trigger_sql = <<<SQL
CREATE TRIGGER $trigger_name
AFTER $event ON $table_name
FOR EACH ROW
BEGIN
  SET @gearman = gman_do_background("$queue", xql_element("trigger", $columns_sql, "$event" AS event, "$model" AS model));
END
SQL;
    $this->log($trigger_sql);
    $this->connection->exec($trigger_sql);
  }
}
