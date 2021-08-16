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

class arB5WidgetFormInputFileEditable extends sfWidgetFormInputFile
{
    public function render($name, $value = null, $attributes = [], $errors = [])
    {
        $input = parent::render($name, $value, $attributes, $errors);

        $deleteField = '';
        if ($this->getOption('with_delete')) {
            $name = ']' == substr($name, -1) ? substr($name, 0, -1).'_delete]' : $name.'_delete';
            $deleteInput = $this->renderTag(
                'input',
                [
                    'type' => 'checkbox',
                    'class' => 'form-check-input',
                    'name' => $name,
                ],
            );
            $deleteLabel = $this->renderContentTag(
                'label',
                $this->translate('Remove the current file'),
                [
                    'class' => 'form-check-label',
                    'for' => $name,
                ],
            );

            $deleteField = '<div class="form-check mb-3">'
                .$deleteInput
                .$deleteLabel
                .'</div>';
        }

        return $this->getFileAsTag($attributes).$deleteField.$input;
    }

    protected function configure($options = [], $attributes = [])
    {
        parent::configure($options, $attributes);

        $this->setOption('type', 'file');
        $this->setOption('needs_multipart', true);
        $this->addOption('with_delete', true);
        $this->addRequiredOption('file_src');
    }

    protected function getFileAsTag($attributes)
    {
        if (false !== $this->getOption('file_src')) {
            $img = $this->renderTag(
                'img',
                [
                    'class' => 'img-thumbnail',
                    'src' => $this->getOption('file_src'),
                ]
            );

            return '<div class="mb-3">'.$img.'</div>';
        }

        return '';
    }
}
