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
 * Settings module - "site information" form definition
 *
 * @package    AccesstoMemory
 * @subpackage settings
 * @author     Andy Koch <koch.andy@gmail.com>
 */
class SettingsPermissionsForm extends sfForm
{
  protected function newSubForm($act)
  {
    $choices = array('0' => 'Disallowed', '1' => 'Allowed');
    $form = new sfForm;

    $form->setWidgets(array(
      'allow_master'           => new sfWidgetFormSelect(array('choices' => $choices)),
      'allow_reference'        => new sfWidgetFormSelect(array('choices' => $choices)),
      'allow_thumb'            => new sfWidgetFormSelect(array('choices' => $choices)),
      'conditional_master'     => new sfWidgetFormSelect(array('choices' => $choices)),
      'conditional_reference'  => new sfWidgetFormSelect(array('choices' => $choices)),
      'conditional_thumb'      => new sfWidgetFormSelect(array('choices' => $choices)),
      'disallow_master'        => new sfWidgetFormSelect(array('choices' => $choices)),
      'disallow_reference'     => new sfWidgetFormSelect(array('choices' => $choices)),
      'disallow_thumb'         => new sfWidgetFormSelect(array('choices' => $choices))
    ));

    $form->widgetSchema->setLabels(array(
      'allow_master'           => 'View/Download Master representation',
      'allow_reference'        => 'View/Download Reference representation',
      'allow_thumb'            => 'View/Download Thumbnail representation',
      'conditional_master'     => 'View/Download Master representation',
      'conditional_reference'  => 'View/Download Reference representation',
      'conditional_thumb'      => 'View/Download Thumbnail representation',
      'disallow_master'        => 'View/Download Master representation',
      'disallow_reference'     => 'View/Download Reference representation',
      'disallow_thumb'         => 'View/Download Thumbnail representation'
    ));

    return $form;
  }

  public function configure()
  {
    $this->routing  = sfContext::getInstance()->getRouting();
    $this->acts = QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_ACT_ID);
    $formCollection = new sfForm;

    foreach ($this->acts as $i => $act)
    {
      $choices[$this->routing->generate(null, array($act, 'module' => 'term'))] = $act->__toString();
      $formCollection->embedForm($act->__toString(), $this->newSubForm($act));
    }

    $formCollection->setValidator('act', new sfValidatorString);
    $formCollection->setWidget('act', new sfWidgetFormSelect(array('choices' => $choices)));

    $this->embedForm('permissions', $formCollection);
  }
}
