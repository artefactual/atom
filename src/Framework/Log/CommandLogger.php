<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * AtoM is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 */

declare(strict_types=1);

namespace Atom\Framework\Log;

use Atom\Framework\Bridge\Event;
use Atom\Framework\Bridge\EventDispatcher;

final class CommandLogger extends ConsoleLogger
{
    public function initialize(
        EventDispatcher $dispatcher,
        $options = [],
    ) {
        $dispatcher->connect(
            'command.log',
            [$this, 'listenToLogEvent'],
        );
        parent::initialize($dispatcher, $options);
    }

    public function listenToLogEvent(Event $event): void
    {
        foreach ($event->getParameters() as $key => $message) {
            if ('priority' !== $key) {
                $this->log((string) $message);
            }
        }
    }
}
