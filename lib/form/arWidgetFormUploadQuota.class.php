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

class arWidgetFormUploadQuota extends sfWidgetFormInput
{
    public function render($name, $value = null, $attributes = [], $errors = [])
    {
        $uploadLimit = (int) sfConfig::get('app_upload_limit');

        if (0 === $uploadLimit) {
            return '<label>'.__('%1% upload is disabled', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]).'</label>';
        }
        if (-1 === $uploadLimit) {
            return '<label>'.__('Unlimited').'</label>';
        }

        $size = Qubit::getDirectorySize(sfConfig::get('sf_upload_dir'));
        if ($size < 0) {
            return '<label>'.__('The uploads directory has not been created yet.').'</label>';
        }

        return '<label>'
            .__(
                '%1% used of %2%',
                [
                    '%1%' => hr_filesize($size),
                    '%2%' => $uploadLimit.' GB',
                ]
            )
            .'</label>';
    }

    protected function configure($options = [], $attributes = [])
    {
        parent::configure($options, $attributes);
    }
}
