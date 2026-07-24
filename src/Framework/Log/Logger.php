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

#[\AllowDynamicProperties]
abstract class Logger
{
    public const EMERG = 0;
    public const ALERT = 1;
    public const CRIT = 2;
    public const ERR = 3;
    public const WARNING = 4;
    public const NOTICE = 5;
    public const INFO = 6;
    public const DEBUG = 7;

    protected $dispatcher;
    protected $options = [];
    protected $level = self::INFO;

    public function __construct(
        EventDispatcher $dispatcher,
        array $options = [],
    ) {
        $this->initialize($dispatcher, $options);
    }

    public function initialize(
        EventDispatcher $dispatcher,
        $options = [],
    ) {
        $this->dispatcher = $dispatcher;
        $this->options = $options;

        if (isset($options['level'])) {
            $this->setLogLevel($options['level']);
        }

        $dispatcher->connect(
            'application.log',
            [$this, 'listenToLogEvent'],
        );
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    public function getLogLevel()
    {
        return $this->level;
    }

    public function setLogLevel($level)
    {
        $this->level = is_int($level)
            ? $level
            : constant(self::class.'::'.strtoupper((string) $level));
    }

    public function log($message, $priority = self::INFO)
    {
        return $this->level < $priority
            ? false
            : $this->doLog($message, $priority);
    }

    public function emerg($message): void
    {
        $this->log($message, self::EMERG);
    }

    public function alert($message): void
    {
        $this->log($message, self::ALERT);
    }

    public function crit($message): void
    {
        $this->log($message, self::CRIT);
    }

    public function err($message): void
    {
        $this->log($message, self::ERR);
    }

    public function warning($message): void
    {
        $this->log($message, self::WARNING);
    }

    public function notice($message): void
    {
        $this->log($message, self::NOTICE);
    }

    public function info($message): void
    {
        $this->log($message, self::INFO);
    }

    public function debug($message): void
    {
        $this->log($message, self::DEBUG);
    }

    public function listenToLogEvent(Event $event): void
    {
        $priority = $event['priority'] ?? self::INFO;

        foreach ($event->getParameters() as $key => $message) {
            if ('priority' !== $key) {
                $this->log((string) $message, $priority);
            }
        }
    }

    public static function getPriorityName($priority): string
    {
        return [
            self::EMERG => 'emerg',
            self::ALERT => 'alert',
            self::CRIT => 'crit',
            self::ERR => 'err',
            self::WARNING => 'warning',
            self::NOTICE => 'notice',
            self::INFO => 'info',
            self::DEBUG => 'debug',
        ][$priority] ?? throw new \InvalidArgumentException(
            sprintf('Priority "%s" does not exist.', $priority),
        );
    }

    public function shutdown(): void {}

    abstract protected function doLog($message, $priority);
}
