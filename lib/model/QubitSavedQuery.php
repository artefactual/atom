<?php


/**
 * Skeleton subclass for representing a row from the 'drmc_query' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    lib.model
 */
class QubitSavedQuery extends BaseSavedQuery
{
  /**
   * Additional save functionality (e.g. update search index)
   *
   * @param mixed $connection a database connection object
   * @return QubitDrmcQuery self-reference
   */
  public function save($connection = null)
  {
    parent::save($connection);

    QubitSearch::getInstance()->update($this);

    return $this;
  }

  public function delete($connection = null)
  {
    QubitSearch::getInstance()->delete($this);

    parent::delete($connection);
  }
}
