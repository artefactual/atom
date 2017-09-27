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

/**
 * This class is used to provide methods that supplement the core Qubit
 * information object with behaviour or presentation features that are specific
 * to the Brazilian Archival Standard Desciption - NOBRADE
 *
 * @package    AccesstoMemory
 * @author     Peter Van Garderen <peter@artefactual.com>
 */

class sfNobradePlugin implements ArrayAccess
{
  protected
    $resource;

  public function __construct($resource)
  {
    $this->resource = $resource;
  }

  public function __toString()
  {
    $string = array();

    $levelOfDescriptionAndIdentifier = array();

    if (isset($this->resource->levelOfDescription))
    {
      $levelOfDescriptionAndIdentifier[] = $this->resource->levelOfDescription->__toString();
    }

    if (isset($this->resource->identifier))
    {
      $levelOfDescriptionAndIdentifier[] = $this->resource->identifier;
    }

    if (0 < count($levelOfDescriptionAndIdentifier))
    {
      $string[] = implode($levelOfDescriptionAndIdentifier, ' ');
    }

    $titleAndPublicationStatus = array();

    if (0 < strlen($title = $this->resource->__toString()))
    {
      $titleAndPublicationStatus[] = $title;
    }

    $publicationStatus = $this->resource->getPublicationStatus();
    if (isset($publicationStatus) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $publicationStatus->statusId)
    {
      $titleAndPublicationStatus[] = "({$publicationStatus->status->__toString()})";
    }

    if (0 < count($titleAndPublicationStatus))
    {
      $string[] = implode($titleAndPublicationStatus, ' ');
    }

    return implode(' - ', $string);
  }

  public function __get($name)
  {
    switch ($name)
    {
      case 'languageNotes':

        return $this->resource->getNotesByType(array('noteTypeId' => QubitTerm::LANGUAGE_NOTE_ID))->offsetGet(0);

      case 'referenceCode':

        return $this->resource->referenceCode;

      case 'sourceCulture':

        return $this->resource->sourceCulture;
    }
  }

  public function __set($name, $value)
  {
    switch ($name)
    {
      case 'languageNotes':

        $note = $this->resource->getNotesByType(array('noteTypeId' => QubitTerm::LANGUAGE_NOTE_ID))->offsetGet(0);
        $missingNote = count($note) === 0;

        if (0 == strlen($value))
        {
          // Delete note if it's available
          if (!$missingNote)
          {
            $note->delete();
          }

          break;
        }

        if ($missingNote)
        {
          $note = new QubitNote;
          $note->typeId = QubitTerm::LANGUAGE_NOTE_ID;
          $note->userId = sfContext::getInstance()->user->getAttribute('user_id');

          $this->resource->notes[] = $note;
        }

        $note->content = $value;

        return $this;
    }
  }

  public function offsetExists($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__isset'), $args);
  }

  public function offsetGet($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__get'), $args);
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

  public static function eventTypes()
  {
    return array(QubitTerm::getById(QubitTerm::CREATION_ID),
      QubitTerm::getById(QubitTerm::ACCUMULATION_ID),
      QubitTerm::getById(QubitTerm::NOBRADE_DATE_SUBJECT_ID),
      QubitTerm::getById(QubitTerm::NOBRADE_INCLUSIVE_DATE_ID));
  }
}
