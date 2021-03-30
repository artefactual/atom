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
 * Digital Object view action.
 *
 * @author     Andy Koch <koch.andy@gmail.com>
 */
class DigitalObjectViewAction extends sfAction
{
    public function execute($request)
    {
        $pathinfo = pathinfo($request->getPathInfo());
        $pathinfo['dirname'] = str_replace("/{$request->module}/{$request->action}", '', $pathinfo['dirname']).'/';

        $this->resource = QubitDigitalObject::getByPathFile($pathinfo['dirname'], $pathinfo['basename']);

        // We are going to need this later
        $this->digitalObjectId = $this->resource->id;

        // Resource Found?
        if (null === $this->resource) {
            $this->forward404();
        }

        list($obj, $action) = $this->getObjAndAction();

        // If access is denied, forward user to a 404 "Not found" page
        if (!QubitAcl::check($obj, $action)) {
            $this->forward404();
        }

        if ($this->needsPopup($action)) {
            $this->resource = $this->resource->object;

            $this->accessToken = bin2hex(random_bytes(32)); // URL friendly
            $this->context->user->setAttribute("token-{$this->digitalObjectId}", $this->accessToken, 'symfony/user/sfUser/copyrightStatementTmpAccess');

            $this->response->addMeta('robots', 'noindex,nofollow');
            $this->setTemplate('viewCopyrightStatement');

            $this->copyrightStatement = sfConfig::get('app_digitalobject_copyright_statement');

            return sfView::SUCCESS;
        }

        $this->setResponseHeaders();

        return sfView::HEADER_ONLY;
    }

    protected function needsPopup($action)
    {
        // Only if the user is reading the master digital object, and the resource
        // has a PREMIS conditional copyright restriction
        if ('readMaster' != $action || !$this->resource->hasConditionalCopyright()) {
            return false;
        }

        // Show the pop-up if a valid access token was not submitted
        return false === $this->isAccessTokenValid();
    }

    protected function setResponseHeaders()
    {
        $this->response->setContentType($this->resource->mimeType);

        // Using X-Accel-Redirect (Nginx) unless ATOM_XSENDFILE is set
        if (false === filter_var($_SERVER['ATOM_XSENDFILE'], FILTER_VALIDATE_BOOLEAN)) {
            $urlPath = preg_replace('\/?[^\/]+\.php$', '', $_SERVER['SCRIPT_NAME']);
            $this->response->setHttpHeader('X-Accel-Redirect', $urlPath.'/private'.$this->resource->getFullPath());
        } else {
            $this->response->setHttpHeader('X-Sendfile', sprintf(
                '%s/%s',
                sfConfig::get('sf_root_dir'),
                $this->resource->getFullPath()
            ));
        }
    }

    private function getObjAndAction()
    {
        switch ($this->resource->usageId) {
            case QubitTerm::MASTER_ID:
                $action = 'readMaster';
                $obj = $this->resource->object;

                break;

            case QubitTerm::REFERENCE_ID:
            case QubitTerm::CHAPTERS_ID:
            case QubitTerm::SUBTITLES_ID:
                $action = 'readReference';
                $obj = $this->resource->parent->object;

                break;

            case QubitTerm::THUMBNAIL_ID:
                $action = 'readThumbnail';
                $obj = $this->resource->parent->object;

                break;

            default:
                throw new sfException("Invalid usageId given in digitalobject/view: {$this->resource->usageId}");
        }

        return [$obj, $action];
    }

    private function isAccessTokenValid()
    {
        $providedToken = $this->request->token;
        $internalToken = $this->context->user->getAttribute("token-{$this->digitalObjectId}", null, 'symfony/user/sfUser/copyrightStatementTmpAccess');

        if (empty($providedToken) || empty($internalToken)) {
            return false;
        }

        if ($providedToken !== $internalToken) {
            return false;
        }

        return true;
    }
}
