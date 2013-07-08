<?php

/*
 */

class sfIsaarPlugin implements ArrayAccess
{
  protected
    $resource,
    $maintenanceNote;

  public function __construct(QubitActor $resource)
  {
    $this->resource = $resource;
  }

  public function offsetExists($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__isset'), $args);
  }

  public function __get($name)
  {
    $args = func_get_args();

    $options = array();
    if (1 < count($args))
    {
      $options = $args[1];
    }

    switch ($name)
    {
      case '_maintenanceNote':

        if (!isset($this->maintenanceNote))
        {
          $criteria = new Criteria;
          $criteria->add(QubitNote::OBJECT_ID, $this->resource->id);
          $criteria->add(QubitNote::TYPE_ID, QubitTerm::MAINTENANCE_NOTE_ID);

          if (1 == count($query = QubitNote::get($criteria)))
          {
            $this->maintenanceNote = $query[0];
          }
          else
          {
            $this->maintenanceNote = new QubitNote;
            $this->maintenanceNote->typeId = QubitTerm::MAINTENANCE_NOTE_ID;

            $this->resource->notes[] = $this->maintenanceNote;
          }
        }

        return $this->maintenanceNote;

      case 'maintenanceNotes':

        return $this->_maintenanceNote->__get('content', $options);

      case 'sourceCulture':

        return $this->resource->sourceCulture;
    }
  }

  public function offsetGet($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__get'), $args);
  }

  public function __set($name, $value)
  {
    switch ($name)
    {
      case 'maintenanceNotes':
        $this->_maintenanceNote->content = $value;

        return $this;
    }
  }

  public function offsetSet($offset, $value)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__set'), $args);
  }

  public function offsetUnset($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__unset'), $args);
  }
}
