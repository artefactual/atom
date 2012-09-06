<?php

class testGearmanTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'test'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'test';
    $this->name             = 'gearman';
    $this->briefDescription = 'Test gearman plugin';
    $this->detailedDescription = <<<EOF
The [test:gearman|INFO] task does things.
Call it with:

  [php symfony test:gearman|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $this->log('Dont forget to launch in parallel : [./symfony gearman:worker-doctrine --config=test --verbose]');

    $o = Doctrine::getTable('TestArticle')->create();
    $o->title = 'test';
    $o->save();

    $o->title = 'retest';
    $o->save();

    $o->delete();

    echo $o->task('publish', mt_rand()),"\n";

    echo Doctrine::getTable('TestArticle')->task('publish', mt_rand()),"\n";
  }
}
