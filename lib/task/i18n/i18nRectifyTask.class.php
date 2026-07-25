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
class i18nRectifyTask extends sfBaseTask
{
    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        $this->logSection('i18n', sprintf('Rectifying existing i18n strings for the "%s" application', $options['application']));

        $changed = (new \Atom\Framework\Translation\TranslationCatalogue(
            sfConfig::get('sf_root_dir'),
            $this->configuration->getApplication()
        ))->rectifyPlugins($arguments['culture']);
        $this->logSection('i18n', sprintf('Updated %d translations', $changed));
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument('culture', sfCommandArgument::REQUIRED, 'The target culture'),
        ]);

        $this->addOptions([
            // http://trac.symfony-project.org/ticket/8352
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', true),
        ]);

        $this->namespace = 'i18n';
        $this->name = 'rectify';
        $this->briefDescription = 'Copy i18n target messages from application source to plugin source. This prevents losing translated string in the fragmentation of application message source into multiple plugin message sources.';

        $this->detailedDescription = <<<'EOF'
FIXME
EOF;
    }
}
