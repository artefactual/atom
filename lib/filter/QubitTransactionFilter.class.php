<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class QubitTransactionFilter extends sfFilter
{
  protected static
    $connection = null,
    $retry = 0,
    $retryLimit = 3;

  public static function getConnection()
  {
    if (!isset(self::$connection))
    {
      self::$connection = Propel::getConnection();
    }

    return self::$connection;
  }

  protected function retry()
  {
    // If we've hit the retry limit, abort and return false
    if (self::$retry++ > self::$retryLimit)
    {
      return false;
    }

    // Log a warning
    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->context->getLogger()->warning(
        sprintf('Encountered a SQL transaction deadlock, retry %d of %d',
          self::$retry, self::$retryLimit
        )
      );
    }

    // Get the current action instance
    $actionInstance = $this->context
      ->getController()
      ->getActionStack()
      ->getLastEntry()
      ->getActionInstance();

    // Create a new filter chain and reload config
    $filterChain = new sfFilterChain();
    $filterChain->loadConfiguration($actionInstance);

    $this->context->getEventDispatcher()->notify(
      new sfEvent($this, 'transaction.retry')
    );

    // Execute whole filter chain again
    $filterChain->execute();

    return true;
  }

  public function execute($filterChain)
  {
    try
    {
      $conn = self::getConnection();
      $conn->beginTransaction();
    }
    catch (PropelException $e)
    {
    }

    try
    {
      $filterChain->execute();

      if (isset($conn))
      {
        $conn->commit();
      }
    }
    catch (PDOException $e)
    {
      // Rollback the transaction
      $conn->rollBack();

      // If there was a transaction deadlock error (MySQL error code 1213)
      if (isset($conn) && $e->errorInfo[1] == 1213)
      {
        // Retry the current action (returns false when out of retries)
        if (!$this->retry())
        {
          // If we've hit the retry limit, re-throw the exception
          throw $e;
        }
      }
      else
      {
        // Re-throw any other PDOExceptions
        throw $e;
      }
    }
    catch (Exception $e)
    {
      if (isset($conn))
      {
        // Whitelist of exceptions which commit instead of rollback the
        // transaction
        if ($e instanceof sfStopException)
        {
          $conn->commit();
        }
        else
        {
          $conn->rollBack();
        }
      }

      throw $e;
    }
  }
}
