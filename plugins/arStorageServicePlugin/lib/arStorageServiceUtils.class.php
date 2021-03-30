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

class arStorageServiceUtils
{
    public const STORAGE_SERVICE_PACKAGE_PATH = 'file';

    /**
     * Use phpcurl to request a URL and pass the header and stream back to
     * the browser.
     *
     * @param mixed $url
     */
    public static function getFileFromStorageService($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 500);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $data) {
            echo $data;

            return strlen($data);
        });

        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $header) {
            header($header);

            return strlen($header);
        });

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Storage service redirects
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            sprintf(
                'Authorization: ApiKey %s:%s',
                (string) QubitSetting::getByName('storage_service_username'),
                (string) QubitSetting::getByName('storage_service_api_key')
            ),
            'User-Agent: DRMC',
        ]);

        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        curl_close($ch);

        return $status;
    }

    /**
     * Return true if plugin is enabled and feature is activated.
     */
    public static function getAipDownloadEnabled()
    {
        $configuration = ProjectConfiguration::getActive();

        if (
            $configuration->isPluginEnabled('arStorageServicePlugin')
            && null !== $setting = QubitSetting::getByName('download_aip_enabled')
        ) {
            return boolval($setting->getValue(['sourceCulture' => true]));
        }
    }

    public static function getStorageServiceException($status)
    {
        switch ($status) {
            case '400':
                return new QubitApiBadRequestException('Storage service bad request');

            case '404':
                return new QubitApi404Exception('Storage service resource not found');

            case '401':
                return new QubitApiNotAuthorizedException('Storage service resource not authorized');

            case '403':
                return new QubitApiForbiddenException('Storage service resource forbidden');

            default:
                return new Exception(sprintf('Storage service error %s', (string) $status));
        }
    }
}
