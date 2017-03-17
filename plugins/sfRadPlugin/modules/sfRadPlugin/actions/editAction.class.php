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
 * Information Object - editRad
 *
 * @package    AccesstoMemory
 * @subpackage informationObject - initialize an editRad template for updating an information object
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class sfRadPluginEditAction extends InformationObjectEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'accessConditions',
      'accruals',
      'acquisition',
      'alternateTitle',
      'archivalHistory',
      'arrangement',
      'descriptionDetail',
      'descriptionIdentifier',
      'edition',
      'editionStatementOfResponsibility',
      'extentAndMedium',
      'findingAids',
      'identifier',
      'institutionResponsibleIdentifier',
      'issuingJurisdictionAndDenomination',
      'language',
      'languageNotes',
      'languageOfDescription',
      'levelOfDescription',
      'locationOfCopies',
      'locationOfOriginals',
      'nameAccessPoints',
      'noteOnPublishersSeries',
      'numberingWithinPublishersSeries',
      'otherTitleInformation',
      'otherTitleInformationOfPublishersSeries',
      'parallelTitleOfPublishersSeries',
      'physicalCharacteristics',
      'placeAccessPoints',
      'relatedUnitsOfDescription',
      'relatedMaterialDescriptions',
      'repository',
      'reproductionConditions',
      'revisionHistory',
      'rules',
      'scopeAndContent',
      'script',
      'scriptOfDescription',
      'sources',
      'standardNumber',
      'statementOfCoordinates',
      'statementOfProjection',
      'statementOfResponsibilityRelatingToPublishersSeries',
      'statementOfScaleArchitectural',
      'statementOfScaleCartographic',
      'subjectAccessPoints',
      'genreAccessPoints',
      'descriptionStatus',
      'title',
      'titleStatementOfResponsibility',
      'titleProperOfPublishersSeries',
      'type',
      'displayStandard',
      'displayStandardUpdateDescendants');

  public function earlyExecute()
  {
    parent::earlyExecute();

    $this->rad = new sfRadPlugin($this->resource);

    $title = $this->context->i18n->__('Add new archival description');
    if (isset($this->getRoute()->resource))
    {
      if (1 > strlen($title = $this->resource->__toString()))
      {
        $title = $this->context->i18n->__('Untitled');
      }

      $title = $this->context->i18n->__('Edit %1%', array('%1%' => $title));
    }

    $this->response->setTitle("$title - {$this->response->getTitle()}");

    $this->alternativeIdentifiersComponent = new InformationObjectAlternativeIdentifiersComponent($this->context, 'informationobject', 'alternativeIdentifiers');
    $this->alternativeIdentifiersComponent->resource = $this->resource;
    $this->alternativeIdentifiersComponent->execute($this->request);

    $this->eventComponent = new InformationObjectEventComponent($this->context, 'informationobject', 'event');
    $this->eventComponent->resource = $this->resource;
    $this->eventComponent->execute($this->request);

    $this->eventComponent->form->getWidgetSchema()->date->setHelp($this->context->i18n->__('"Give the date(s) of creation of the unit being described either as a single date, or range of dates (for inclusive dates and/or predominant dates). Always give the inclusive dates. When providing predominant dates, specify them as such, preceded by the word predominant..." (RAD 1.4B2) Record probable and uncertain dates in square brackets, using the conventions described in 1.4B5.'));
    $this->eventComponent->form->getWidgetSchema()->description->setHelp($this->context->i18n->__('"Make notes on dates and any details pertaining to the dates of creation, publication, or distribution, of the unit being described that are not included in the Date(s) of creation, including publication, distribution, etc., area and that are considered to be important." (RAD 1.8B8) "Make notes on the date(s) of accumulation or collection of the unit being described." (RAD 1.8B8a)'));
    $this->eventComponent->form->getWidgetSchema()->place->setHelp($this->context->i18n->__("\"For an item, transcribe a place of publication, distribution, etc., in the form and the grammatical case in which it appears.\" (RAD 1.4C1) {$this->eventComponent->form->getWidgetSchema()->place->getHelp()}"));
    $this->eventComponent->form->getWidgetSchema()->type->setHelp($this->context->i18n->__('Select the type of activity that established the relation between the authority record and the archival description (e.g. creation, accumulation, collection, publication, etc.)'));

    $this->titleNotesComponent = new InformationObjectNotesComponent($this->context, 'informationobject', 'notes');
    $this->titleNotesComponent->resource = $this->resource;
    $this->titleNotesComponent->execute($this->request, $options = array('type' => 'radTitleNotes'));

    $this->notesComponent = new InformationObjectNotesComponent($this->context, 'informationobject', 'notes');
    $this->notesComponent->resource = $this->resource;
    $this->notesComponent->execute($this->request, $options = array('type' => 'radNotes'));

    $this->otherNotesComponent = new InformationObjectNotesComponent($this->context, 'informationobject', 'notes');
    $this->otherNotesComponent->resource = $this->resource;
    $this->otherNotesComponent->execute($this->request, $options = array('type' => 'radOtherNotes'));
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'alternateTitle':
      case 'edition':
        $this->form->setDefault($name, $this->resource[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);

        break;

      case 'editionStatementOfResponsibility':
      case 'issuingJurisdictionAndDenomination':
      case 'noteOnPublishersSeries':
      case 'numberingWithinPublishersSeries':
      case 'otherTitleInformation':
      case 'otherTitleInformationOfPublishersSeries':
      case 'parallelTitleOfPublishersSeries':
      case 'standardNumber':
      case 'statementOfCoordinates':
      case 'statementOfProjection':
      case 'statementOfResponsibilityRelatingToPublishersSeries':
      case 'statementOfScaleArchitectural':
      case 'statementOfScaleCartographic':
      case 'titleStatementOfResponsibility':
      case 'titleProperOfPublishersSeries':
        $this->form->setDefault($name, $this->rad[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);

        break;

      case 'languageNotes':
        $this->form->setDefault($name, $this->rad[$name]);
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormTextarea);

        break;

      case 'type':
        $criteria = new Criteria;
        $this->resource->addObjectTermRelationsRelatedByObjectIdCriteria($criteria);
        QubitObjectTermRelation::addJoinTermCriteria($criteria);
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::MATERIAL_TYPE_ID);

        $value = array();
        foreach ($this->relations = QubitObjectTermRelation::get($criteria) as $item)
        {
          $value[] = $this->context->routing->generate(null, array($item->term, 'module' => 'term'));
        }

        $this->form->setDefault('type', $value);
        $this->form->setValidator('type', new sfValidatorPass);

        $choices = array();
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::MATERIAL_TYPE_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item;
        }

        $this->form->setWidget('type', new sfWidgetFormSelect(array('choices' => $choices, 'multiple' => true)));

        break;

      default:

        return parent::addField($name);
    }
  }

  protected function processField($field)
  {
    switch ($field->getName())
    {
      case 'editionStatementOfResponsibility':
      case 'issuingJurisdictionAndDenomination':
      case 'languageNotes':
      case 'noteOnPublishersSeries':
      case 'numberingWithinPublishersSeries':
      case 'otherTitleInformation':
      case 'otherTitleInformationOfPublishersSeries':
      case 'parallelTitleOfPublishersSeries':
      case 'standardNumber':
      case 'statementOfCoordinates':
      case 'statementOfProjection':
      case 'statementOfResponsibilityRelatingToPublishersSeries':
      case 'statementOfScaleArchitectural':
      case 'statementOfScaleCartographic':
      case 'titleProperOfPublishersSeries':
      case 'titleStatementOfResponsibility':
        $this->rad[$field->getName()] = $this->form->getValue($field->getName());

        break;

      case 'type':
        $value = $filtered = array();
        foreach ($this->form->getValue('type') as $item)
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($item));
          $resource = $params['_sf_route']->resource;
          $value[$resource->id] = $filtered[$resource->id] = $resource;
        }

        foreach ($this->relations as $item)
        {
          if (isset($value[$item->term->id]))
          {
            unset($filtered[$item->term->id]);
          }
          else
          {
            $item->delete();
          }
        }

        foreach ($filtered as $item)
        {
          $relation = new QubitObjectTermRelation;
          $relation->term = $item;

          $this->resource->objectTermRelationsRelatedByobjectId[] = $relation;
        }

        break;

      default:

        return parent::processField($field);
    }
  }

  protected function processForm()
  {
    $this->resource->sourceStandard = 'RAD version Jul2008';

    $this->alternativeIdentifiersComponent->processForm();

    $this->eventComponent->processForm();

    $this->titleNotesComponent->processForm();

    $this->notesComponent->processForm();

    $this->otherNotesComponent->processForm();

    return parent::processForm();
  }
}
