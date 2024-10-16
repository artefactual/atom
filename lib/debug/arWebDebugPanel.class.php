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

class arWebDebugPanel extends sfWebDebugPanel
{
    public static function listenToLoadDebugWebPanelEvent(sfEvent $event)
    {
        $panelManager = $event->getSubject();
        $event->getSubject()->setPanel(
            'AtoM',
            new self($panelManager)
        );
    }

    public function getTitle()
    {
        $title = 'AtoM '.qubitConfiguration::VERSION;

        if (null !== $rev = $this->getCurrentGitRevision()) {
            $title .= ' <strong>(git:'.$rev.')</strong>';
        }

        return $title;
    }

    public function getPanelTitle()
    {
        return 'AtoM';
    }

    public function getPanelContent() {}

    protected function getCurrentGitRevision()
    {
        $gitDirectory = sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR;
        $headFile = $gitDirectory.'HEAD';
        // Use at sign to avoid unnecessary warning
        if (false !== $fd = @fopen($headFile, 'r')) {
            $line = fgets($fd);
            fclose($fd);
        } else {
            return;
        }

        $refParts = preg_split("/[\\s\t]+/", $line, -1, PREG_SPLIT_NO_EMPTY);
        if (2 == count($refParts)) {
            $ref = $gitDirectory.$refParts[1];
            if (false !== $fd = fopen($ref, 'r')) {
                $hash = fgets($fd);
                fclose($fd);

                $branch = substr($ref, strrpos($ref, '/') + 1);

                return $branch.'/'.substr($hash, 0, 16);
            }
        }
    }
}
