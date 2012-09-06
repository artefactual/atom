<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The generator for the search interface.
 *
 * @package sfSearch
 * @subpackage Generator
 * @author Carl Vondrick
 */
final class xfGeneratorInterface extends sfGenerator
{
  /**
   * The parameters.
   *
   * @var array
   */
  private $params = array();

  /**
   * @see sfGenerator
   */
  public function initialize(sfGeneratorManager $m)
  {
    parent::initialize($m);

    $this->setGeneratorClass('sfSearchInterface');
  }

  /**
   * @see sfGenerator
   */
  public function generate($params = array())
  {
    $this->params = $params;

    foreach (array('index_class', 'moduleName') as $required)
    {
      if (!isset($this->params[$required]))
      {
        throw new xfGeneratorException('You must specify "' . $required . '".');
      }
    }

    if (!class_exists($this->params['index_class']))
    {
      throw new xfGeneratorException('Unable to build interface for nonexistant index "' . $this->params['index_class'] . '"');
    }

    if (null !== $form = $this->get('simple.form.class', null))
    {
      $reflection = new ReflectionClass($form);
      
      if (!$reflection->isSubClassOf(new ReflectionClass('xfForm')))
      {
        throw new xfGeneratorException('Form class must extend xfForm');
      }
    }

    // check to see if theme exists
    if (!isset($this->params['theme']))
    {
      $this->params['theme'] = 'default';
    }

    $themeDir = $this->generatorManager->getConfiguration()->getGeneratorTemplate($this->getGeneratorClass(), $this->params['theme'], '');

    $this->setGeneratedModuleName('auto' . ucfirst($this->params['moduleName']));
    $this->setModuleName($this->params['moduleName']);
    $this->setTheme($this->params['theme']);

    $themeFiles = sfFinder::type('file')->relative()->discard('.*')->in($themeDir);

    $this->generatePhpFiles($this->generatedModuleName, $themeFiles);

    $data = "require_once(sfConfig::get('sf_module_cache_dir') . '/" . $this->generatedModuleName . "/actions/actions.class.php');"; 
    $data .= "require_once(sfConfig::get('sf_module_cache_dir') . '/" . $this->generatedModuleName . "/actions/components.class.php');";

    return $data;
  }   

  /**
   * A shortcut for templates to get parameters.
   *
   * @param string $name The param name
   * @param mixed $default The default response, if param does not exist
   * @returns mixed The response
   */
  protected function get($name, $default = null)
  {
    $response = $this->params;

    foreach (explode('.', $name) as $part)
    {
      if (!isset($response[$part]))
      {
        return $default;
      }

      $response = $response[$part];
    }

    return $response;
  }
}
