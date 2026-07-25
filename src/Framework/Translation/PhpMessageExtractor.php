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

namespace Atom\Framework\Translation;

final readonly class PhpMessageExtractor
{
    public function extract(string $contents): array
    {
        $messages = [];
        $state = 0;
        $heredoc = false;
        $buffer = '';

        foreach (token_get_all($contents) as $token) {
            if (is_string($token)) {
                if ('(' === $token && 1 === $state) {
                    $state = 2;
                } else {
                    $state = 0;
                }

                continue;
            }

            [$type, $text] = $token;

            switch ($type) {
                case \T_STRING:
                    if ($heredoc && 2 === $state) {
                        $buffer .= $text;
                    } else {
                        $state = in_array(
                            $text,
                            ['__', 'format_number_choice'],
                            true,
                        ) ? 1 : 0;
                    }

                    break;

                case \T_WHITESPACE:
                    break;

                case \T_START_HEREDOC:
                    $heredoc = true;

                    break;

                case \T_END_HEREDOC:
                    $heredoc = false;

                    if ('' !== $buffer) {
                        $messages[] = $buffer;
                        $buffer = '';
                    }

                    $state = 0;

                    break;

                case \T_CONSTANT_ENCAPSED_STRING:
                    if (2 === $state) {
                        $delimiter = $text[0];
                        $messages[] = str_replace(
                            '\\'.$delimiter,
                            $delimiter,
                            substr($text, 1, -1),
                        );
                    }

                    $state = 0;

                    break;

                default:
                    if ($heredoc && 2 === $state) {
                        $buffer .= $text;
                    } else {
                        $state = 0;
                    }
            }
        }

        return $messages;
    }
}
