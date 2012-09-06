<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../bootstrap/functional.php';
bootstrap('frontend');

class GeneratorTester
{
  public $t;

  public function __construct()
  {
    $this->t = new lime_test(null, new lime_output_color);
  }

  public function customize($params)
  {
    $params['moduleName']  = 'search';

    sfToolkit::clearDirectory(sfConfig::get('sf_app_cache_dir'));

    $generatorManager = new sfGeneratorManager(sfProjectConfiguration::getActive());
    sfGeneratorConfigHandler::getContent($generatorManager, 'xfGeneratorInterface', $params);
  }

  public function test($params, $expect, $msg)
  {
    try
    {
      $this->customize($params);

      if ($expect)
      {
        $this->t->fail($msg);
      }
      else
      {
        $this->t->pass($msg);
      }
    } 
    catch (Exception $e)
    {
      $this->t->diag($e->getMessage());
      if ($expect)
      {
        $this->t->pass($msg);
      }
      else
      {
        $this->t->fail($msg);
      }
    }


    return $this;
  }
}

class Barfoo
{
}

class GoodForm extends xfSimpleFormBase
{
}


$t = new GeneratorTester;
$t->
  test(array(), true, 'generator fails if missing a required param')->
  test(array('index_class' => 'foobar'), true, 'generator fails is "index_class" does not exist')->
  test(array('index_class' => 'TestSearch', 'theme' => 'foobar'), true, 'generator fails if "theme" does not exist')->
  test(array('index_class' => 'TestSearch', 'simple' => array('form' => array('class' => 'Foobar'))), true, 'generator fails if "simple.form.class" does not exist')->
  test(array('index_class' => 'TestSearch', 'simple' => array('form' => array('class' => 'Barfoo'))), true, 'generator fails if "simple.form.class" does not extend "xfForm"')->
  test(array('index_class' => 'TestSearch', 'simple' => array('form' => array('class' => 'GoodForm'))), false, 'generator does not fail if "simple.form.class" is OK')
  ;
