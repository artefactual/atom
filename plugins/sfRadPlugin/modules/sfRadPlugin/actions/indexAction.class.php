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
 * Information Object - showRad
 *
 * @package    AccesstoMemory
 * @subpackage informationObject - initialize a showRad template for displaying an information object
 * @author     Peter Van Garderen <peter@artefactual.com>
 */

class sfRadPluginIndexAction extends InformationObjectIndexAction
{
  public function execute($request)
  {
    parent::execute($request);

    $this->rad = new sfRadPlugin($this->resource);

    if (1 > strlen($title = $this->resource->__toString()))
    {
      $title = $this->context->i18n->__('Untitled');
    }

    // Set creator history label
    $this->creatorHistoryLabels = array(
      NULL => $this->context->i18n->__('Administrative history / Biographical sketch'),
      QubitTerm::CORPORATE_BODY_ID => $this->context->i18n->__('Administrative history'),
      QubitTerm::PERSON_ID => $this->context->i18n->__('Biographical sketch'),
      QubitTerm::FAMILY_ID => $this->context->i18n->__('Biographical sketch')
    );

    $this->response->setTitle("$title - {$this->response->getTitle()}");

    if (QubitAcl::check($this->resource, 'update'))
    {
      $validatorSchema = new sfValidatorSchema;
      $values = array();

      $validatorSchema->dates = new QubitValidatorCountable(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('This archival description requires at least one date.')));
      $values['dates'] = $this->resource->getDates();

      // Dates consistency
      $validatorSchema->dateRange = new QubitValidatorDates(array(), array(
        'invalid' => $this->context->i18n->__('Date(s) - are not consistent with %1%higher levels%2%.', array('%1%' => '<a href="%ancestor%">', '%2%' => '</a>'))));
      $values['dateRange'] = $this->resource;

      $validatorSchema->extentAndMedium = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('Physical description - This is a mandatory element.')));
      $values['extentAndMedium'] = $this->resource->getExtentAndMedium(array('cultureFallback' => true));

      $validatorSchema->title = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('Title - This is a mandatory element.')));
      $values['title'] = $this->resource->getTitle(array('cultureFallback' => true));

      $this->addField($validatorSchema, 'levelOfDescription');
      $validatorSchema->levelOfDescription->setMessage('forbidden', $this->context->i18n->__('Level of description - Value "%value%" is not consistent with higher levels.'));
      $validatorSchema->levelOfDescription->setMessage('required', $this->context->i18n->__('Level of description - This is a mandatory element.'));

      if (isset($this->resource->levelOfDescription))
      {
        $values['levelOfDescription'] = $this->resource->levelOfDescription->getName(array('sourceCulture' => true));
      }

      // Class of materials specific details
      foreach ($this->resource->getMaterialTypes() as $materialType)
      {
        switch ($materialType->term->getName(array('sourceCulture' => true)))
        {
          case 'Architectural drawing':
            $validatorSchema->statementOfScaleArchitectural = new sfValidatorString(array(
              'required' => true), array(
              'required' => $this->context->i18n->__('Statement of scale (architectural) - This is a mandatory element for architectural drawing.')));
            $values['statementOfScaleArchitectural'] = $this->rad->statementOfScaleArchitectural;

            break;

          case 'Cartographic material':
            $validatorSchema->statementOfCoordinates = new sfValidatorString(array(
              'required' => true), array(
              'required' => $this->context->i18n->__('Statement of coordinates (cartographic) - This is a mandatory element for cartographic material.')));
            $values['statementOfCoordinates'] = $this->rad->statementOfCoordinates;

            $validatorSchema->statementOfProjection = new sfValidatorString(array(
              'required' => true), array(
              'required' => $this->context->i18n->__('Statement of projection (cartographic) - This is a mandatory element for cartographic material.')));
            $values['statementOfProjection'] = $this->rad->statementOfProjection;

            $validatorSchema->statementOfScaleCartographic = new sfValidatorString(array(
              'required' => true), array(
              'required' => $this->context->i18n->__('Statement of scale (cartographic) - This is a mandatory element for cartographic material.')));
            $values['statementOfScaleCartographic'] = $this->rad->statementOfScaleCartographic;

            break;

          case 'Philatelic record':
            $validatorSchema->issuingJurisdictionAndDenomination = new sfValidatorString(array(
              'required' => true), array(
              'required' => $this->context->i18n->__('Issuing jurisdiction and denomination (philatelic) - This is a mandatory element for philatelic record.')));
            $values['issuingJurisdictionAndDenomination'] = $this->rad->issuingJurisdictionAndDenomination;

            break;
        }
      }

      if (isset($this->resource->levelOfDescription))
      {
        switch ($this->resource->levelOfDescription->getName(array('sourceCulture' => true)))
        {
          // Only if top level of description
          /* Disable custodial history validation temporary (see issue 1984)
          case 'Series':
          case 'Fonds':
          case 'Collection':

            if (!isset($this->resource->parent->parent))
            {
              $validatorSchema->custodialHistory = new sfValidatorString(array('required' => true), array('required' => $this->context->i18n->__('Custodial history - This is a mandatory element for top level of description.')));
              $values['custodialHistory'] = $this->resource->getArchivalHistory(array('cultureFallback' => true));
            }

            break;
          */

          case 'Series':
          case 'Fonds':
          case 'Collection':
          case 'Subseries':
          case 'Subfonds':

            $validatorSchema->scopeAndContent = new sfValidatorString(array(
              'required' => true), array(
              'required' => $this->context->i18n->__('Scope and content - This is a mandatory element.')));
            $values['scopeAndContent'] = $this->resource->getScopeAndContent(array('cultureFallback' => true));

            break;

          case 'Item':

            // No publication events?
            $isPublication = false;
            foreach ($this->resource->eventsRelatedByobjectId as $item)
            {
              if (QubitTerm::PUBLICATION_ID == $item->typeId)
              {
                $isPublication = true;

                break;
              }
            }

            if ($isPublication)
            {
              $validatorSchema->edition = new sfValidatorString(array(
                'required' => true), array(
                'required' => $this->context->i18n->__('Edition statement - This is a mandatory element for published items if there are multiple editions.')));
              $values['edition'] = $this->resource->getEdition(array('cultureFallback' => true));
            }
        }
      }

      try
      {
        $validatorSchema->clean($values);
      }
      catch (sfValidatorErrorSchema $e)
      {
        $this->errorSchema = $e;
      }
    }
  }
}
