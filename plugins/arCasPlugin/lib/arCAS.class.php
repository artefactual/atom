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
 *
 * This file is based heavily on the sfCASPlugin developed by D.Jeanmonod and
 * maintained by H.Lepesant, MIT License, https://github.com/jeanmonod/sfCASPlugin.
 */

class arCAS
{
    protected static $phpCASIsInitialized = false;

    /**
     * Initialize the phpCAS library.
     */
    public static function initializePhpCAS()
    {
        // Return if phpCAS is already initialized
        if (self::$phpCASIsInitialized) {
            return;
        }

        require_once sfConfig::get('sf_root_dir').'/vendor/composer/jasig/phpcas/CAS.php';

        if (true == sfConfig::get('sf_debug', false)) {
            $debugLogPath = sfConfig::get('sf_log_dir').'/phpcas.log';
            phpCAS::setDebug($debugLogPath);
            phpCAS::setVerbose(true);
        }

        $casVersion = sfConfig::get('app_cas_cas_version', '3.0');
        $validCasVersions = ['1.0', '2.0', '3.0'];
        $casVersion = in_array($casVersion, $validCasVersions) ? $casVersion : CAS_VERSION_3_0;

        phpCAS::client(
            $casVersion,
            sfConfig::get('app_cas_server_name'),
            sfConfig::get('app_cas_server_port'),
            sfConfig::get('app_cas_server_path'),
            false  // Let Symfony handle the session
        );

        // This setting prevents a redirection loop that is otherwise caused by the
        // interaction of phpCAS with Symfony's session management.
        phpCAS::setNoClearTicketsFromUrl();

        // Override default service URL if required
        $serviceUrl = sfConfig::get('app_cas_service_url', '');
        if (!empty($serviceUrl)) {
            phpCAS::setFixedServiceURL($serviceUrl);
        }

        // Validate the server SSL certificate according to configuration.
        $certPath = sfConfig::get('app_cas_server_cert', false);
        if (0 === !strpos($certPath, '/')) {
            $certPath = sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.$certPath;
        }
        if (file_exists($certPath)) {
            phpCAS::setCasServerCACert($certPath);
        } elseif (false === $certPath) {
            phpCAS::setNoCasServerValidation();
        } else {
            throw new Exception('Invalid SSL certificate settings. Please review the app_cas_server_cert parameter in plugin app.yml.');
        }

        self::$phpCASIsInitialized = true;
    }
}
