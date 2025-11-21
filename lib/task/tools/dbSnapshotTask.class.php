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
 * Create a gzipped MySQL database snapshot using mysqldump.
 *
 * Usage:
 *   php symfony tools:db-snapshot --label="pre-import myfile.csv"
 *
 * Options can be overridden via CLI flags or app.yml:
 *   app_db_snapshot:
 *     dir:          "%SF_DATA_DIR%/db-snapshots"
 *     mysqldump_path: "mysqldump"
 *     gzip:         true
 *
 * This task reads database connection parameters from the configured
 * Propel connection and writes a timestamped dump file into the snapshot
 * directory. Credentials are supplied to mysqldump via a temporary
 * defaults-extra-file to avoid exposing passwords in process lists.
 */
class dbSnapshotTask extends sfBaseTask
{
    public function configure()
    {
        $this->namespace = 'tools';
        $this->name = 'db-snapshot';
        $this->briefDescription = 'Create a gzipped MySQL database snapshot using mysqldump';

        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),

            new sfCommandOption('label', null, sfCommandOption::PARAMETER_OPTIONAL, 'Optional label to add to the filename'),
            new sfCommandOption('output-dir', null, sfCommandOption::PARAMETER_OPTIONAL, 'Output directory for snapshots (defaults to app_db_snapshot.dir)'),
            new sfCommandOption('mysqldump-path', null, sfCommandOption::PARAMETER_OPTIONAL, 'Path to mysqldump binary (defaults to app_db_snapshot.mysqldump_path or "mysqldump")'),
            new sfCommandOption('no-gzip', null, sfCommandOption::PARAMETER_NONE, 'Disable gzip compression'),
            new sfCommandOption('dry-run', null, sfCommandOption::PARAMETER_NONE, 'Print the command that would be executed and exit'),
        ]);

        $this->detailedDescription = <<<'EOF'
Creates a gzipped MySQL snapshot using mysqldump. Example:

  php symfony tools:db-snapshot --label="pre-import records.csv"

Options can be configured in app.yml under app_db_snapshot or overridden via flags.
EOF;
    }

    public function execute($arguments = [], $options = [])
    {
        // Bootstrap DB config
        $databaseManager = new sfDatabaseManager($this->configuration);
        /** @var sfPropelDatabase $database */
        $database = $databaseManager->getDatabase($options['connection']);

        $dsn = $database->getParameter('dsn');
        $username = $database->getParameter('username');
        $password = $database->getParameter('password');

        $params = $this->parseDsn($dsn);
        if (empty($params['dbname'])) {
            throw new sfException('Unable to parse dbname from DSN.');
        }

        $host = isset($params['host']) ? $params['host'] : 'localhost';
        $port = isset($params['port']) ? $params['port'] : null;
        $dbname = $params['dbname'];

        // Resolve config defaults
        $defaultDir = sfConfig::get('app_db_snapshot_dir', sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'db-snapshots');
        $outDir = $options['output-dir'] ?: $defaultDir;
        $mysqldumpPath = $options['mysqldump-path'] ?: sfConfig::get('app_db_snapshot_mysqldump_path', 'mysqldump');
        $gzip = $options['no-gzip'] ? false : (bool) sfConfig::get('app_db_snapshot_gzip', true);
        // Sanitize label aggressively to avoid path traversal or shell issues
        $label = null;
        if (!empty($options['label'])) {
            $label = preg_replace('/[^A-Za-z0-9._-]/', '-', trim((string) $options['label']));
            $label = trim($label, '-._');
            if (strlen($label) > 64) {
                $label = substr($label, 0, 64);
            }
            if ($label === '') {
                $label = null;
            }
        }

        if (!is_dir($outDir)) {
            if (!@mkdir($outDir, 0775, true) && !is_dir($outDir)) {
                throw new sfException(sprintf('Output directory could not be created: %s', $outDir));
            }
        }

        $timestamp = date('Ymd-His');
        $base = sprintf('atom-%s%s.sql', $timestamp, $label ? '-'.$label : '');
        $dumpPath = rtrim($outDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$base; // plain SQL output
        $outPath = $gzip ? $dumpPath.'.gz' : $dumpPath; // final path

        // Create temporary defaults-extra-file for credentials
        $cnfPath = tempnam(sys_get_temp_dir(), 'mysqldump_');
        if (false === $cnfPath) {
            throw new sfException('Failed to create temporary credentials file.');
        }
        $cnf = "[client]\n";
        if ($username) {
            $cnf .= sprintf("user=\"%s\"\n", addcslashes($username, "\\\""));
        }
        if ($password) {
            $cnf .= sprintf("password=\"%s\"\n", addcslashes($password, "\\\""));
        }
        if ($host) {
            $cnf .= sprintf("host=\"%s\"\n", addcslashes($host, "\\\""));
        }
        if ($port) {
            $cnf .= sprintf("port=%d\n", (int) $port);
        }
        file_put_contents($cnfPath, $cnf);
        @chmod($cnfPath, 0600);

        try {
            // Build command
            $cmd = [];
            $cmd[] = escapeshellarg($mysqldumpPath);
            $cmd[] = '--defaults-extra-file='.escapeshellarg($cnfPath);
            $cmd[] = '--single-transaction';
            $cmd[] = '--triggers';
            $cmd[] = '--routines';
            $cmd[] = '--events';
            $cmd[] = '--skip-lock-tables';
            $cmd[] = '--set-gtid-purged=OFF'; // avoid GTID warnings on some setups
            $cmd[] = '--result-file='.escapeshellarg($dumpPath);
            $cmd[] = escapeshellarg($dbname);

            $pipeline = implode(' ', $cmd);

            $this->logSection('db-snapshot', 'Output: '.$outPath);
            $this->logSection('db-snapshot', 'Command: '.$this->redactCmd($pipeline, $password));

            if ($options['dry-run']) {
                $this->logSection('db-snapshot', 'Dry run: no changes made.');
                return;
            }

            exec($pipeline, $output, $exitCode);
            if (0 !== (int) $exitCode) {
                throw new sfException(sprintf('mysqldump failed with exit code %d', $exitCode));
            }

            // Optionally compress using PHP to avoid shell pipelines
            if ($gzip) {
                $gz = @gzopen($outPath, 'wb9');
                if (false === $gz) {
                    throw new sfException('Failed to open gzip output for writing.');
                }
                $in = @fopen($dumpPath, 'rb');
                if (false === $in) {
                    @gzclose($gz);
                    throw new sfException('Failed to open temporary dump for reading.');
                }
                while (!feof($in)) {
                    $buf = fread($in, 8192);
                    if ($buf === false) {
                        fclose($in);
                        gzclose($gz);
                        throw new sfException('Read error while compressing dump.');
                    }
                    if ($buf !== '') {
                        gzwrite($gz, $buf);
                    }
                }
                fclose($in);
                gzclose($gz);
                @unlink($dumpPath);
            }

            // Tighten permissions on the output file
            @chmod($outPath, 0600);

            $size = @filesize($outPath);
            $this->logSection('db-snapshot', sprintf('Snapshot created (%s bytes).', false !== $size ? $size : 'unknown'));
        } finally {
            @unlink($cnfPath);
        }
    }

    private function redactCmd($cmd, $password)
    {
        if (!$password) {
            return $cmd;
        }

        // Password is not on cmd line, but be cautious in case of logging
        return str_replace($password, '****', $cmd);
    }

    private function parseDsn($dsn)
    {
        $params = [
            'host' => 'localhost',
        ];

        if (!preg_match('/^(\w+):/', $dsn)) {
            return $params;
        }

        if (preg_match('/dbname=([^;]+)/', $dsn, $m)) {
            $params['dbname'] = $m[1];
        }
        if (preg_match('/host=([^;]+)/', $dsn, $m)) {
            $params['host'] = $m[1];
        }
        if (preg_match('/port=(\d+)/', $dsn, $m)) {
            $params['port'] = $m[1];
        }

        return $params;
    }
}
