<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Service found, but ignored exception
 *
 * @package xfSearch
 * @subpackage Service
 * @author Carl Vondrick
 */
class xfServiceIgnoredException extends xfServiceException
{
  /**
   * The service ignored.
   *
   * @var xfService
   */
  private $service;

  /**
   * Constructor to set service.
   *
   * @param xfService $service
   */
  public function __construct(xfService $service)
  {
    $this->service = $service;
  }

  /**
   * Gets the ignored service.
   *
   * @returns xfService
   */
  public function getService()
  {
    return $this->service;
  }
}
