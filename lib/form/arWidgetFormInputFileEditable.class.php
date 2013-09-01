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

class arWidgetFormInputFileEditable extends sfWidgetFormInputFile
{
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->setOption('type', 'file');
    $this->setOption('needs_multipart', true);

    $this->addRequiredOption('file_src');
    $this->addOption('is_image', false);
    $this->addOption('edit_mode', true);
    $this->addOption('with_delete', true);
    $this->addOption('delete_label', 'Remove the current file');
  }

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $input = parent::render($name, $value, $attributes, $errors);

    if (!$this->getOption('edit_mode'))
    {
      return $input;
    }

    if ($this->getOption('with_delete'))
    {
      $deleteName = ']' == substr($name, -1) ? substr($name, 0, -1).'_delete]' : $name.'_delete';

      $delete = $this->renderTag('input', array_merge(array('type' => 'checkbox', 'name' => $deleteName), $attributes));
      $deleteLabel = $this->translate($this->getOption('delete_label'));
      $deleteLabel = $this->renderContentTag('i', $deleteLabel);

      return $this->getFileAsTag($attributes).$delete.$deleteLabel.'<br \>'.$input;
    }
    else
    {
      return $this->getFileAsTag($attributes).$input;
    }
  }

  protected function getFileAsTag($attributes)
  {
    if ($this->getOption('is_image'))
    {
      return false !== $this->getOption('file_src') ? $this->renderTag('img', array_merge(array('src' => $this->getOption('file_src')), $attributes)) : '';
    }
    else
    {
      return $this->getOption('file_src');
    }
  }
}
