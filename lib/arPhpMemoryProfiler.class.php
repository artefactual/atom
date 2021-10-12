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
 * Utilities for interacting with arnaud-lb/php-memory-profiler
 * https://github.com/arnaud-lb/php-memory-profiler.
 *
 * 1) install arnaud-lb/php-memory-profiler
 * 2) configure atom-worker.service to include "Environment=MEMPROF_PROFILE=native"
 * 3) OPTIONAL: set the basepath and name using env var:
 *    "Environment=MEMPROF_OUTPUT_BASENAME=/vagrant/atom_memprof_file.grind"
 * 4) run: sudo systemctl daemon-reload
 * 5) restart the atom-worker
 *
 * If MEMPROF_OUTPUT_BASENAME is not set, the grind files will be output
 * to the AtoM folder (atom_memprof_file.grind.<timestamp>)
 */
class arPhpMemoryProfiler
{
    public function createMemprofGrindFile(string $basename = 'atom_memprof_file.grind')
    {
        if (!self::getMemprofEnabled()) {
            return;
        }

        if (false !== $memprof_basename = getenv('MEMPROF_OUTPUT_BASENAME')) {
            $basename = $memprof_basename;
        }

        $filename = $basename.'.'.$this->getUniqueExtension();
        memprof_dump_callgrind(fopen($filename, 'w'));

        return $filename;
    }

    public function getMemprofProfile()
    {
        return getenv('MEMPROF_PROFILE');
    }

    public static function getMemprofEnabled()
    {
        if (
            function_exists('memprof_enabled')
            && memprof_enabled()
            && function_exists('memprof_dump_callgrind')
            && getenv('MEMPROF_PROFILE')
        ) {
            return true;
        }

        return false;
    }

    protected function getUniqueExtension()
    {
        return date('Ymd-His-').substr(microtime(false), 2, 3);
    }
}
