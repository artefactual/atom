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
 * Restore i18n strings lost when XLIFF files were broken into plugin-specific
 * directories.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class I18nRemoveDuplicatesTask extends sfBaseTask
{
    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        $this->logSection('i18n', sprintf('Removing duplicate i18n sources for the "%s" application', $options['application']));

        // Loop through plugins
        $pluginNames = sfFinder::type('dir')->maxdepth(0)->relative()->not_name('.')->in(sfConfig::get('sf_plugins_dir'));
        foreach ($pluginNames as $pluginName) {
            $this->logSection('i18n', sprintf('Removing %s duplicates', $pluginName));

            foreach (sfFinder::type('files')->in(sfConfig::get('sf_plugins_dir').'/'.$pluginName.'/i18n') as $file) {
                self::deleteDuplicateSource($file);
            }
        }
    }

    public function deleteDuplicateSource($filename)
    {
        return (new \Atom\Framework\Translation\XliffFile())
            ->removeDuplicateSources($filename);
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addOptions([
            // http://trac.symfony-project.org/ticket/8352
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
        ]);

        $this->namespace = 'i18n';
        $this->name = 'remove-duplicates';
        $this->briefDescription = 'Delete duplicate source messages';

        $this->detailedDescription = <<<'EOF'
FIXME
EOF;
    }
}
