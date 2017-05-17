<?php decorate_with('layout_2col.php') ?>
<?php use_helper('Date') ?>

<?php slot('sidebar') ?>

  <?php include_component('repository', 'contextMenu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo render_title($rad) ?></h1>

  <?php if (isset($sf_request->source)): ?>
    <div class="messages status">
      <?php echo __('This is a duplicate of record %1%', array('%1%' => $sourceInformationObjectLabel)) ?>
    </div>
  <?php endif; ?>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderGlobalErrors() ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
    <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'edit')), array('id' => 'editForm')) ?>
  <?php else: ?>
    <?php echo $form->renderFormTag(url_for(array('module' => 'informationobject', 'action' => 'add')), array('id' => 'editForm')) ?>
  <?php endif; ?>

    <?php echo $form->renderHiddenFields() ?>

    <div id="content">

      <fieldset class="collapsible collapsed" id="titleAndStatementOfResponsibilityArea">

        <legend><?php echo __('Title and statement of responsibility area') ?></legend>

        <?php echo render_field($form->title
          ->help(__('Enter the title proper, either transcribed or supplied. (RAD 1.1B)'))
          ->label(__('Title proper').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource) ?>

        <?php echo $form->type
          ->help(__('Select the General Material Designation at the highest level of description. If there are more than three, select "multiple media" (RAD 1.1C)'))
          ->label(__('General material designation'))
          ->renderRow() ?>

        <?php echo render_field($form->alternateTitle
          ->help(__('"[W]hen applicable, transcribe parallel titles that appear in conjunction with the formal title proper..." (RAD 1.1D)'))
          ->label(__('Parallel titles')), $resource) ?>

        <?php echo render_field($form->otherTitleInformation
          ->help(__('"Transcribe other title information that appears in conjunction with the formal title proper..." (RAD 1.1E)'))
          ->label(__('Other title information')), $rad) ?>

        <?php echo render_field($form->titleStatementOfResponsibility
          ->help(__('"At the item level of description, transcribe explicit statements of responsibility appearing in conjunction with the formal title proper in or on the chief source of information..." (RAD 1.1F)'))
          ->label(__('Statement of responsibility')), $rad) ?>

        <?php echo get_partial('informationobject/notes', $sf_data->getRaw('titleNotesComponent')->getVarHolder()->getAll()) ?>

        <?php echo $form->levelOfDescription
          ->help(__('Select a level of description from the drop-down menu. See RAD 1.0A for rules and conventions on selecting levels of description.'))
          ->label(__('Level of description').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
          ->renderRow() ?>

        <?php echo get_partial('informationobject/childLevels', array('help' => __('Identifier: Enter an unambiguous code used to uniquely identify the description. Level: Select a level of description from the drop-down menu. See RAD 1.0A for rules and conventions on selecting levels of description. Title: Enter the title proper, either transcribed or supplied. (RAD 1.1B)'))) ?>

        <div class="form-item">
          <?php echo $form->repository->renderLabel() ?>
          <?php echo $form->repository->render(array('class' => 'form-autocomplete')) ?>
          <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'repository', 'action' => 'add')) ?> #authorizedFormOfName"/>
          <input class="list" type="hidden" value="<?php echo url_for($sf_data->getRaw('repoAcParams')) ?>"/>
          <?php echo $form->repository
            ->help(__('Select the repository that has custody and control of the archival material. The values in this field are drawn from the Authorized form of name field in archival institution records. Search for an existing name by typing the first few characters of the name. Alternatively, type a new name to create and link to a new archival institution.'))
            ->renderHelp() ?>
        </div>

        <?php echo $form->identifier
          ->help(__('Enter an unambiguous code used to uniquely identify the description.'))
          ->renderRow() ?>

        <?php echo get_partial('informationobject/identifierOptions', array('mask' => $mask)) ?>
        <?php echo get_partial('informationobject/alternativeIdentifiers', $sf_data->getRaw('alternativeIdentifiersComponent')->getVarHolder()->getAll()) ?>

        <?php echo render_show(__('Reference code'), $rad->referenceCode) ?>

      </fieldset> <!-- #titleAndStatementOfResponsibilityArea -->

      <fieldset class="collapsible collapsed" id="editionArea">

        <legend><?php echo __('Edition area') ?></legend>

        <?php echo render_field($form->edition
          ->help(__('"Use this area only in item level description to record statements relating to versions of items existing in two or more versions or states in single or multiple copies." (RAD 1.2A1) "Transcribe the edition statement relating to the item being described." (RAD 1.2B1) "If the item being described lacks an edition statement but is known to contain significant changes from other editions, supply a suitable brief statement in the language and script of the title proper and enclose it in square brackets." (RAD 1.2B3)'))
          ->label(__('Edition statement')), $resource) ?>

        <?php echo render_field($form->editionStatementOfResponsibility
          ->help(__('"Transcribe a statement of responsibility relating to one or more editions, but not to all editions, of the item being described following the edition statement if there is one." (RAD 1.2.C1) "When describing the first edition, give all statements of responsibility in the Title and statement of responsibility area." (RAD 1.2C2)'))
          ->label(__('Statement of responsibility relating to the edition')), $rad) ?>

      </fieldset> <!-- #editionArea -->

      <fieldset class="collapsible collapsed" id="classOfMaterialSpecificDetailsArea">

        <legend><?php echo __('Class of material specific details area') ?></legend>

        <?php echo render_field($form->statementOfScaleCartographic
          ->help(__('"Give the scale of the unit being described...as a representative fraction (RF) expressed as a ratio (1: ). Precede the ratio by Scale. Give the scale even if it is already recorded as part of the title proper or other title information." (RAD 5.3B1)'))
          ->label(__('Statement of scale (cartographic)')), $rad) ?>

        <?php echo render_field($form->statementOfProjection
          ->help(__('"Give the statement of projection if it is found on the prescribed source(s) of information." (RAD 5.3C1)'))
          ->label(__('Statement of projection (cartographic)')), $rad) ?>

        <?php echo render_field($form->statementOfCoordinates
          ->help(__('"At the fonds, series or file levels, record coordinates for the maximum coverage provided by the materials in the unit, as long as they are reasonably contiguous." (RAD 5.3D)'))
          ->label(__('Statement of coordinates (cartographic)')), $rad) ?>

        <?php echo render_field($form->statementOfScaleArchitectural
          ->help(__('"Give in English the scale in the units of measure found on the unit being described. If there is no English equivalent for the name of the unit of measure, give the name, within quotation marks, as found on the unit being described." (RAD 6.3B)'))
          ->label(__('Statement of scale (architectural)')), $rad) ?>

        <?php echo render_field($form->issuingJurisdictionAndDenomination
          ->help(__('"Give the name of the jurisdiction (e.g., government) responsible for issuing the philatelic records." (RAD 12.3B1) "For all units possessing a denomination (e.g., postage stamps, revenue stamps, postal stationery items), give the denomination in a standardized format, recording the denomination number in arabic numerals followed by the name of the currency unit. Include a denomination statement even if the denomination is already recorded as part of the title proper or other title information." (RAD 12.3C1)'))
          ->label(__('Issuing jurisdiction and denomination (philatelic)')), $rad) ?>

      </fieldset> <!-- #classOfMaterialSpecificDetailsArea -->

      <fieldset class="collapsible collapsed" id="datesOfCreationArea">

        <legend><?php echo __('Dates of creation area') ?></legend>

        <?php echo get_partial('informationobject/relatedEvents', array('resource' => $resource)) ?>

        <div class="section">

          <h3><?php echo __('Add new name and/or date(s)') ?></h3>

          <?php echo get_partial('informationobject/event', $sf_data->getRaw('eventComponent')->getVarHolder()->getAll()) ?>

        </div>

      </fieldset> <!-- #datesOfCreationArea -->

      <fieldset class="collapsible collapsed" id="physicalDescriptionArea">

        <legend><?php echo __('Physical description area') ?></legend>

        <?php echo render_field($form->extentAndMedium
          ->help(__('"At all levels record the extent of the unit being described by giving the number of physical units in arabic numerals and the specific material designation as instructed in subrule .5B in the chapter(s) dealing with the broad class(es) of material to which the unit being described belongs." (RAD 1.5B1) Include other physical details and dimensions as specified in RAD 1.5C and 1.5D. Separate multiple entries in this field with a carriage return (i.e. press the Enter key on your keyboard).'))
          ->label(__('Physical description').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource, array('class' => 'resizable')) ?>

      </fieldset> <!-- #physicalDescriptionArea -->

      <fieldset class="collapsible collapsed" id="publishersSeriesArea">

        <legend><?php echo __('Publisher\'s series area') ?></legend>

        <?php echo render_field($form->titleProperOfPublishersSeries
          ->help(__('"At the item level of description, transcribe a title proper of the publisher\'s series as instructed in 1.1B1." (RAD 1.6B)'))
          ->label(__('Title proper of publisher\'s series')), $rad) ?>

        <?php echo render_field($form->parallelTitleOfPublishersSeries
          ->help(__('"Transcribe parallel titles of a publisher\'s series as instructed in 1.1D." (RAD 1.6C1)'))
          ->label(__('Parallel title of publisher\'s series')), $rad) ?>

        <?php echo render_field($form->otherTitleInformationOfPublishersSeries
          ->help(__('"Transcribe other title information of a publisher\'s series as instructed in 1.1E and only if considered necessary for identifying the publisher\'s series." (RAD 1.6D1)'))
          ->label(__('Other title information of publisher\'s series')), $rad) ?>

        <?php echo render_field($form->statementOfResponsibilityRelatingToPublishersSeries
          ->help(__('"Transcribe explicit statements of responsibility appearing in conjunction with a formal title proper of a publisher\'s series as instructed in 1.1F and only if considered necessary for identifying the publisher\'s series." (RAD 1.6E1)'))
          ->label(__('Statement of responsibility relating to publisher\'s series')), $rad) ?>

        <?php echo render_field($form->numberingWithinPublishersSeries
          ->help(__('"Give the numbering of the item within a publisher\'s series in the terms given in the item." (RAD 1.6F1)'))
          ->label(__('Numbering within publisher\'s series')), $rad) ?>

        <?php echo render_field($form->noteOnPublishersSeries
          ->help(__('"Make notes on important details of publisher\'s series that are not included in the Publisher\'s series area, including variant series titles, incomplete series, and of numbers or letters that imply a series." (RAD 1.8B10)'))
          ->label(__('Note on publisher\'s series')), $rad) ?>

      </fieldset> <!-- #publishersSeriesArea -->

      <fieldset class="collapsible collapsed" id="archivalDescriptionArea">

        <legend><?php echo __('Archival description area') ?></legend>

        <?php foreach ($resource->getCreators() as $item): ?>
          <div class="field">

            <h3>
              <?php if (null !== ($entityType = $item->entityType) && QubitTerm::CORPORATE_BODY_ID == $entityType->id): ?>
                <?php echo __('Administrative history') ?>
              <?php else: ?>
                <?php echo __('Biographical sketch') ?>
              <?php endif; ?>
            </h3>

            <div>
              <?php echo link_to('<h3>'.render_title($item).'</h3>', array($item, 'module' => 'actor', 'action' => 'edit'), array('title' => __('Edit authority record'))) ?>
              <?php echo $item->getHistory(array('cultureFallback' => 'true')) ?>
            </div>

          </div>
        <?php endforeach; ?>

        <?php echo render_field($form->archivalHistory
          ->help(__('"Give the history of the custody of the unit being described, i.e., the successive transfers of ownership and custody or control of the material, along with the dates thereof, insofar as it can be ascertained." (RAD 1.7C)'))
          ->label(__('Custodial history')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->scopeAndContent
          ->help(__('"At the fonds, series, and collection levels of description, and when necessary at the file and the item levels of description, indicate the level being described and give information about the scope and the internal structure of or arrangement of the records, and about their contents." (RAD 1.7D) "For the scope of the unit being described, give information about the functions and/or kinds of activities generating the records, the period of time, the subject matter, and the geographical area to which they pertain. For the content of the unit being described, give information about its internal structure by indicating its arrangement, organization, and/or enumerating its next lowest level of description. Summarize the principal documentary forms (e.g., reports, minutes, correspondence, drawings, speeches)." (RAD 1.7D1)'))
          ->label(__('Scope and content').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource, array('class' => 'resizable')) ?>

      </fieldset> <!-- #archivalDescriptionArea -->

      <fieldset class="collapsible collapsed" id="notesArea">

        <legend><?php echo __('Notes area') ?></legend>

        <?php echo render_field($form->physicalCharacteristics
          ->help(__('"Make notes on the physical condition of the unit being described if that condition materially affects the clarity or legibility of the records." (RAD 1.8B9a)'))
          ->label(__('Physical condition')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->acquisition
          ->help(__('"Record the donor or source (i.e., the immediate prior custodian) from whom the unit being described was acquired, and the date and method of acquisition, as well as the source/donor\'s relationship to the material, if any or all of this information is not confidential. If the source/donor is unknown, record that information." (RAD 1.8B12)'))
          ->label(__('Immediate source of acquisition')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->arrangement
          ->help(__('"Make notes on the arrangement of the unit being described which contribute significantly to its understanding but cannot be put in the Scope and content (see 1.7D), e.g., about reorganisation(s) by the creator, arrangement by the archivist, changes in the classification scheme, or reconstitution of original order." (RAD 1.8B13)')), $resource, array('class' => 'resizable')) ?>

        <?php echo $form->language
          ->help(__('"Record the language or languages of the unit being described, unless they are noted elsewhere or are apparent from other elements of the description." (RAD 1.8.B14). Select the language from the drop-down menu; enter the first few letters to narrow the choices.'))
          ->label(__('Language of material'))
          ->renderRow(array('class' => 'form-autocomplete')) ?>

        <?php echo $form->script
          ->help(__('"Note any distinctive alphabets or symbol systems employed." (RAD 1.8.B14) Select the script from the drop-down menu; enter the first few letters to narrow the choices.'))
          ->label(__('Script of material'))
          ->renderRow(array('class' => 'form-autocomplete')) ?>

        <?php echo render_field($form->languageNotes
          ->help(__('"Record the language or languages of the unit being described, unless they are noted elsewhere or are apparent from other elements of the description. Also note any distinctive alphabets or symbol systems employed." (RAD 1.8B14). Do not duplicate information added via the drop-down in the language or script fields.'))
          ->label(__('Language and script notes')), $rad, array('class' => 'resizable')) ?>

        <?php echo render_field($form->locationOfOriginals
          ->help(__('"If the unit being described is a reproduction and the location of the original material is known, give that location. Give, in addition, any identifying numbers that may help in locating the original material in the cited location. If the originals are known to be no longer extant, give that information." (RAD 1.8B15a)')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->locationOfCopies
          ->help(__('"If all or part of the unit being described is available (either in the institution or elsewhere) in another format(s), e.g., if the text being described is also available on microfilm; or if a film is also available on videocassette, make a note indicating the other format(s) in which the unit being described is available and its location, if that information is known. If only a part of the unit being described is available in another format(s), indicate which parts." (RAD 1.8B15b)'))
          ->label(__('Availability of other formats')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->accessConditions
          ->help(__('"Give information about any restrictions placed on access to the unit (or parts of the unit) being described." (RAD 1.8B16a)'))
          ->label(__('Restrictions on access')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->reproductionConditions
          ->help(__('For terms governing use and reproduction, "Give information on legal or donor restrictions that may affect use or reproduction of the material." (RAD 1.8B16c). For terms governing publication, "Give information on legal or donor restrictions that may affect publication of the material." (RAD 1.8B16d)'))
          ->label(__('Terms governing use, reproduction, and publication')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->findingAids
          ->help(__('"Give information regarding the existence of any finding aids. Include appropriate administrative and/or intellectual control tools over the material in existence at the time the unit is described, such as card catalogues, box lists, series lists, inventories, indexes, etc." (RAD 1.8B17)')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->relatedUnitsOfDescription
          ->help(__('For associated material, "If records in another institution are associated with the unit being described by virtue of the fact that they share the same provenance, make a citation to the associated material at the fonds, series or collection level, or for discrete items, indicating its location if known." (RAD 1.8B18). For related material, "Indicate groups of records having some significant relationship by reason of shared responsibility or shared sphere of activity in one or more units of material external to the unit being described." (RAD 1.8B20)'))
          ->label(__('Associated materials')), $resource, array('class' => 'resizable')) ?>

        <div class="form-item">
          <?php echo $form->relatedMaterialDescriptions
            ->label(__('Related materials'))
            ->renderLabel() ?>
          <?php echo $form->relatedMaterialDescriptions->render(array('class' => 'form-autocomplete')) ?>
          <?php if (QubitAcl::check(QubitInformationObject::getRoot(), 'create')): ?>
            <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'informationobject', 'action' => 'add')) ?> #title"/>
          <?php endif; ?>
          <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'informationobject', 'action' => 'autocomplete')) ?>"/>
          <?php echo $form->relatedMaterialDescriptions
            ->help(__('To create a relationship between this description and another description held in AtoM, begin typing the name of the related description and select it from the autocomplete drop-down menu when it appears below. Multiple relationships can be created.'))
            ->renderHelp() ?>
        </div>

        <?php echo render_field($form->accruals
          ->help(__('"When the unit being described is not yet complete, e.g., an open fonds or series, make a note explaining that further accruals are expected... If no further accruals are expected, indicate that the unit is considered closed." (RAD 1.8B19)')), $resource, array('class' => 'resizable')) ?>

        <?php echo get_partial('informationobject/notes', $sf_data->getRaw('notesComponent')->getVarHolder()->getAll()) ?>

        <?php echo get_partial('informationobject/notes', $sf_data->getRaw('otherNotesComponent')->getVarHolder()->getAll()) ?>

      </fieldset> <!-- #notesArea -->

      <fieldset class="collapsible collapsed" id="standardNumberArea">

        <legend><?php echo __('Standard number area') ?></legend>

        <?php echo render_field($form->standardNumber
          ->help(__('"Give the International Standard Book Number (ISBN), International Standard Serial Number (ISSN), or any other internationally agreed standard number for the item being described. Give such numbers with the agreed abbreviation and with the standard spacing or hyphenation." (RAD 1.9B1)')), $rad) ?>

      </fieldset> <!-- #standardNumberArea -->

      <fieldset class="collapsible collapsed" id="accessPointsArea">

        <legend><?php echo __('Access points') ?></legend>

        <div class="form-item">
          <?php echo $form->subjectAccessPoints
            ->label(__('Subject access points'))
            ->renderLabel() ?>
          <?php echo $form->subjectAccessPoints->render(array('class' => 'form-autocomplete')) ?>
          <?php if (QubitAcl::check(QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID), 'createTerm')): ?>
            <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID), 'module' => 'taxonomy')))) ?> #name"/>
          <?php endif; ?>
          <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID), 'module' => 'taxonomy')))) ?>"/>
          <?php echo $form->subjectAccessPoints
            ->help(__('Search for an existing term in the Subjects taxonomy by typing the first few characters of the term. Alternatively, type a new term to create and link to a new subject term.'))
            ->renderHelp() ?>
        </div>

        <div class="form-item">
          <?php echo $form->placeAccessPoints
            ->label(__('Place access points'))
            ->renderLabel() ?>
          <?php echo $form->placeAccessPoints->render(array('class' => 'form-autocomplete')) ?>
          <?php if (QubitAcl::check(QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'createTerm')): ?>
            <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'module' => 'taxonomy')))) ?> #name"/>
          <?php endif; ?>
          <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'module' => 'taxonomy')))) ?>"/>
          <?php echo $form->placeAccessPoints
            ->help(__('Search for an existing term in the Places taxonomy by typing the first few characters of the term name. Alternatively, type a new term to create and link to a new place term.'))
            ->renderHelp() ?>
        </div>

        <div class="form-item">
          <?php echo $form->genreAccessPoints
            ->label(__('Genre access points'))
            ->renderLabel() ?>
          <?php echo $form->genreAccessPoints->render(array('class' => 'form-autocomplete')) ?>
          <?php if (QubitAcl::check(QubitTaxonomy::getById(QubitTaxonomy::GENRE_ID), 'createTerm')): ?>
            <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::GENRE_ID), 'module' => 'taxonomy')))) ?> #name"/>
          <?php endif; ?>
          <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::GENRE_ID), 'module' => 'taxonomy')))) ?>"/>
          <?php echo $form->genreAccessPoints
            ->help(__('Search for an existing term in the Genre taxonomy by typing the first few characters of the term name. Alternatively, type a new term to create and link to a new genre term.'))
            ->renderHelp() ?>
        </div>

        <div class="form-item">
          <?php echo $form->nameAccessPoints
            ->label(__('Name access points (subjects)'))
            ->renderLabel() ?>
          <?php echo $form->nameAccessPoints->render(array('class' => 'form-autocomplete')) ?>
          <?php if (QubitAcl::check(QubitActor::getRoot(), 'create')): ?>
            <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'actor', 'action' => 'add')) ?> #authorizedFormOfName"/>
          <?php endif; ?>
          <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'actor', 'action' => 'autocomplete', 'showOnlyActors' => 'true')) ?>"/>
          <?php echo $form->nameAccessPoints
            ->help(__('"Choose provenance, author and other non-subject access points from the archival description, as appropriate. All access points must be apparent from the archival description to which they relate." (RAD 21.0B) The values in this field are drawn from the Authorized form of name field in authority records. Search for an existing name by typing the first few characters of the name. Alternatively, type a new name to create and link to a new authority record.'))
            ->renderHelp() ?>
        </div>

      </fieldset> <!-- #accessPointsArea -->

      <fieldset class="collapsible collapsed" id="descriptionControlArea">

        <legend><?php echo __('Control area') ?></legend>

        <?php echo $form->descriptionIdentifier
          ->help(__('Record a unique description identifier in accordance with local and/or national conventions. If the description is to be used internationally, record the code of the country in which the description was created in accordance with the latest version of ISO 3166 - Codes for the representation of names of countries. Where the creator of the description is an international organisation, give the organisational identifier in place of the country code.'))
          ->label(__('Description identifier'))
          ->renderRow() ?>

        <?php echo render_field($form->institutionResponsibleIdentifier
          ->help(__('Record the full authorised form of name(s) of the agency(ies) responsible for creating, modifying or disseminating the description or, alternatively, record a code for the agency in accordance with the national or international agency code standard.'))
          ->label(__('Institution identifier')), $resource) ?>

        <?php echo render_field($form->rules
          ->help(__('Record the international, national and/or local rules or conventions followed in preparing the description.'))
          ->label(__('Rules or conventions')), $resource, array('class' => 'resizable')) ?>

        <?php echo $form->descriptionStatus
          ->label(__('Status'))
          ->help(__('Record the current status of the description, indicating whether it is a draft, finalized and/or revised or deleted.'))
          ->renderRow() ?>

        <?php echo $form->descriptionDetail
          ->help(__('Record whether the description consists of a minimal, partial or full level of detail in accordance with relevant international and/or national guidelines and/or rules.'))
          ->label(__('Level of detail'))
          ->renderRow() ?>

        <?php echo render_field($form->revisionHistory
          ->help(__('Record the date(s) the entry was prepared and/or revised.'))
          ->label(__('Dates of creation, revision and deletion')), $resource, array('class' => 'resizable')) ?>

        <?php echo $form->languageOfDescription
          ->help(__('Indicate the language(s) used to create the description of the archival material.'))
          ->label(__('Language'))
          ->renderRow(array('class' => 'form-autocomplete')) ?>

        <?php echo $form->scriptOfDescription
          ->help(__('Indicate the script(s) used to create the description of the archival material.'))
          ->label(__('Script'))
          ->renderRow(array('class' => 'form-autocomplete')) ?>

        <?php echo render_field($form->sources
          ->help(__('Record citations for any external sources used in the archival description (such as the Scope and Content, Custodial History, or Notes fields).'))
          ->label(__('Sources')), $resource, array('class' => 'resizable')) ?>

      </fieldset> <!-- #descriptionControlArea -->

      <?php echo get_partial('informationobject/adminInfo', array('form' => $form, 'resource' => $resource)) ?>

    </div>

  <?php echo get_partial('informationobject/editActions', array('resource' => ($parent !== null ? $parent : $resource))) ?>

<?php end_slot() ?>
