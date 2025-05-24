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
 * Designed to be extended by arElasticSearchPlugin.
 */
abstract class QubitSearchEngine
{
    private $dispatcher;
    private $event;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dispatcher = sfContext::getInstance()->getEventDispatcher();

        if (sfContext::getInstance()->getController()->inCLI()) {
            $this->event = 'command.log';
        } else {
            $this->event = 'search.log';
        }
    }

    /**
     * Log a message.
     *
     * @param string $message Log message
     */
    public function log($message)
    {
        $this->dispatcher->notify(new sfEvent($this, $this->event, [$message]));
    }
}
