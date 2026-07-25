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

class SearchGlobalReplaceAction extends InformationObjectBrowseAction
{
    public function execute($request)
    {
        $this->hasSearchCriteria = $this->hasSearchCriteria($request);
        if ($this->hasSearchCriteria) {
            $request->limit = (string) arElasticSearchPluginConfiguration::getMaxResultWindow();
        }

        parent::execute($request);

        $this->addFields();
        $this->bindForm($request);

        $this->title = $this->context->i18n->__('Global search/replace');
        $this->searchFields = $this->getSearchFields();

        if (!$this->hasSearchCriteria) {
            unset($this->pager);
        }

        if (!$request->isMethod('post')) {
            return;
        }

        if (!$this->hasSearchCriteria || !isset($this->pager) || 0 === $this->pager->getNbResults()) {
            $this->error = $this->context->i18n->__('Search for at least one description before replacing text.');

            return;
        }

        if (empty($request->pattern) || empty($request->replacement) || empty($request->column)) {
            $this->error = $this->context->i18n->__('Both source and replacement fields are required.');

            return;
        }

        if (isset($request->allowRegex) && false === @preg_match($this->getRegex($request), '')) {
            $this->error = $this->context->i18n->__('The regular expression is invalid.');

            return;
        }

        if (!isset($request->confirm)) {
            $this->title = $this->context->i18n->__(
                'Are you sure you want to replace "%1%" with "%2%" in %3%?',
                [
                    '%1%' => $request->pattern,
                    '%2%' => $request->replacement,
                    '%3%' => sfInflector::humanize(sfInflector::underscore($request->column)),
                ]
            );

            return;
        }

        if (count($this->pager->getResults()) < $this->pager->getNbResults()) {
            $this->error = $this->context->i18n->__(
                'The search matches more than %1% descriptions. Refine the search before replacing text.',
                ['%1%' => arElasticSearchPluginConfiguration::getMaxResultWindow()]
            );

            return;
        }

        foreach ($this->pager->getResults() as $hit) {
            $io = QubitInformationObject::getById($hit->getId());

            if (null === $io || !$io->__isset($request->column)) {
                continue;
            }

            if (isset($request->allowRegex)) {
                $replaced = preg_replace(
                    $this->getRegex($request),
                    $request->replacement,
                    $io->__get($request->column)
                );
            } elseif (isset($request->caseSensitive)) {
                $replaced = str_replace($request->pattern, $request->replacement, $io->__get($request->column));
            } else {
                $replaced = str_ireplace($request->pattern, $request->replacement, $io->__get($request->column));
            }

            $io->__set($request->column, $replaced);
            $io->save();
        }

        QubitSearch::getInstance()->optimize();

        $this->redirect(['module' => 'search', 'action' => 'globalReplace']);
    }

    private function addFields()
    {
        $map = new InformationObjectI18nTableMap();
        $choices = [];

        foreach ($map->getColumns() as $column) {
            if (!$column->isPrimaryKey() && !$column->isForeignKey()) {
                $columnName = $column->getPhpName();
                $choices[$columnName] = sfInflector::humanize(sfInflector::underscore($columnName));
            }
        }
        $choices['identifier'] = $this->context->i18n->__('Identifier');

        $this->form->setValidator('column', new sfValidatorChoice([
            'choices' => array_keys($choices),
            'required' => false,
        ]));
        $this->form->setWidget('column', new sfWidgetFormSelect(['choices' => $choices]));

        $this->form->setValidator('pattern', new sfValidatorString(['required' => false]));
        $this->form->setWidget('pattern', new sfWidgetFormInput());

        $this->form->setValidator('replacement', new sfValidatorString(['required' => false]));
        $this->form->setWidget('replacement', new sfWidgetFormInput());

        $this->form->setValidator('caseSensitive', new sfValidatorBoolean(['required' => false]));
        $this->form->setWidget('caseSensitive', new sfWidgetFormInputCheckbox());

        $this->form->setValidator('allowRegex', new sfValidatorBoolean(['required' => false]));
        $this->form->setWidget('allowRegex', new sfWidgetFormInputCheckbox());

        if ($this->request->isMethod('post') && !isset($this->request->confirm)) {
            $this->form->setValidator('confirm', new sfValidatorBoolean(['required' => false]));
            $this->form->setWidget('confirm', new sfWidgetFormInputHidden([], ['value' => true]));
        }
    }

    private function bindForm($request)
    {
        $params = array_filter(
            $request->getRequestParameters() + $request->getGetParameters(),
            fn ($value) => null !== $value && '' !== $value
        );

        $this->form->bind($params);
    }

    private function getRegex($request)
    {
        $regex = '/'.str_replace('/', '\\/', $request->pattern).'/';

        if (!isset($request->caseSensitive)) {
            $regex .= 'i';
        }

        return $regex;
    }

    private function getSearchFields()
    {
        return [
            '' => $this->context->i18n->__('Any field'),
            'title' => $this->context->i18n->__('Title'),
            'scopeAndContent' => $this->context->i18n->__('Scope and content'),
            'archivalHistory' => $this->context->i18n->__('Archival history'),
            'extentAndMedium' => $this->context->i18n->__('Extent and medium'),
            'identifier' => $this->context->i18n->__('Identifier'),
            'referenceCode' => $this->context->i18n->__('Reference code'),
        ];
    }

    private function hasSearchCriteria($request)
    {
        foreach ($request->getGetParameters() as $name => $value) {
            if (preg_match('/^(query|sq\d+)$/', $name) && '' !== trim((string) $value)) {
                return true;
            }
        }

        return false;
    }
}
