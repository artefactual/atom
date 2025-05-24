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
 * Write Sitemap XML (URL inclusion protocol).
 */
class sitemapTask extends sfBaseTask
{
    /**
     * Sitemap services for submission.
     */
    private static $urls = [
        'Google' => 'http://www.google.com/webmasters/sitemaps/ping?sitemap=%s',
        'Bing' => 'http://www.bing.com/webmaster/ping.aspx?siteMap=%s',
    ];

    public function execute($arguments = [], $options = [])
    {
        sfContext::createInstance($this->configuration);

        if (!$options['base-url']) {
            if (null !== $setting = QubitSetting::getByName('siteBaseUrl')) {
                $options['base-url'] = $setting->getValue();
            } else {
                $options['base-url'] = 'http://127.0.0.1';
            }
        }

        // Check if the given directory exists
        if (!is_dir($options['output-directory'])) {
            throw new sfException('The given directory cannot be found');
        }

        // Delete existing sitemap(s)
        $files = sfFinder::type('file')
            ->name('sitemap*.xml')
            ->name('sitemap*.xml.gz')
            ->maxdepth(0)
            ->in($options['output-directory']);
        if (count($files) > 0) {
            if (!$options['no-confirmation']) {
                $result = $this->askConfirmation(['Do you want to delete the previous sitemap(s)? (Y/n)'], 'QUESTION_LARGE', true);
                if (!$result) {
                    $this->log('Quitting');

                    return;
                }
            }
            natsort($files);
            foreach ($files as $file) {
                $this->log('Deleting '.$file);
                unlink($file);
            }
        }

        // Write XML
        $writer = new SitemapWriter($options['output-directory'], $options['base-url'], $options['indent'], !$options['no-compress']);
        $this->log('Indexing information objects');
        $writer->addSet(new SitemapInformationObjectSet());
        $this->log('Indexing actors');
        $writer->addSet(new SitemapActorSet());
        $this->log('Indexing static pages');
        $writer->addSet(new SitemapStaticPageSet());
        $writer->end();

        // Sitemap submission
        if ($options['ping']) {
            $location = $options['base-url'].'/sitemap.xml';

            $client = new sfWebBrowser();
            foreach (self::$urls as $sName => $sUrl) {
                $url = sprintf($sUrl, $location);
                $this->log(sprintf('[%s] Submitting - %s', $sName, $url));

                $client->get($url);
                $this->log(sprintf('[%s] Response code: %s', $sName, $client->getResponseCode()));
            }
        }

        $this->log('Done!');
    }

    protected function configure()
    {
        $outputDirectory = sfConfig::get('sf_root_dir');

        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),

            new sfCommandOption('output-directory', 'O', sfCommandOption::PARAMETER_OPTIONAL, 'Location of the sitemap file(s)', $outputDirectory),
            new sfCommandOption('base-url', null, sfCommandOption::PARAMETER_OPTIONAL, 'Base URL', null),
            new sfCommandOption('indent', null, sfCommandOption::PARAMETER_OPTIONAL, 'Indent XML', true),
            new sfCommandOption('no-compress', null, sfCommandOption::PARAMETER_NONE, 'Compress XML output with Gzip'),
            new sfCommandOption('no-confirmation', '-B', sfCommandOption::PARAMETER_NONE, 'Avoid prompting the user'),
            new sfCommandOption('ping', null, sfCommandOption::PARAMETER_NONE, 'Submit sitemap to Google and Bing'),
        ]);

        $this->namespace = 'tools';
        $this->name = 'sitemap';
        $this->briefDescription = 'Write a Sitemap XML file that lists the URLs of the site.';

        $this->detailedDescription = <<<'EOF'
Write a Sitemap XML file that lists the URLs of the current site.

By default, the sitemap is stored in the root directory. Its final location can
be defined using [--output-directory|INFO].

The URLs included in the sitemap will be based on [Site base URL|INFO], that
can be defined under the application settings in the web interface or using
[--base-url|INFO].

Optionally, you can submit the sitemap to Google and Bing with [--ping|INFO].
EOF;
    }
}
