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
 * Convert custom link format to Markdown syntax in various i18n table fields.
 */
class i18nCustomLinkToMarkdownTask extends i18nTransformBaseTask
{
    /**
     * @see sfTask
     */
    protected function configure()
    {
        parent::configure();

        $this->namespace = 'i18n';
        $this->name = 'custom-link-to-markdown';
        $this->briefDescription = 'Convert custom link format to Markdown syntax in various i18n table fields';

        $this->detailedDescription = <<<'EOF'
Convert custom link format to Markdown syntax from inside information object, actor, note, repository, and rights i18n fields.
EOF;
    }

    /**
     * @see i18nProcessColumnsBaseTask
     *
     * @param mixed $row
     * @param mixed $tableName
     * @param mixed $columns
     */
    protected function processRow($row, $tableName, $columns)
    {
        // Determine what column values have custom links
        $columnValues = [];

        foreach ($columns as $column) {
            $regex = '~
                (?:
                    (?:&quot;|\")(.*?)(?:\&quot;|\")\:            # Double quote and colon
                )
                (
                    (?:(?:https?|ftp)://)|                        # protocol spec, or
                    (?:www\.)|                                    # www.*
                    (?:mailto:)                                   # mailto:*
                )
                (
                    [-\w@]+                                       # subdomain or domain
                    (?:\.[-\w@]+)*                                # remaining subdomains or domain
                    (?::\d+)?                                     # port
                    (?:/(?:(?:[\~\w\+%-]|(?:[,.;:][^\s$]))+)?)*   # path
                    (?:\?[\w\+\/%&=.;-]+)?                        # query string
                    (?:\#[\w\-/\?!=]*)?                           # trailing anchor
                )
                ~x';

            $transformedValue = preg_replace_callback($regex, function ($matches) {
                if (!empty($matches[1])) {
                    return "[{$matches[1]}](".('www.' == $matches[2] ? 'http://www.' : $matches[2]).trim($matches[3]).')';
                }

                return "[{$matches[2]}".trim($matches[3]).']('.('www.' == $matches[2] ? 'http://www.' : $matches[2]).trim($matches[3]).')';
            }, $row[$column]);

            // Save changed values
            if ($row[$column] != $transformedValue) {
                $columnValues[$column] = $transformedValue;
            }
        }

        // Update row
        $this->updateRow($tableName, $row['id'], $row['culture'], $columnValues);

        return count($columnValues);
    }
}
