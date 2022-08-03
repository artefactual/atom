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

class SearchErrorAction extends sfAction
{
    public function execute($request)
    {
        $context = sfContext::getInstance();
        $exception = $request->getParameter('exception');
        $exceptionName = get_class($exception);

        if ($exception instanceof Elastica\Exception\ResponseException) {
            $error = $exception->getResponse()->getError();
        } else {
            $error = $exception->getMessage();
        }

        $this->message = sprintf('Elasticsearch error: %s - %s', $exceptionName, $error);
        $this->logMessage($this->message, 'err');

        // $this->message is going to be shown in the template only in debug mode
        if (!$context->getConfiguration()->isDebug()) {
            unset($this->message);
        }
    }
}
