<?php

class QubitFavorites extends BaseFavorites
{
  public function __toString()
  {
    $string = $this->name;
    if (!isset($string)) {
      $string = $this->getName(['sourceCulture' => true]);
    }

    return (string) $string;
  }

  public function insert($connection = null)
  {
    return parent::insert($connection);
  }

  /**
   * Overwrite BaseFavorites::delete() method to add cascading delete
   * logic.
   *
   * @param mixed $connection a database connection object
   */
  public function delete($connection = null)
  {
    parent::delete($connection);
  }
}
