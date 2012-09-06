<?php

class TestArticleTable extends Doctrine_Table
{
  public static function getInstance()
  {
    return Doctrine_Core::getTable('TestArticle');
  }

  public function publish($arg)
  {
    echo __METHOD__,"\n";
    $job = $this->getGearmanJob();
    echo $job->handle(),"\n";
    echo $job->functionName(),"\n";
    echo $job->unique(),"\n";
    echo $arg,"\n";

    return __METHOD__.'('.$arg.')';
  }
}
