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

class QubitValidatorMimeType extends sfValidatorBase
{
    /**
     * @param array $options  An array of options
     * @param array $messages An array of error messages
     *
     * @see sfValidatorBase
     */
    protected function configure($options = [], $messages = [])
    {
        $this->addRequiredOption('mime_types');
        $this->addMessage('mime_types', 'Invalid file type (%value%: %mime_type%)');
    }

    /**
     * @see sfValidatorBase
     *
     * @param mixed $value
     */
    protected function doClean($value)
    {
        $mimeType = QubitDigitalObject::deriveMimeType($value['name']);
        $mimeTypes = $this->getOption('mime_types');

        if (!in_array($mimeType, $mimeTypes)) {
            throw new sfValidatorError($this, 'mime_types', ['value' => $value['name'], 'mime_type' => $mimeType]);
        }

        return $value;
    }

    /**
     * @see sfValidatorBase
     *
     * @param mixed $value
     */
    protected function isEmpty($value)
    {
        // Empty if the value is not an array
        // or if the value comes from PHP with an error of UPLOAD_ERR_NO_FILE.
        return !is_array($value) || (is_array($value) && isset($value['error']) && UPLOAD_ERR_NO_FILE === $value['error']);
    }
}
