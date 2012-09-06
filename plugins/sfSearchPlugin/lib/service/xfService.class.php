<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A service to control part of an index.
 *
 * @package sfSearch
 * @subpackage Service
 * @author Carl Vondrick
 */
class xfService
{
  /**
   * The identifier, which identifies this service.
   *
   * @var xfIdentifier
   */
  private $identifier;

  /**
   * Builders for this service.
   *
   * @var array
   */
  private $builders = array();

  /**
   * Retorts for this service.
   *
   * @var array
   */
  private $retorts = array();

  /**
   * Options for this service.
   *
   * @var array
   */
  private $options = array();

  /**
   * Constructor to set initial values.
   *
   * @param xfIdentifier $identifier The identifier
   */
  final public function __construct(xfIdentifier $identifier)
  {
    $this->identifier = $identifier;

    $this->configure();
  }

  /**
   * Runs an internal setup procedure.  If you wish to create 'packaged'
   * services, overload this method.  You are, however, strongly encouraged to
   * avoid packaged services.
   */
  protected function configure()
  {
    // nothing to do
  }

  /**
   * Returns the identifier.
   *
   * @returns xfIdentifier
   */
  final public function getIdentifier()
  {
    return $this->identifier;
  }

  /**
   * Adds a builder to the service.
   *
   * @param xfBuilder $builder
   */
  final public function addBuilder(xfBuilder $builder)
  {
    $this->builders[] = $builder;
  }

  /**
   * Builds a document from this service.
   *
   * @param mixed $input The input to build off
   * @returns xfDocument
   */
  final public function buildDocument($input)
  {
    $doc = new xfDocument($this->getIdentifier()->getGuid($input));
    $doc->addField(new xfFieldValue(new xfField('_service', xfField::UNINDEXED), $this->getIdentifier()->getName()));

    foreach ($this->builders as $builder)
    {
      $builder->build($input, $doc);
    }

    return $doc;
  }

  /**
   * Adds a retort to the service.
   *
   * @param xfRetort $responer
   */
  final public function addRetort(xfRetort $retort)
  {
    $this->retorts[] = $retort;
  }

  /**
   * Gets all the retorts.
   *
   * @returns array
   */
  final public function getRetorts()
  {
    return $this->retorts;
  }

  /**
   * Sets an option
   *
   * @param string $name The option name
   * @param mixed $value The option value
   */
  final public function setOption($name, $value)
  {
    $this->options[$name] = $value;
  }

  /**
   * Gets an option
   *
   * @param string $name The option name
   * @param mixed $default The default response (optional)
   * @returns mixed The option value
   */
  final public function getOption($name, $default = null)
  {
    if (isset($this->options[$name]))
    {
      return $this->options[$name];
    }

    return $default;
  }

  /**
   * Tests to see if an option exists.
   *
   * @param string $name The option name
   * @returns bool true if it exists, false otherwise
   */
  final public function hasOption($name)
  {
    return isset($this->options[$name]);
  }
}
