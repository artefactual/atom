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

class InformationObjectNotesComponent extends sfComponent
{
  public function execute($request, $options = array())
  {
    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    $this->addField('content');

    if (isset($options['type']))
    {
      switch ($options['type'])
      {
        case 'radTitleNotes':
          $this->hiddenType = false;
          $this->taxonomyId = QubitTaxonomy::RAD_TITLE_NOTE_ID;
          $this->allNotes = $this->resource->getNotesByTaxonomy(array('taxonomyId' => $this->taxonomyId));
          $this->tableName = $this->context->i18n->__('Title notes');
          $this->arrayName = 'radTitleNotes';
          $this->help = $this->context->i18n->__('Select a note type from the drop-down menu and enter note text in accordance with RAD 1.8B1 through 1.8B6.');

          $this->addField('type');

          break;

        case 'radOtherNotes':
          $this->hiddenType = false;
          $this->taxonomyId = QubitTaxonomy::RAD_NOTE_ID;
          $this->allNotes = $this->resource->getNotesByTaxonomy(array('taxonomyId' => $this->taxonomyId));
          $this->tableName = $this->context->i18n->__('Other notes');
          $this->arrayName = 'radOtherNotes';
          $this->help = $this->context->i18n->__('Select a note type from the drop-down menu and enter note text in accordance with the following sections in RAD: 1.5E (Accompanying material); 1.8 B11 (Alpha-numeric designations); 1.8B9b (Conservation); 1.8B7 (Edition); 1.8B9 (Physical Description); 1.8B16b (Rights).');

          $this->addField('type');

          break;

        case 'isadPublicationNotes':
          $this->hiddenType = true;
          $this->hiddenTypeId = QubitTerm::PUBLICATION_NOTE_ID;
          $this->allNotes = $this->resource->getNotesByType(array('noteTypeId' => $this->hiddenTypeId));
          $this->tableName = $this->context->i18n->__('Publication notes');
          $this->arrayName = 'isadPublicationNotes';
          $this->help = $this->context->i18n->__('Record a citation to, and/or information about a publication that is about or based on the use, study, or analysis of the unit of description. Include references to published facsimiles or transcriptions. (ISAD 3.5.4)');

          break;

        case 'dacsPublicationNotes':
          $this->hiddenType = true;
          $this->hiddenTypeId = QubitTerm::PUBLICATION_NOTE_ID;
          $this->allNotes = $this->resource->getNotesByType(array('noteTypeId' => $this->hiddenTypeId));
          $this->tableName = $this->context->i18n->__('Publication notes');
          $this->arrayName = 'dacsPublicationNotes';
          $this->help = $this->context->i18n->__('Record a citation to, or information about, a publication that is about or is based on the use, study, or analysis of the materials being described. Provide sufficient information to indicate the relationship between the publication and the unit being described. This includes annotated editions. (DACS 6.4.4)');

          break;

        case 'radNotes':
          $this->hiddenType = true;
          $this->hiddenTypeId = QubitTerm::GENERAL_NOTE_ID;
          $this->allNotes = $this->resource->getNotesByType(array('noteTypeId' => $this->hiddenTypeId));
          $this->tableName = $this->context->i18n->__('General note(s)');
          $this->arrayName = 'radNotes';
          $this->help = $this->context->i18n->__('"Use this note to record any other descriptive information considered important but not falling within the definitions of the other notes." (RAD 1.8B21)');

          break;

        case 'isadNotes':
          $this->hiddenType = true;
          $this->hiddenTypeId = QubitTerm::GENERAL_NOTE_ID;
          $this->allNotes = $this->resource->getNotesByType(array('noteTypeId' => $this->hiddenTypeId));
          $this->tableName = $this->context->i18n->__('Notes');
          $this->arrayName = 'isadNotes';
          $this->help = $this->context->i18n->__('Record specialized or other important information not accommodated by any of the defined elements of description. (ISAD 3.6.1)');

          break;

        case 'dacsNotes':
          $this->hiddenType = true;
          $this->hiddenTypeId = QubitTerm::GENERAL_NOTE_ID;
          $this->allNotes = $this->resource->getNotesByType(array('noteTypeId' => $this->hiddenTypeId));
          $this->tableName = $this->context->i18n->__('General note(s)');
          $this->arrayName = 'dacsNotes';
          $this->help = $this->context->i18n->__('Record, as needed, information not accommodated by any of the defined elements of description. (DACS 7.1.2)');

          break;

        case 'dacsSpecializedNotes':
          $this->hiddenType = false;
          $this->taxonomyId = QubitTaxonomy::DACS_NOTE_ID;
          $this->allNotes = $this->resource->getNotesByTaxonomy(array('taxonomyId' => $this->taxonomyId));
          $this->tableName = $this->context->i18n->__('Specialized note(s)');
          $this->arrayName = 'dacsSpecializedNotes';
          $this->help = $this->context->i18n->__('Select a note type from the drop-down menu and record, as needed, specialized information not accommodated by any of the defined elements of description, including Conservation (DACS 7.1.3), Citation (DACS 7.1.5), Alphanumeric designations (DACS 7.1.6), Variant title information (DACS 7.1.7), or Processing information (DACS 7.1.8).');

          $this->addField('type');

          break;

        case 'isadArchivistsNotes':
          $this->hiddenType = true;
          $this->hiddenTypeId = QubitTerm::ARCHIVIST_NOTE_ID;
          $this->allNotes = $this->resource->getNotesByType(array('noteTypeId' => $this->hiddenTypeId));
          $this->tableName = $this->context->i18n->__('Archivist\'s notes');
          $this->arrayName = 'isadArchivistsNotes';
          $this->help = $this->context->i18n->__('Record notes on sources consulted in preparing the description and who prepared it. (ISAD 3.7.1)');

          break;

        case 'dacsArchivistsNotes':
          $this->hiddenType = true;
          $this->hiddenTypeId = QubitTerm::ARCHIVIST_NOTE_ID;
          $this->allNotes = $this->resource->getNotesByType(array('noteTypeId' => $this->hiddenTypeId));
          $this->tableName = $this->context->i18n->__('Archivist and date');
          $this->arrayName = 'dacsArchivistsNotes';
          $this->help = $this->context->i18n->__('Record the name(s) of the person(s) who created or revised the description, as well as the creation or revision date. (DACS 8.1.5)');

          break;
      }

      // Ignore notes where the desired translation is not available
      $culture = sfContext::getInstance()->getUser()->getCulture();
      $this->notes = array();
      if (isset($this->allNotes))
      {
        foreach ($this->allNotes as $note)
        {
          if (0 < strlen($note->getContent(array('culture' => $culture))) || 0 < strlen($note->getContent(array('sourceCulture' => true))))
          {
            $this->notes[] = $note;
          }
        }
      }
    }
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'content':
        $this->form->setValidator('content', new sfValidatorString);
        $this->form->setWidget('content', new sfWidgetFormTextarea);

        break;

      case 'type':
        $choices = array();
        foreach (QubitTerm::getOptionsForSelectList($this->taxonomyId) as $value => $label)
        {
          $choices[$value] = htmlentities($label, ENT_QUOTES, sfConfig::get('sf_charset'));
        }

        $this->form->setValidator('type', new sfValidatorString);
        $this->form->setWidget('type', new sfWidgetFormSelect(array('choices' => $choices)));

        break;
    }
  }

  public function processForm()
  {
    $params = array();
    if (isset($this->request->{$this->arrayName}))
    {
      $params = $this->request->{$this->arrayName};
    }

    $finalNotes = array();
    foreach ($params as $item)
    {
      $this->note = null;
      if (isset($item['id']))
      {
        $this->note = QubitNote::getById($item['id']);

        // Store notes that haven't been deleted by multiRow.js
        $finalNotes[] = $this->note->id;
      }

      // Continue only if user typed something
      if (1 > strlen($item['content']))
      {
        // TODO: if the user is in translation mode and nothing is typed,
        // the type changes won't be saved
        continue;
      }

      $this->form->bind($item);
      if ($this->form->isValid())
      {
        if (is_null($this->note))
        {
          $this->resource->notes[] =  $this->note = new QubitNote;
        }

        if (isset($item['type']))
        {
          $this->note['typeId'] = $item['type'];
        }
        if (isset($item['content']))
        {
          $this->note['content'] = $item['content'];
        }

        // Save the old notes, because adding a new note with "$this->resource->notes[] ="
        // overrides the unsaved changes.
        //
        // We also do an additional check against resource id and note objectId; if they do
        // not match, we're in duplicate record mode and want to avoid modifying the original
        // record's notes.
        if (isset($item['id']) && $this->note->objectId == $this->resource->id)
        {
          $this->note->save();
        }
      }
    }

    // Delete the old notes if they don't appear in the table (removed by multiRow.js)
    foreach ($this->notes as $item)
    {
      if (false === array_search($item->id, $finalNotes))
      {
        $item->delete();
      }
    }
  }
}
