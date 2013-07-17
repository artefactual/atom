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

class SearchGlobalReplaceAction extends SearchAdvancedAction
{
  public function execute($request)
  {
    parent::execute($request);

    $this->addFields();

    $this->title = $this->context->i18n->__('Global search/replace');

    if ($request->isMethod('post'))
    {
      // Make sure we have required information for search/replace
      if (empty($request->pattern) || empty($request->replacement))
      {
        $this->error = $this->context->i18n->__('Both source and replacement fields are required.');
        return;
      }
      // Make sure we have confirmed the action
      else if (!isset($request->confirm))
      {
        $this->title = $this->context->i18n->__('Are you sure you want to replace "%1%" with "%2%" in %3%?', array('%1%' => $request->pattern, '%2%' => $request->replacement, '%3%' => sfInflector::humanize(sfInflector::underscore($request->column))));
        return;
      }

      // Process replacement on each IO
      // NB: could this be made faster by reading a batch of IDs?
      foreach ($this->pager->hits as $hit)
      {
        $io = QubitInformationObject::getById($hit->getDocument()->id);

        // Omit iteration if the column does not exist
        if (!$io->__isset($request->column))
        {
          continue;
        }

        if (isset($request->allowRegex))
        {
          $pattern = '/' . strtr($request->pattern, array('/' => '\/')) . '/';
          if (!isset($request->caseSensitive)) $pattern .= 'i';

          $replacement = strtr($request->replacement, array('/' => '\/'));

          $replaced = preg_replace($pattern, $replacement, $io->__get($request->column));
        }
        elseif (isset($request->caseSensitive))
        {
          $replaced = str_replace($request->pattern, $request->replacement, $io->__get($request->column));
        }
        else
        {
          $replaced = str_ireplace($request->pattern, $request->replacement, $io->__get($request->column));
        }

        $io->__set($request->column, $replaced);
        $io->save();
      }

      // force refresh of index to keep sync
      QubitSearch::getInstance()->optimize();

      // When complete, redirect to GSR home
      $this->redirect(array('module' => 'search', 'action' => 'globalReplace'));
    }
  }

  public function addFields()
  {
    // Information object attribute (db column) to perform s/r on
    $map = new InformationObjectI18nTableMap;

    foreach ($map->getColumns() as $col)
    {
      if (!$col->isPrimaryKey() && !$col->isForeignKey())
      {
        $col_name = $col->getPhpName();
        $choices[$col_name] = sfInflector::humanize(sfInflector::underscore($col_name));
      }
    }
    $choices['identifier'] = $this->context->i18n->__('Identifier');

    $this->form->setValidator('column', new sfValidatorString);
    $this->form->setWidget('column', new sfWidgetFormSelect(array('choices' => $choices), array('style' => 'width: auto')));

    // Search-replace values
    $this->form->setValidator('pattern', new sfValidatorString);
    $this->form->setWidget('pattern', new sfWidgetFormInput);

    $this->form->setValidator('replacement', new sfValidatorString);
    $this->form->setWidget('replacement', new sfWidgetFormInput);

    $this->form->setValidator('caseSensitive', new sfValidatorBoolean);
    $this->form->setWidget('caseSensitive', new sfWidgetFormInputCheckbox);

    $this->form->setValidator('allowRegex', new sfValidatorBoolean);
    $this->form->setWidget('allowRegex', new sfWidgetFormInputCheckbox);

    if ($this->request->isMethod('post') && !isset($this->request->confirm) && !empty($this->request->pattern) && !empty($this->request->replacement))
    {
      $this->form->setValidator('confirm', new sfValidatorBoolean);
      $this->form->setWidget('confirm', new sfWidgetFormInputHidden(array(), array('value' => true)));
    }
  }
}
