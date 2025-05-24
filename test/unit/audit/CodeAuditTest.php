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

require_once dirname(__FILE__).'/../../bootstrap/unit.php';

global $preambleExceptions;
$preambleExceptions = '/'.implode('|', [
    '\.yml$',
    preg_quote(SF_ROOT_DIR, '/').'\/apps\/qubit\/config\/config\.php$',
    preg_quote(SF_ROOT_DIR, '/').'\/apps\/qubit\/config\/tidy\.conf$',
    preg_quote(SF_ROOT_DIR, '/').'\/apps\/qubit\/i18n\/[^\/]+\/messages.xml$',
    preg_quote(SF_ROOT_DIR, '/').'\/apps\/qubit\/modules\/[^\/]+\/templates\/[^\/.]+\.php$',
    preg_quote(SF_ROOT_DIR, '/').'\/apps\/qubit\/templates\/[^\/.]+\.php$',
    preg_quote(SF_ROOT_DIR, '/').'\/batch\/[^\/.]+\.php$',
    preg_quote(SF_ROOT_DIR, '/').'\/config\/[^\/.]+\.[^\/]+$',
    preg_quote(SF_ROOT_DIR, '/').'\/COPYRIGHT$',
    preg_quote(SF_ROOT_DIR, '/').'\/data\/',
    preg_quote(SF_ROOT_DIR, '/').'\/lib\/drupal\/',
    preg_quote(SF_ROOT_DIR, '/').'\/lib\/GoogleMapAPI\/',
    preg_quote(SF_ROOT_DIR, '/').'\/lib\/model\/map\/[^\/.]+\.php$',
    preg_quote(SF_ROOT_DIR, '/').'\/lib\/model\/om\/[^\/.]+\.php$',
    preg_quote(SF_ROOT_DIR, '/').'\/lib\/xhtml-to-xslfo.xsl$',
    preg_quote(SF_ROOT_DIR, '/').'\/LICENSE$',
    preg_quote(SF_ROOT_DIR, '/').'\/plugins\/sfTranslatePlugin\/',
    preg_quote(SF_ROOT_DIR, '/').'\/plugins\/sfHistoryPlugin\/',
    preg_quote(SF_ROOT_DIR, '/').'\/README$',
    preg_quote(SF_ROOT_DIR, '/').'\/symfony$',
    preg_quote(SF_ROOT_DIR, '/').'\/test\/bootstrap\/[^\/.]+\.php$',
    preg_quote(SF_ROOT_DIR, '/').'\/test\/functional\/qubit\/[^\/.]+\.php$',
    preg_quote(SF_ROOT_DIR, '/').'\/web\/',
]).'/';

// Use this file's preamble
global $preamble;
$preamble = preg_replace('/\*\/.*$/s', '*/', file_get_contents(__FILE__));

_readDir(SF_ROOT_DIR, $filePaths);

global $t;
$t = new lime_test(2 * count($filePaths), new lime_output_color());

foreach ($filePaths as $filePath) {
    checkPreamble($filePath);
}

$snifferExceptions = [
    // '\.yml$',
    // preg_quote(SF_ROOT_DIR, '/').'\/apps\/qubit\/config\/config\.php$',
    // preg_quote(SF_ROOT_DIR, '/').'\/apps\/qubit\/config\/tidy\.conf$',
    // preg_quote(SF_ROOT_DIR, '/').'\/apps\/qubit\/lib\/helper\/mySubmitTagHelper\.php$',
    // preg_quote(SF_ROOT_DIR, '/').'\/apps\/qubit\/modules\/[^\/]+\/templates\/[^\/.]+\.php$',
    // preg_quote(SF_ROOT_DIR, '/').'\/apps\/qubit\/templates\/[^\/.]+\.php$',
    preg_quote(SF_ROOT_DIR, '/').'\/batch\/[^\/.]+\.php$',
    preg_quote(SF_ROOT_DIR, '/').'\/config\/[^\/.]+\.[^\/]+$',
    // preg_quote(SF_ROOT_DIR, '/').'\/COPYRIGHT$',
    // preg_quote(SF_ROOT_DIR, '/').'\/data',
    preg_quote(SF_ROOT_DIR, '/').'\/lib\/GoogleMapAPI',
    preg_quote(SF_ROOT_DIR, '/').'\/lib\/model\/map\/[^\/.]+\.php$',
    preg_quote(SF_ROOT_DIR, '/').'\/lib\/model\/om\/[^\/.]+\.php$',
    preg_quote(SF_ROOT_DIR, '/').'\/lib\/PHP',
    // preg_quote(SF_ROOT_DIR, '/').'\/LICENSE$',
    // preg_quote(SF_ROOT_DIR, '/').'\/README$',
    preg_quote(SF_ROOT_DIR, '/').'\/symfony$',
    // preg_quote(SF_ROOT_DIR, '/').'\/test\/archival_description\/[^\/.]+\.php$',
    preg_quote(SF_ROOT_DIR, '/').'\/test\/bootstrap\/[^\/.]+\.php$',
    // preg_quote(SF_ROOT_DIR, '/').'\/test\/functional\/qubit\/[^\/.]+\.php$',
    // preg_quote(SF_ROOT_DIR, '/').'\/web')).'/';
];

function _readDir($dirPath, &$filePaths)
{
    // The rest of the file contains one record for each directory entry.  Each
    // record contains a number of ordered fields as described below.  The fields
    // are terminated by a line feed (0x0a) character.  Empty fields are
    // represented by just the terminator.  Empty fields that are only followed by
    // empty fields may be omitted from the record.  Records are terminated by a
    // form feed (0x0c) and a cosmetic line feed (0x0a).
    //
    // By matching records which follow a form feed and a line feed (\f\n) we
    // ignore the first record, which is for this directory.
    //
    // (?:[^\f\n]*\n){20} matches the 20 don't care fields between the name field
    // and the deleted field.  It is enclosed in (?:)? because empty fields that
    // are only followed by empty fields may be omitted from the record.
    //
    // @see libsvn_wc/README
    //
    // $match[1] is the name field
    // $match[2] is the kind field
    // $match[3] is the deleted field, if present
    preg_match_all('/\f\n([^\f\n]*)\n([^\f\n]*)\n(?:(?:[^\f\n]*){20}([^\f\n]*)\n)?/', file_get_contents("{$dirPath}/.svn/entries"), $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
        switch ($match[2]) {
            case 'dir':
                _readDir($dirPath.'/'.$match[1], $filePaths);

                break;

            case 'file':
                $filePaths[] = $dirPath.'/'.$match[1];

                break;

            default:
                // @todo
                echo "ERROR\n";
        }
    }
}

function checkPreamble($filePath)
{
    global $t;

    global $preambleExceptions;
    if (preg_match($preambleExceptions, $filePath)) {
        $t->skip("{$filePath} preamble skipped");

        return;
    }

    $fileContents = file_get_contents($filePath);

    global $preamble;
    $t->like($fileContents, '/^'.preg_quote($preamble, '/').'/', $filePath.' starts with preamble');
}
