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

// TODO
// post-1.2 Check upload/repository limit
// post-1.2 PUT/DELETE verbs
// post-1.2 X-On-Behalf-Of (mediation)
// post-1.2 X-No-Op (dev feature: dry run)
// post-1.2 X-Verbose (dev feature: verbose output)

class qtSwordPluginDepositAction extends sfAction
{
    public function execute($request)
    {
        if ($request->isMethod('put') || $request->isMethod('delete')) {
            return $this->generateResponse(501, 'error/ErrorNotImplemented', ['summary' => $this->context->i18n->__('Not implemented')]);
        }

        if (!$request->isMethod('post')) {
            return $this->generateResponse(400, 'error/ErrorBadRequest', ['summary' => $this->context->i18n->__('Bad request')]);
        }

        if (!QubitAcl::check(QubitInformationObject::getRoot(), 'create')) {
            return $this->generateResponse(403, 'error/ErrorBadRequest', ['summary' => $this->context->i18n->__('Forbidden')]);
        }

        if (!isset($this->getRoute()->resource)) {
            return $this->generateResponse(404, 'error/ErrorBadRequest', ['summary' => $this->context->i18n->__('Not found')]);
        }

        $this->resource = $this->getRoute()->resource;
        $this->user = $this->context->user;
        $this->package = [];

        // Package format, check if supported
        $this->package['format'] = $request->getHttpHeader('X-Packaging');
        if (!in_array($this->package['format'], qtSwordPluginConfiguration::$packaging)) {
            return $this->generateResponse(415, 'error/ErrorContent', ['summary' => $this->context->i18n->__('The supplied format is not supported by this server')]);
        }

        // Package content is part of the request or sent by reference?
        if (null !== $request->getHttpHeader('Content-Location')) {
            $this->package['location'] = $request->getHttpHeader('Content-Location');
        } else {
            // Save the file temporary
            $this->package['filename'] = qtSwordPlugin::saveRequestContent();

            // Package content type, check if supported
            $this->package['type'] = $request->getContentType();
            if (!in_array($this->package['type'], qtSwordPluginConfiguration::$mediaRanges)) {
                return $this->generateResponse(415, 'error/ErrorContent', ['summary' => $this->context->i18n->__('The supplied content type is not supported by this server')]);
            }
        }

        // Check if a filename was suggested
        if (null !== $request->getHttpHeader('Content-Disposition')) {
            $this->package['suggested_name'] = substr($request->getHttpHeader('Content-Disposition'), 9);
        } else {
            // TODO see [RFC2183]
            $this->package['suggested_name'] = $filename;
        }

        // Check if a filename was suggested
        if (null !== $request->getHttpHeader('Content-MD5')) {
            $this->package['checksum_md5'] = $request->getHttpHeader('Content-MD5');
        }

        $this->informationObject = $this->resource;

        $data = $this->package + ['objectId' => $this->informationObject->id];

        QubitJob::runJob('qtSwordPluginWorker', $data);

        // Job accepted!
        return $this->generateResponse(
            202,
            'deposit',
            ['headers' => ['Location' => $this->context->routing->generate(null, [$this->informationObject, 'module' => 'informationobject'])]]
        );
    }

    protected function generateResponse($code, $template = null, array $options = [])
    {
        $this->response->setStatusCode($code);

        if (null !== $template) {
            $this->request->setRequestFormat('xml');

            $this->response->setHttpHeader('Content-Type', 'application/atom+xml; charset="utf-8"');

            if (isset($options['headers'])) {
                foreach ($options['headers'] as $key => $value) {
                    $this->response->setHttpHeader($key, $value);
                }
            }

            if (isset($options['summary'])) {
                $this->summary = $options['summary'];
            }

            $this->setTemplate($template);
        }

        return null;
    }
}
