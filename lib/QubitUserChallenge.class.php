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

class QubitUserChallenge
{
    protected $testHeadless;
    protected $cookieNameHeadless;
    protected $context;

    public function __construct(bool $testHeadless, string $cookieNameHeadless)
    {
        $this->testHeadless = $testHeadless;
        $this->cookieNameHeadless = $cookieNameHeadless;
        $this->context = sfContext::getInstance();
    }

    /**
     * Initiate js challenge.
     */
    public function challenge()
    {
        return $this->context->getController()->redirect(['module' => 'admin', 'action' => 'challenge']);
    }

    /**
     * Check to see if the browser reports if it is headless or not. Returns
     * true if is headless, otherwise false.
     *
     * @param mixed $key
     */
    public function checkHeadless($key)
    {
        // Check if test is activated.
        if (empty($this->testHeadless)) {
            return true;
        }

        // Parse cookie contents.
        if (isset($_COOKIE[$this->cookieNameHeadless])) {
            $headless = explode(':', base64_decode($_COOKIE[$this->cookieNameHeadless]));
            if (isset($headless[0], $headless[1])
                && $headless[0] === $key
                && 'false' === $headless[1]) {
                return true;
            }
        }

        return false;
    }
}
