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
class arUnogPreservicaPluginRestClient
{
    private $httpStatusCode;

    public function __construct($host, $username, $password)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;

        $this->token = $this->getToken();
    }

    public function getHttpStatus()
    {
        return $this->httpStatusCode;
    }

    public function getObjectDetails($uuid)
    {
        $url = $this->baseUrl().'/content/object-details';
        $url .= '?'.http_build_query(['id' => 'sdb:IO|'.$uuid]);

        $curlSession = curl_init($url);
        curl_setopt($curlSession, CURLOPT_HTTPHEADER, $this->accessHeaders('application/json'));
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

        $returnData = curl_exec($curlSession);
        $this->httpStatusCode = curl_getinfo($curlSession, CURLINFO_HTTP_CODE);

        curl_close($curlSession);

        if (200 == $this->httpStatusCode) {
            return json_decode($returnData);
        }
    }

    public function getObjectDetailsPropertyByName($objectData, $name)
    {
        foreach ($objectData->value->properties as $data) {
            if ($data->name == $name) {
                return $data->value;
            }
        }
    }

    public function downloadThumbnailToTempDir($cmisId, $filename, $size = 'large')
    {
        $url = $this->baseUrl().'/content/thumbnail';
        $url .= '?'.http_build_query(['id' => $cmisId, 'size' => $size]);

        $curlSession = curl_init($url);
        curl_setopt($curlSession, CURLOPT_HTTPHEADER, $this->accessHeaders('application/octet-stream'));
        curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

        $dir = $this->tempdir('preservicaThumb');
        $tempFile = $dir.DIRECTORY_SEPARATOR.$filename;
        file_put_contents($tempFile, curl_exec($curlSession));

        $this->httpStatusCode = curl_getinfo($curlSession, CURLINFO_HTTP_CODE);

        curl_close($curlSession);

        if (200 == $this->httpStatusCode) {
            return $tempFile;
        }
    }

    public function getFullText($uuid)
    {
        $queryTerm = [
            'q' => $uuid,
            'fields' => [
                ['name' => 'xip.full_text', 'values' => ['']],
            ],
        ];

        $url = 'https://'.$this->host.'/api/content/search';

        $params = [
            'q' => json_encode($queryTerm, JSON_PRETTY_PRINT),
            'metadata' => 'xip.full_text',
            'start' => 0,
            'max' => 1,
        ];

        $curlSession = curl_init($url);
        curl_setopt($curlSession, CURLOPT_HTTPHEADER, $this->accessHeaders('application/x-www-form-urlencoded'));
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

        $returnData = curl_exec($curlSession);
        $this->httpStatusCode = curl_getinfo($curlSession, CURLINFO_HTTP_CODE);

        curl_close($curlSession);

        if (200 == $this->httpStatusCode) {
            $data = json_decode($returnData);
            $metadata = $data->value->metadata;

            return (empty($metadata[0])) ? '' : $metadata[0][0]->value;
        }
    }

    public function streamAsset($id, $filename)
    {
        function streamFunction($ch, $str)
        {
            echo $str;

            return strlen($str);
        }

        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename='.$filename);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        $url = 'https://'.$this->host.'/api/content/download';
        $url .= '?'.http_build_query(['id' => $id]);

        // Proxy file from Preservica
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->accessHeaders('application/octet-stream'));
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, 'streamFunction');
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        $response = curl_exec($ch);
        curl_close($ch);
    }

    private function baseUrl()
    {
        return 'https://'.$this->host.'/api';
    }

    private function accessHeaders($type)
    {
        return [
            'Preservica-Access-Token: '.$this->token,
            'Content-Type: '.$type,
        ];
    }

    private function getToken()
    {
        $url = $this->baseUrl().'/accesstoken/login';
        $params = ['username' => $this->username, 'password' => $this->password];

        $curlSession = curl_init($url);
        curl_setopt($curlSession, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

        $returnData = curl_exec($curlSession);
        $this->httpStatusCode = curl_getinfo($curlSession, CURLINFO_HTTP_CODE);

        curl_close($curlSession);

        if (200 == $this->httpStatusCode) {
            $data = json_decode($returnData);

            return $data->token;
        }
    }

    private function tempdir($prefix = '')
    {
        do {
            $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$prefix.mt_rand(0, 9999999);
        } while (!mkdir($path));

        return $path;
    }
}
