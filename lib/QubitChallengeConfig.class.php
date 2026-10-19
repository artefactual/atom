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
 * Load challenge configuration before Symfony is initialized.
 */
class QubitChallengeConfig
{
    public static function load(string $configFile, string $cacheDir): ?array
    {
        $configFile = realpath($configFile) ?: $configFile;
        $source = @file_get_contents($configFile);
        if (false === $source) {
            return null;
        }

        // Hash the small source file so same-second, same-size edits are detected.
        $hash = hash('sha256', $source);
        $cacheFile = $cacheDir.'/appChallenge-'.hash('sha256', $configFile).'.php';
        if (is_readable($cacheFile)) {
            try {
                $cached = @include $cacheFile;
                if (is_array($cached) && ($cached['hash'] ?? null) === $hash
                    && isset($cached['config']) && is_array($cached['config'])) {
                    return $cached['config'];
                }
            } catch (\ParseError $e) {
                // Rebuild an invalid cache from the source configuration.
            }
        }

        require_once __DIR__.'/../vendor/symfony/lib/yaml/sfYaml.php';

        $config = sfYaml::load($source) ?? [];
        if (!is_array($config)) {
            throw new InvalidArgumentException('Challenge configuration must be a YAML mapping.');
        }

        self::writeCache($cacheDir, $cacheFile, ['hash' => $hash, 'config' => $config]);

        return $config;
    }

    protected static function writeCache(string $cacheDir, string $cacheFile, array $cached): void
    {
        if (!is_dir($cacheDir) && !@mkdir($cacheDir, 0700, true) && !is_dir($cacheDir)) {
            return;
        }
        if (!is_writable($cacheDir)) {
            return;
        }

        // tempnam creates a private file; the cache contains the challenge salt.
        $temporary = @tempnam($cacheDir, 'challenge-');
        if (false === $temporary) {
            return;
        }

        try {
            // tempnam can fall back to /tmp. Require the same directory so the
            // rename publishes the complete file atomically on one filesystem.
            if (realpath(dirname($temporary)) !== realpath($cacheDir)) {
                return;
            }

            $php = "<?php\nreturn ".var_export($cached, true).";\n";
            if (@file_put_contents($temporary, $php) !== strlen($php)) {
                return;
            }
            if (@rename($temporary, $cacheFile) && function_exists('opcache_invalidate')) {
                // Also refresh installations with opcache.validate_timestamps=0.
                @opcache_invalidate($cacheFile, true);
            }
        } finally {
            if (file_exists($temporary)) {
                @unlink($temporary);
            }
        }
    }
}
