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

namespace Atom\Framework\Bridge;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final readonly class LoggerAdapter
{
    public function __construct(
        private LoggerInterface $logger = new NullLogger(),
    ) {}

    public function emerg(string $message): void
    {
        $this->logger->emergency($message);
    }

    public function alert(string $message): void
    {
        $this->logger->alert($message);
    }

    public function crit(string $message): void
    {
        $this->logger->critical($message);
    }

    public function err(string $message): void
    {
        $this->logger->error($message);
    }

    public function warning(string $message): void
    {
        $this->logger->warning($message);
    }

    public function notice(string $message): void
    {
        $this->logger->notice($message);
    }

    public function info(string $message): void
    {
        $this->logger->info($message);
    }

    public function debug(string $message): void
    {
        $this->logger->debug($message);
    }
}
