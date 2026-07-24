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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM). If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Atom\Framework\Filter;

final class PropelSettingsRepository implements SettingsRepository
{
    public function all(string $culture): array
    {
        $sql = 'SELECT
                setting.*,
                (CASE
                    WHEN current.value IS NOT NULL AND current.value <> ""
                    THEN current.value ELSE source.value
                END) AS value,
                source.value AS value_source
            FROM setting
            LEFT JOIN setting_i18n current
                ON setting.id = current.id AND current.culture = ?
            LEFT JOIN setting_i18n source
                ON setting.id = source.id
                    AND source.culture = setting.source_culture';
        $settings = [];
        $languages = [];

        foreach (\QubitPdo::fetchAll($sql, [$culture]) as $setting) {
            if ('i18n_languages' === $setting->scope) {
                $languages[] = $setting->value_source;

                continue;
            }

            $key = null !== $setting->scope
                ? sprintf('app_%s_%s', $setting->scope, $setting->name)
                : 'app_'.$setting->name;
            $settings[$key] = $setting->value;
            $settings[$key.'__source'] = $setting->value_source;
        }

        $settings['app_i18n_languages'] = $languages;

        return $settings;
    }
}
