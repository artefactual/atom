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

namespace Atom\Framework\Routing;

final class QubitResourceClassifier implements ResourceClassifier
{
    private const TYPES = [
        'QubitRepository' => 'repository',
        'QubitRelation' => 'relation',
        'QubitDonor' => 'donor',
        'QubitRights' => 'rights',
        'QubitRightsHolder' => 'rights_holder',
        'QubitUser' => 'user',
        'QubitActor' => 'actor',
        'QubitFunctionObject' => 'function',
        'QubitDigitalObject' => 'digital_object',
        'QubitInformationObject' => 'information_object',
        'QubitAccession' => 'accession',
        'QubitDeaccession' => 'deaccession',
        'QubitTerm' => 'term',
        'QubitTaxonomy' => 'taxonomy',
        'QubitStaticPage' => 'static_page',
        'QubitPhysicalObject' => 'physical_object',
        'QubitEvent' => 'event',
    ];

    public function classify(object $resource): ?string
    {
        foreach (self::TYPES as $class => $type) {
            if (is_a($resource, $class)) {
                return $type;
            }
        }

        return null;
    }
}
