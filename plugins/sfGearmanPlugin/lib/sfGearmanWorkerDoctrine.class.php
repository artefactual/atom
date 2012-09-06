<?php

/**
 * Gearman worker for doctrine jobs
 *
 * @uses sfGearmanWorker
 * @package   sfGearmanPlugin
 * @author    Benjamin VIELLARD <bicou@bicou.com>
 * @license   The MIT License
 * @version   SVN: $Id: sfGearmanWorkerDoctrine.class.php 29482 2010-05-16 17:11:45Z bicou $
 */
class sfGearmanWorkerDoctrine extends sfGearmanWorker
{
  /**
   * Configure doctrine worker
   *
   * @access public
   * @return void
   */
  public function configure()
  {
    // configure the doctrine worker
    if (($config = $this->getParameter('config')) !== null)
    {
      // use a config key from gearman.yml worker section
      $configuration = sfGearman::getDoctrine($config);
      foreach ($configuration as $model => $methods)
      {
        $this->addFunctions($model, $methods);
      }
    }
    elseif (($model = $this->getParameter('model')) !== null)
    {
      // target a specific model
      if (($methods = $this->getParameters('methods')) !== null)
      {
        // --methods=insert,update,delete,...
        $this->addFunctions($model, explode(',', $methods));
      }
      else
      {
        // use template methods defined in schema.yml
        $this->addFunctions($model);
      }
    }
    else
    {
      // load all models and add all methods
      Doctrine::loadModels(sfConfig::get('sf_lib_dir').'/model');
      foreach (array_keys(Doctrine::getLoadedModelFiles()) as $model)
      {
        $this->addFunctions($model);
      }
    }
  }

  /**
   * recursive add functions to worker
   * loop through Gearmanable template for events if needed
   *
   * @param string $model  Doctrine model name
   * @param mixed  $method Doctrine methods
   *
   * @return void
   */
  public function addFunctions($model, $method = null)
  {
    if (is_array($method))
    {
      // loop
      foreach ($method as $f)
      {
        $this->addFunctions($model, $f);
      }
    }
    elseif ($method === null)
    {
      // install every event defined in schema.yml
      $table = Doctrine::getTable($model);
      if ($table->hasTemplate('Doctrine_Template_Gearmanable'))
      {
        $template = $table->getTemplate('Doctrine_Template_Gearmanable');
        $this->addFunctions($model, $template->getOption('events'));
      }
    }
    else
    {
      $method = trim($method);

      // convert special event to trigger%Event%
      if (in_array(strtolower($method), array('insert', 'update', 'delete')))
      {
        $method = 'trigger'.ucfirst(strtolower($method));
      }

      // attach to functions named "Model.Method"
      $this->addFunction($model.'.'.$method, array($this, 'handler'));
    }
  }

  /**
   * Gearman work handler
   *
   * @param GearmanJob $job  The gearman job
   * @return string
   */
  public function handler($job)
  {
    // notify job event
    $this->notifyEventJob($job);

    // extract model and method from gearman function name
    list($model, $method) = explode('.', $job->functionName());

    // unserialize workload
    $workload = unserialize($job->workload());
    if ($workload === false)
    {
      $job->sendWarning('Unable to unserialize workload');
      $job->sendFail();
      return;
    }

    // restore things
    $object    = isset($workload['record']) ? $workload['record'] : Doctrine::getTable($model);
    $arguments = isset($workload['arguments']) ? $workload['arguments'] : array();
    $result    = null;

    // attach gearman job to object
    $object->setGearmanJob($job);

    // call workMethod
    try
    {
      $result = call_user_func_array(array($object, $method), $arguments);
    }
    catch(Exception $e)
    {
      $job->sendWarning($e->getMessage());
      $job->sendFail();
      throw $e;
    }

    // free record
    if ($object instanceof Doctrine_Record)
    {
      $object->free(true);
    }

    // gearman accepts strings only
    return sfGearman::serialize($result);
  }
}

