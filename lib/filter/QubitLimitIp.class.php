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

class QubitLimitIpFilter extends sfFilter
{
    public function execute($filterChain)
    {
        $this->context = $this->getContext();
        $this->request = $this->context->getRequest();

        $this->limit = explode(';', sfConfig::get('app_limit_admin_ip'));

        // Pass if:
        // - Debug mode is on
        // - Setting "limit_admin_ip" is not set
        // - The filter is forwarding to admin/secure (isFirstCall)
        // - Route is user/logout
        if (
            $this->context->getConfiguration()->isDebug()
            || !$this->limit
            || !$this->isFirstCall()
            || ('user' == $this->request->getParameter('module') && 'logout' == $this->request->getParameter('action'))
        ) {
            $filterChain->execute();

            return;
        }

        // Forward to admin/secure if not allowed (only applies if user is authenticated)
        if ($this->context->user->isAuthenticated() && !$this->isAllowed()) {
            $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

            throw new sfStopException();
        }

        $filterChain->execute();
    }

    protected function getRemoteAddress()
    {
        $pathInfo = $this->request->getPathInfoArray();

        return $pathInfo['REMOTE_ADDR'];
    }

    protected function isAllowed()
    {
        $address = $this->getRemoteAddress();
        $addressBinary = inet_pton($address);

        // Check if empty
        if (1 == count($this->limit) && empty($this->limit[0])) {
            return true;
        }

        foreach ($this->limit as $item) {
            // Ranges are supported, using a comma or a dash
            $limit = preg_split('/[,-]/', $item);
            $limitBinary = inet_pton(trim($limit[0]));

            // Single IP
            if (1 == count($limit) && $addressBinary == $limitBinary && strlen($addressBinary) == strlen($limitBinary)) {
                return true;
            }

            // Range
            if (2 == count($limit)) {
                $limit[0] = trim($limit[0]);
                $limit[1] = trim($limit[1]);
                $firstInRangeBinary = inet_pton($limit[0]);
                $lastInRangeBinary = inet_pton($limit[1]);

                if (
                    (strlen($addressBinary) == strlen($firstInRangeBinary))
                     && ($addressBinary >= $firstInRangeBinary && $addressBinary <= $lastInRangeBinary)
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
