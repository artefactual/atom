<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A service registry holds the services together.
 *
 * @package sfSearch
 * @subpackage Service
 * @author Carl Vondrick
 */
final class xfServiceRegistry
{
  /**
   * The registered services.
   *
   * @var array
   */
  private $services = array();

  /**
   * Registers a new service.
   *
   * @param xfService $service The service
   */
  public function register(xfService $service)
  {
    $this->services[$service->getIdentifier()->getName()] = $service;
  }

  /**
   * Gets a service from name.
   *
   * @param string $name The name
   * @returns xfService
   */
  public function getService($name)
  {
    if (!isset($this->services[$name]))
    {
      throw new xfServiceNotFoundException('Unable to find service with name ' . $name);
    }

    return $this->services[$name];
  }

  /**
   * Gets all the services.
   *
   * @returns array
   */
  public function getServices()
  {
    return $this->services;
  }

  /**
   * Locates a service from input. 
   *
   * @param mixed $input
   * @returns xfService
   * @throws xfServiceNotFoundException if service is not found
   */
  public function locate($input)
  {
    $ignored = null;

    foreach ($this->services as $service)
    {
      switch($service->getIdentifier()->match($input))
      {
        case xfIdentifier::MATCH_YES:
          return $service;
        case xfIdentifier::MATCH_IGNORED;
          $ignored = $service;
          break;
        default:
      }
    }
    
    if ($ignored == null)
    {
      throw new xfServiceNotFoundException('Unable to find service from input');
    }
    else
    {
      throw new xfServiceIgnoredException($ignored);
    }
  }
}
