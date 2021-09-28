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

class AtomNetGearmanWorker extends Net_Gearman_Worker
{
    protected $jobsCompleted = 0;
    protected $maxJobCount = 0;

    public function setMaxJobCount(int $maxJobCount)
    {
        if ($maxJobCount > 0) {
            $this->maxJobCount = $maxJobCount;
        }
    }

    public function getJobsCompleted()
    {
        return $this->jobsCompleted;
    }

    /**
     * Begin working.
     *
     * Overrides net_gearman's Worker.php beginWork method. Adds check
     * $this->maxJobCountReached() and exits work loop if true.
     *
     * @param callable $monitor Function to monitor work
     *
     * @see Net_Gearman_Connection::send(), Net_Gearman_Connection::connect()
     * @see Net_Gearman_Worker::doWork(), Net_Gearman_Worker::addAbility()
     */
    public function beginWork($monitor = null)
    {
        if (!is_callable($monitor)) {
            $monitor = [$this, 'stopWork'];
        }
        $write = null;
        $except = null;
        $working = true;
        $lastJob = time();
        $retryTime = 5;
        while ($working) {
            $sleep = true;
            $currentTime = time();
            foreach ($this->conn as $server => $socket) {
                $worked = false;

                try {
                    $worked = $this->doWork($socket);
                } catch (Net_Gearman_Exception $e) {
                    unset($this->conn[$server]);
                    $this->retryConn[$server] = $currentTime;
                }
                if ($worked) {
                    $lastJob = time();
                    $sleep = false;
                }
            }
            $idle = false;
            if ($sleep && count($this->conn)) {
                foreach ($this->conn as $socket) {
                    Net_Gearman_Connection::send($socket, 'pre_sleep');
                }
                $read = $this->conn;
                @socket_select($read, $write, $except, $this->socket_timeout);
                $idle = (0 == count($read));
            }
            $retryChange = false;
            foreach ($this->retryConn as $s => $lastTry) {
                if (($lastTry + $retryTime) < $currentTime) {
                    try {
                        $conn = Net_Gearman_Connection::connect($s);
                        $this->conn[$s] = $conn;
                        $retryChange = true;
                        unset($this->retryConn[$s]);
                        Net_Gearman_Connection::send($conn, 'set_client_id', ['client_id' => $this->id]);
                    } catch (Net_Gearman_Exception $e) {
                        $this->retryConn[$s] = $currentTime;
                    }
                }
            }
            if (0 == count($this->conn)) {
                // sleep to avoid wasted cpu cycles if no connections to block on using socket_select
                sleep(1);
            }
            if (true === $retryChange) {
                // broadcast all abilities to all servers
                foreach ($this->abilities as $ability => $timeout) {
                    $this->addAbility(
                        $ability, $timeout, $this->initParams[$ability]
                    );
                }
            }
            if (true == call_user_func($monitor, $idle, $lastJob)) {
                $working = false;
            }

            if ($this->maxJobCountReached()) {
                $working = false;
            }
        }
    }

    protected function maxJobCountReached()
    {
        if ($this->maxJobCount <= 0) {
            return false;
        }

        if ($this->jobsCompleted >= $this->maxJobCount) {
            $this->log(sprintf('Max job count reached: %u jobs completed.', $this->jobsCompleted), sfLogger::INFO);

            return true;
        }

        return false;
    }

    protected function incrementJobsCompleted()
    {
        ++$this->jobsCompleted;
    }

    protected function complete($handle, $job, array $result)
    {
        $this->incrementJobsCompleted();
        $this->log(sprintf('Jobs completed: %u', $this->jobsCompleted), sfLogger::INFO);

        parent::complete($handle, $job, $result);
    }

    protected function log(string $message)
    {
        printf("%s %s\n", date('Y-m-d H:i:s >'), $message);
    }
}
