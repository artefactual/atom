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

class SettingsDigitalObjectDerivativesAction extends DefaultEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'pdfPageNumber',
      'refImageMaxWidth');

  protected static $refImageMaxWidthMin = 100;
  protected static $refImageMaxWidthMax = 2000;

  protected function earlyExecute()
  {
    $this->i18n = sfContext::getInstance()->i18n;
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'pdfPageNumber':
        $default = 1;
        if (null !== $this->settingPdfPageNumber = QubitSetting::getByName('digital_object_derivatives_pdf_page_number'))
        {
          $default = $this->settingPdfPageNumber->getValue(array('sourceCulture' => true));
        }
        $this->form->setDefault('pdfPageNumber', $default);
        $this->form->setValidator('pdfPageNumber', new sfValidatorInteger(array('min' => 1)));
        $this->form->setWidget('pdfPageNumber', new sfWidgetFormInput);

        break;

      case 'refImageMaxWidth':
        $default = null;
        if (null !== $this->settingRefImageMaxWidth = QubitSetting::getByName('reference_image_maxwidth'))
        {
          $default = $this->settingRefImageMaxWidth->getValue(array('sourceCulture' => true));
        }
        $this->form->setDefault('refImageMaxWidth', $default);
        $this->form->setValidator('refImageMaxWidth', new sfValidatorInteger(
          array('min' => self::$refImageMaxWidthMin, 'max' => self::$refImageMaxWidthMax),
          array('min' => $this->i18n->__('This value must be at least %min% pixels'), 'max' => $this->i18n->__('This value can not be greater than %max% pixels'))
        ));
        $this->form->setWidget('refImageMaxWidth', new sfWidgetFormInput);

        break;
    }
  }

  protected function processField($field)
  {
    switch ($field->getName())
    {
      case 'pdfPageNumber':
        if (null === $this->settingPdfPageNumber)
        {
          $this->settingPdfPageNumber = new QubitSetting;
          $this->settingPdfPageNumber->name = 'digital_object_derivatives_pdf_page_number';
        }
        $this->settingPdfPageNumber->setValue($field->getValue(), array('culture' => 'en'));
        $this->settingPdfPageNumber->save();
        break;

      case 'refImageMaxWidth':
        if (null === $this->settingRefImageMaxWidth)
        {
          $this->settingRefImageMaxWidth = new QubitSetting;
          $this->settingRefImageMaxWidth->name = 'reference_image_maxwidth';
        }


        $this->settingRefImageMaxWidth->setValue($field->getValue(), array('culture' => 'en'));
        $this->settingRefImageMaxWidth->save();
        break;
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    $this->pdfinfoAvailable = sfImageMagickAdapter::pdfinfoToolAvailable();

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      {
        $this->processForm();

        QubitCache::getInstance()->removePattern('settings:i18n:*');

        $this->redirect(array('module' => 'settings', 'action' => 'digitalObjectDerivatives'));
      }
    }
  }
}
