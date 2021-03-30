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

class QubitTimer
{
    public $fh;
    public $start;
    public $end;
    public $total = 0;

    public function __construct($logFile = null)
    {
        $this->start();

        if (null != $logFile) {
            $this->fh = fopen($logFile, 'w');
        }
    }

    public function __destruct()
    {
        if (isset($this->fh)) {
            fclose($this->fh);
        }
    }

    public function start()
    {
        $this->start = microtime(true);
        $this->end = null;

        return $this;
    }

    public function stop()
    {
        $this->end = microtime(true);

        return $this;
    }

    public function elapsed($rnd = 2)
    {
        if (empty($this->end)) {
            $this->add(true);
        }

        return round($this->total, $rnd);
    }

    public function add($continue = false)
    {
        $this->stop();

        $this->total += $this->end - $this->start;

        if ($continue) {
            $this->start();
        }

        return $this;
    }

    public function log($string)
    {
        if (!isset($this->fh)) {
            return;
        }

        fwrite($this->fh, $string.' ('.$this->elapsed()."s)\n");
    }
}
