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
 * Form decorator for <table class="list"> css definition.
 *
 * @package    AccesstoMemory
 * @subpackage forms
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitWidgetFormSchemaFormatterList extends sfWidgetFormSchemaFormatter
{
  protected
    $rowFormat = "<tr>\n <td><span title=\"%help%\">%label%</td>\n <td>%error%%field%%hidden_fields%</td>\n</tr>\n",
    $helpFormat = '%help%',
    $errorRowFormat = "<tr><td colspan=\"2\">\n%errors%</td></tr>\n",
    $errorListFormatInARow = " <ul class=\"validation_error\">\n%errors% </ul>\n",
    $errorRowFormatInARow = " <li>%error%</li>\n",
    $namedErrorRowFormatInARow = " <li>%name%: %error%</li>\n",
    $decoratorFormat = "<table>\n %content%</table>";
}

?>
