<?php decorate_with('layout_2col.php') ?>
<?php use_helper('Date') ?>

<?php slot('sidebar') ?>

  <?php include_component('repository', 'contextMenu') ?>

<?php end_slot() ?>

<?php slot('title') ?>
  <h1><?php echo render_title($isad) ?></h1>

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

      <fieldset class="collapsible collapsed" id="identityArea">

        <legend><?php echo __('Identity area') ?></legend>

        <?php echo render_show(__('Reference code'), $isad->referenceCode) ?>

        <?php echo $form->identifier
          ->help(__('Provide a specific local reference code, control number, or other unique identifier. The country and repository code will be automatically added from the linked repository record to form a full reference code. (ISAD 3.1.1)'))
          ->label(__('Identifier').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
          ->renderRow() ?>

        <?php echo get_partial('informationobject/identifierOptions', array('mask' => $mask)) ?>
        <?php echo get_partial('informationobject/alternativeIdentifiers', $sf_data->getRaw('alternativeIdentifiersComponent')->getVarHolder()->getAll()) ?>

        <?php echo render_field($form->title
          ->help(__('Provide either a formal title or a concise supplied title in accordance with the rules of multilevel description and national conventions. (ISAD 3.1.2)'))
          ->label(__('Title').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource) ?>

        <?php echo get_partial('event', $sf_data->getRaw('eventComponent')->getVarHolder()->getAll() + array('help' => __('"Identify and record the date(s) of the unit of description. Identify the type of date given. Record as a single date or a range of dates as appropriate.” (ISAD 3.1.3). The Date display field can be used to enter free-text date information, including typographical marks to express approximation, uncertainty, or qualification. Use the start and end fields to make the dates searchable. Do not use any qualifiers or typographical symbols to express uncertainty. Acceptable date formats: YYYYMMDD, YYYY-MM-DD, YYYY-MM, YYYY.'))) ?>

        <?php echo $form->levelOfDescription
          ->help(__('Record the level of this unit of description. (ISAD 3.1.4)'))
          ->label(__('Level of description').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
          ->renderRow() ?>

        <?php echo get_partial('informationobject/childLevels', array('help' => __('Identifier: Provide a specific local reference code, control number, or other unique identifier. Level of description: Record the level of this unit of description. Title: Provide either a formal title or a concise supplied title in accordance with the rules of multilevel description and national conventions.'))) ?>

        <?php echo render_field($form->extentAndMedium
          ->help(__('Record the extent of the unit of description by giving the number of physical or logical units in arabic numerals and the unit of measurement. Give the specific medium (media) of the unit of description. Separate multiple extents with a linebreak. (ISAD 3.1.5)'))
          ->label(__('Extent and medium').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource, array('class' => 'resizable')) ?>

      </fieldset> <!-- /#identityArea -->

      <fieldset class="collapsible collapsed" id="contextArea">

        <legend><?php echo __('Context area') ?></legend>

        <div class="form-item">
          <?php echo $form->creators
            ->label(__('Name of creator(s)').' <span class="form-required" title="'.__('This archival description, or one of its higher levels, requires at least one creator.').'">*</span>')
            ->renderLabel() ?>
          <?php echo $form->creators->render(array('class' => 'form-autocomplete')) ?>
          <?php echo $form->creators
            ->help(__('Record the name of the organization(s) or the individual(s) responsible for the creation, accumulation and maintenance of the records in the unit of description. Search for an existing name in the authority records by typing the first few characters of the name. Alternatively, type a new name to create and link to a new authority record. (ISAD 3.2.1)'))
            ->renderHelp() ?>
          <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'actor', 'action' => 'add')) ?> #authorizedFormOfName"/>
          <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'actor', 'action' => 'autocomplete')) ?>"/>
        </div>

        <div class="form-item">
          <?php echo $form->repository->renderLabel() ?>
          <?php echo $form->repository->render(array('class' => 'form-autocomplete')) ?>
          <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'repository', 'action' => 'add')) ?> #authorizedFormOfName"/>
          <input class="list" type="hidden" value="<?php echo url_for($sf_data->getRaw('repoAcParams')) ?>"/>
          <?php echo $form->repository
            ->help(__('Record the name of the organization which has custody of the archival material. Search for an existing name in the archival institution records by typing the first few characters of the name. Alternatively, type a new name to create and link to a new archival institution record.'))
            ->renderHelp(); ?>
        </div>

        <?php echo render_field($form->archivalHistory
          ->help(__('Record the successive transfers of ownership, responsibility and/or custody of the unit of description and indicate those actions, such as history of the arrangement, production of contemporary finding aids, re-use of the records for other purposes or software migrations, that have contributed to its present structure and arrangement. Give the dates of these actions, insofar as they can be ascertained. If the archival history is unknown, record that information. (ISAD 3.2.3)')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->acquisition
          ->help(__('Record the source from which the unit of description was acquired and the date and/or method of acquisition if any or all of this information is not confidential. If the source is unknown, record that information. Optionally, add accession numbers or codes. (ISAD 3.2.4)'))
          ->label(__('Immediate source of acquisition or transfer')), $resource, array('class' => 'resizable')) ?>

      </fieldset> <!-- /#contextArea -->

      <fieldset class="collapsible collapsed" id="contentAndStructureArea">

        <legend><?php echo __('Content and structure area') ?></legend>

        <?php echo render_field($form->scopeAndContent
          ->help(__('Give a summary of the scope (such as, time periods, geography) and content, (such as documentary forms, subject matter, administrative processes) of the unit of description, appropriate to the level of description. (ISAD 3.3.1)')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->appraisal
          ->help(__('Record appraisal, destruction and scheduling actions taken on or planned for the unit of description, especially if they may affect the interpretation of the material. (ISAD 3.3.2)'))
          ->label(__('Appraisal, destruction and scheduling')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->accruals
          ->help(__('Indicate if accruals are expected. Where appropriate, give an estimate of their quantity and frequency. (ISAD 3.3.3)')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->arrangement
          ->help(__('Specify the internal structure, order and/or the system of classification of the unit of description. Note how these have been treated by the archivist. For electronic records, record or reference information on system design. (ISAD 3.3.4)'))
          ->label(__('System of arrangement')), $resource, array('class' => 'resizable')) ?>

      </fieldset> <!-- /#contentAndStructureArea -->

      <fieldset class="collapsible collapsed" id="conditionsOfAccessAndUseArea">

        <legend><?php echo __('Conditions of access and use area') ?></legend>

        <?php echo render_field($form->accessConditions
          ->help(__('Specify the law or legal status, contract, regulation or policy that affects access to the unit of description. Indicate the extent of the period of closure and the date at which the material will open when appropriate. (ISAD 3.4.1)'))
          ->label(__('Conditions governing access')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->reproductionConditions
          ->help(__('Give information about conditions, such as copyright, governing the reproduction of the unit of description after access has been provided. If the existence of such conditions is unknown, record this. If there are no conditions, no statement is necessary. (ISAD 3.4.2)'))
          ->label(__('Conditions governing reproduction')), $resource, array('class' => 'resizable')) ?>

        <?php echo $form->language
          ->help(__('Record the language(s) of the materials comprising the unit of description. (ISAD 3.4.3)'))
          ->label(__('Language of material'))
          ->renderRow(array('class' => 'form-autocomplete')) ?>

        <?php echo $form->script
          ->help(__('Record the script(s) of the materials comprising the unit of description. (ISAD 3.4.3)'))
          ->label(__('Script of material'))
          ->renderRow(array('class' => 'form-autocomplete')) ?>

        <?php echo render_field($form->languageNotes
          ->help(__('Note any distinctive alphabets, scripts, symbol systems or abbreviations employed. (ISAD 3.4.3)'))
          ->label(__('Language and script notes')), $isad, array('class' => 'resizable')) ?>

        <?php echo render_field($form->physicalCharacteristics
          ->help(__('Indicate any important physical conditions, such as preservation requirements, that affect the use of the unit of description. Note any software and/or hardware required to access the unit of description.'))
          ->label(__('Physical characteristics and technical requirements')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->findingAids
          ->help(__('Give information about any finding aids that the repository or records creator may have that provide information relating to the context and contents of the unit of description. If appropriate, include information on where to obtain a copy. (ISAD 3.4.5)')), $resource, array('class' => 'resizable')) ?>

      </fieldset> <!-- /#conditionsOfAccessAndUseArea -->

      <fieldset class="collapsible collapsed" id="alliedMaterialsArea">

        <legend><?php echo __('Allied materials area') ?></legend>

        <?php echo render_field($form->locationOfOriginals
          ->help(__('If the original of the unit of description is available (either in the institution or elsewhere) record its location, together with any significant control numbers. If the originals no longer exist, or their location is unknown, give that information. (ISAD 3.5.1)'))
          ->label(__('Existence and location of originals')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->locationOfCopies
          ->help(__('If the copy of the unit of description is available (either in the institution or elsewhere) record its location, together with any significant control numbers. (ISAD 3.5.2)'))
          ->label(__('Existence and location of copies')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->relatedUnitsOfDescription
          ->help(__('Record information about units of description in the same repository or elsewhere that are related by provenance or other association(s). Use appropriate introductory wording and explain the nature of the relationship . If the related unit of description is a finding aid, use the finding aids element of description (3.4.5) to make the reference to it. (ISAD 3.5.3)'))
          ->label(__('Related units of description')), $resource, array('class' => 'resizable')) ?>

        <div class="form-item">
          <?php echo $form->relatedMaterialDescriptions
            ->label(__('Related descriptions'))
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

        <?php echo get_partial('informationobject/notes', $sf_data->getRaw('publicationNotesComponent')->getVarHolder()->getAll()) ?>

      </fieldset> <!-- /#alliedMaterialsArea -->

      <fieldset class="collapsible collapsed" id="notesArea">

        <legend><?php echo __('Notes area') ?></legend>

        <?php echo get_partial('informationobject/notes', $sf_data->getRaw('notesComponent')->getVarHolder()->getAll()) ?>

      </fieldset> <!-- /#notesArea -->

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
        </div>

        <div class="form-item">
          <?php echo $form->genreAccessPoints
            ->label(__('Genre access points'))
            ->renderLabel() ?>
          <?php echo $form->genreAccessPoints->render(array('class' => 'form-autocomplete')) ?>
          <?php if (QubitAcl::check(QubitActor::getRoot(), 'create')): ?>
            <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::GENRE_ID), 'module' => 'taxonomy')))) ?> #name"/>
          <?php endif; ?>
          <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::GENRE_ID), 'module' => 'taxonomy')))) ?>"/>
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
        </div>

      </fieldset>

      <fieldset class="collapsible collapsed" id="descriptionControlArea">

        <legend><?php echo __('Description control area') ?></legend>

        <?php echo $form->descriptionIdentifier
          ->help(__('Record a unique description identifier in accordance with local and/or national conventions. If the description is to be used internationally, record the code of the country in which the description was created in accordance with the latest version of ISO 3166 - Codes for the representation of names of countries. Where the creator of the description is an international organisation, give the organisational identifier in place of the country code.'))
          ->label(__('Description identifier'))
          ->renderRow() ?>

        <?php echo render_field($form->institutionResponsibleIdentifier
          ->help(__('Record the full authorised form of name(s) of the agency(ies) responsible for creating, modifying or disseminating the description or, alternatively, record a code for the agency in accordance with the national or international agency code standard.'))
          ->label(__('Institution identifier')), $resource) ?>

        <?php echo render_field($form->rules
          ->help(__('Record the international, national and/or local rules or conventions followed in preparing the description. (ISAD 3.7.2)'))
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
          ->label(__('Language(s)'))->renderRow(array('class' => 'form-autocomplete')) ?>

        <?php echo $form->scriptOfDescription
          ->help(__('Indicate the script(s) used to create the description of the archival material.'))
          ->label(__('Script(s)'))->renderRow(array('class' => 'form-autocomplete')) ?>

        <?php echo render_field($form->sources
          ->help(__('Record citations for any external sources used in the archival description (such as the Scope and Content, Archival History, or Notes fields).'))
          ->label(__('Sources')), $resource, array('class' => 'resizable')) ?>

        <?php echo get_partial('informationobject/notes', $sf_data->getRaw('archivistsNotesComponent')->getVarHolder()->getAll()) ?>

      </fieldset> <!-- /#descriptionControlArea -->

      <?php echo get_partial('informationobject/adminInfo', array('form' => $form, 'resource' => $resource)) ?>

    </div>

    <?php echo get_partial('informationobject/editActions', array('resource' => ($parent !== null ? $parent : $resource))) ?>

  </form>

<?php end_slot() ?>
