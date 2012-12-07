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
 * Simple representation of an asset
 *
 * @package    AccesstoMemory
 * @subpackage libraries
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitAsset
{
  protected
    $name,
    $contents,
    $checksum,
    $checksumAlgorithm,
    $path;

  public function __construct()
  {
    $args = func_get_args();

    // File path passed
    if (1 == func_num_args())
    {
      $path_parts = pathinfo($args[0]);

      $this->name = $path_parts['basename'];
      $this->path = $args[0];
    }
    // File name and contents passed
    else if (2 == func_num_args())
    {
      $this->name = $args[0];
      $this->contents = $args[1];
    }

    $this->generateChecksum('sha256');
  }

  public function setName($value)
  {
    $this->name = $value;

    return $this;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getPath()
  {
    return $this->path;
  }

  public function setContents($value)
  {
    $this->contents = $value;

    return $this;
  }

  public function getContents()
  {
    return $this->contents;
  }

  public function setChecksum($value, $options)
  {
    if (isset($options['algorithm']))
    {
      $this->setChecksumAlgorithm($options['algorithm']);
    }

    if (0 < strlen($value) && !isset($this->checksumAlgorithm))
    {
      throw new Exception('You cannot set a checksum without specifiying an algorithm.');
    }

    $this->checksum = $value;

    return $this;
  }

  public function setChecksumAlgorithm($value)
  {
    $this->checksumAlgorithm = $value;

    return $this;
  }

  public function getChecksum()
  {
    return $this->checksum;
  }

  public function getChecksumAlgorithm()
  {
    return $this->checksumAlgorithm;
  }

  public function generateChecksum($algorithm)
  {
    if (!in_array($algorithm, hash_algos()))
    {
      throw new Exception('Invalid checksum algorithm');
    }

    $this->checksumAlgorithm = $algorithm;

    if (isset($this->contents))
    {
      $this->checksum = hash($algorithm, $this->contents);
    }
    else if (isset($this->path))
    {
      $this->checksum = hash_file($algorithm, $this->path);
    }

    return $this->checksum;
  }
}
