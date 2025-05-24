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

use AccessToMemory\Path;

class qtPackageExtractorBase
{
    public function __construct(array $options = [])
    {
        // Check arguments, maybe better as an array property
        if (0 < count(array_diff(['format', 'resource'], array_keys($options)))) {
            throw new Exception('Missing arguments.');
        }

        // Package format (see qtSwordPluginConfiguration::packaging)
        $this->format = $options['format'];

        // Database object
        $this->resource = $options['resource'];

        // Filename
        if (isset($options['filename'])) {
            $this->filename = $options['filename'];
        }

        // Suggested name
        if (isset($options['suggested_name'])) {
            $this->suggestedName = $options['suggested_name'];
        }

        // Package physical location (file://, http://, ...)
        if (isset($options['location'])) {
            $this->location = $options['location'];
        }

        // Package container format (zip, rar, directory...)
        if (isset($options['type'])) {
            $this->type = $options['type'];
        }

        if (isset($options['checksum_md5'])) {
            $this->checksumMd5 = $options['checksum_md5'];
        }

        if (isset($options['logger'])) {
            $this->logger = $options['logger'];
        }
    }

    /**
     * Delete package files after processing or on failure.
     */
    public function __destruct()
    {
        if (empty($this->filename)) {
            return; // No files to delete
        }

        $this->info(
            sprintf('Processing complete, deleting "%s"', $this->filename)
        );

        $path = new Path($this->filename);
        $path->delete(true);
    }

    public function run()
    {
        $this->load();

        $this->process();
    }

    /**
     * Log an info level message.
     *
     * @param string $msg log message
     */
    protected function info($msg)
    {
        if (isset($this->logger)) {
            $this->logger->info($msg);
        }
    }

    protected function load()
    {
        // Download the package if it was sent by reference
        if (isset($this->location)) {
            $this->grab();
        }
        // Or send within the deposit HTTP request
        else {
            // MD5 checksum expected
            if (isset($this->checksumMd5) && md5(file_get_contents($this->filename)) != $this->checksumMd5) {
                throw new qtPackageExtractorChecksumException();
            }
        }

        // At this point, $this->filename should point to a local directory or a file
        // If it is a file and a container (tar, zip, etc...), let's extract it
        // $this->filename will point to the directory generated from the container
        if (!is_dir($this->filename)) {
            switch ($this->type) {
                case 'application/xml':
                case 'text/xml':
                    break;

                case 'application/zip':
                    $directory = $this->filename.'_dir';
                    $command = vsprintf('unzip -n -d %s %s', [$directory, $this->filename]);
                    exec($command, $output, $return);
                    if (2 > $return) {
                        $this->filename = $directory;
                    } else {
                        throw new Exception('Zip container could not be extracted.');
                    }

                    break;

                default:
                    throw new Exception('Content type not supported.');
            }
        }
    }

    // This method will be frequently extended from the derived class
    protected function process() {}

    protected function grab()
    {
        if (1 == preg_match('/^(.*):\/\/.*/', $this->location, $matches)) {
            if (2 > count($matches)) {
                throw new Exception('Location string format could not be recognized.');
            }

            switch ($matches[1]) {
                // The file is available in the local filesystem where this code is being executed
                // Absolute paths are not accepted, this loader is restricted to a given directory
                case 'file':
                    $pathinfo = pathinfo($this->location);
                    $name = $pathinfo['basename']; // It could be a file or a directory

                    $this->filename = sfConfig::get('app_sword_deposit_dir', '/tmp').DIRECTORY_SEPARATOR.$name;

                    break;

                // Other locations that we could support in the future
                case 'http':
                case 'https':
                case 'ftp':
                default:
                    throw new Exception("The {$matches[1]} protocol is not supported yet.");
            }

            // Ultimate check to make sure the file/directory exists
            if (!is_readable($this->filename)) {
                throw new Exception('File/directory does not exist or is not readable: '.$this->filename.'.');
            }
        } else {
            throw new Exception('The localization protocol could not be recognized.');
        }
    }

    protected function getUUID($subject)
    {
        preg_match_all('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/', $subject, $matches);

        if (isset($matches[0]) && is_array($matches[0])) {
            return end($matches[0]);
        }
    }
}
