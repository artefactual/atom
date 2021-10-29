<?php decorate_with('layout_2col.php'); ?>
<?php use_helper('Date'); ?>

<?php slot('sidebar'); ?>

  <?php include_component('repository', 'contextMenu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>
  <?php echo get_component('informationobject', 'descriptionHeader', ['resource' => $resource, 'title' => (string) $isad]); ?>

  <?php if (isset($sf_request->source)) { ?>
    <div class="alert alert-info" role="alert">
      <?php echo __('This is a duplicate of record %1%', ['%1%' => $sourceInformationObjectLabel]); ?>
    </div>
  <?php } ?>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'informationobject', 'action' => 'edit']), ['id' => 'editForm']); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for(['module' => 'informationobject', 'action' => 'add']), ['id' => 'editForm']); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="identity-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#identity-collapse" aria-expanded="false" aria-controls="identity-collapse">
            <?php echo __('Identity area'); ?>
          </button>
        </h2>
        <div id="identity-collapse" class="accordion-collapse collapse" aria-labelledby="identity-heading">
          <div class="accordion-body">
            <?php if ($isad->referenceCode) { ?>
              <div class="mb-3">
                <h3 class="fs-6 mb-2">
                  <?php echo __('Reference code'); ?>
                </h3>
                <span class="text-muted">
                  <?php echo render_value_inline($isad->referenceCode); ?>
                </span>
              </div>
            <?php } ?>

            <?php echo render_field($form->identifier
                ->help(__('Provide a specific local reference code, control number, or other unique identifier. The country and repository code will be automatically added from the linked repository record to form a full reference code. (ISAD 3.1.1)'))
                ->label(__('Identifier').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
            ); ?>

            <?php echo get_partial(
                'informationobject/identifierOptions',
                ['mask' => $mask] + $sf_data->getRaw('alternativeIdentifiersComponent')->getVarHolder()->getAll()
            ); ?>

            <?php echo render_field($form->title
                ->help(__('Provide either a formal title or a concise supplied title in accordance with the rules of multilevel description and national conventions. (ISAD 3.1.2)'))
                ->label(__('Title').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource); ?>

            <?php echo get_partial('event', $sf_data->getRaw('eventComponent')->getVarHolder()->getAll() + ['help' => __('"Identify and record the date(s) of the unit of description. Identify the type of date given. Record as a single date or a range of dates as appropriate.” (ISAD 3.1.3). The Date display field can be used to enter free-text date information, including typographical marks to express approximation, uncertainty, or qualification. Use the start and end fields to make the dates searchable. Do not use any qualifiers or typographical symbols to express uncertainty. Acceptable date formats: YYYYMMDD, YYYY-MM-DD, YYYY-MM, YYYY.')]); ?>

            <?php echo render_field($form->levelOfDescription
                ->help(__('Record the level of this unit of description. (ISAD 3.1.4)'))
                ->label(__('Level of description').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
            ); ?>

            <?php echo get_partial('informationobject/childLevels', ['help' => __('Identifier: Provide a specific local reference code, control number, or other unique identifier. Level of description: Record the level of this unit of description. Title: Provide either a formal title or a concise supplied title in accordance with the rules of multilevel description and national conventions.')]); ?>

            <?php echo render_field($form->extentAndMedium
                ->help(__('Record the extent of the unit of description by giving the number of physical or logical units in arabic numerals and the unit of measurement. Give the specific medium (media) of the unit of description. Separate multiple extents with a linebreak. (ISAD 3.1.5)'))
                ->label(__('Extent and medium').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="context-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#context-collapse" aria-expanded="false" aria-controls="context-collapse">
            <?php echo __('Context area'); ?>
          </button>
        </h2>
        <div id="context-collapse" class="accordion-collapse collapse" aria-labelledby="context-heading">
          <div class="accordion-body">
            <?php
                $extraInputs = '<input class="list" type="hidden" value="'
                    .url_for(['module' => 'actor', 'action' => 'autocomplete', 'showOnlyActors' => 'true'])
                    .'">';
                if (QubitAcl::check(QubitActor::getRoot(), 'create')) {
                    $extraInputs .= '<input class="add" type="hidden" data-link-existing="true" value="'
                        .url_for(['module' => 'actor', 'action' => 'add'])
                        .' #authorizedFormOfName">';
                }
                echo render_field(
                    $form->creators
                        ->label(
                            __('Name of creator(s)')
                            .' <span class="form-required" title="'
                            .__('This archival description, or one of its higher levels, requires at least one creator.')
                            .'">*</span>'
                        )
                        ->help(__(
                            'Record the name of the organization(s) or the individual(s) responsible for the creation,'
                            .' accumulation and maintenance of the records in the unit of description. Search for an'
                            .' existing name in the authority records by typing the first few characters of the name.'
                            .' Alternatively, type a new name to create and link to a new authority record. (ISAD 3.2.1)'
                        )),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>

            <?php echo render_field(
                $form->repository->help(__(
                    'Record the name of the organization which has custody of the archival material.'
                    .' Search for an existing name in the archival institution records by typing the'
                    .' first few characters of the name. Alternatively, type a new name to create and'
                    .' link to a new archival institution record.'
                )),
                null,
                [
                    'class' => 'form-autocomplete',
                    'extraInputs' => '<input class="list" type="hidden" value="'
                        .url_for($sf_data->getRaw('repoAcParams'))
                        .'"><input class="add" type="hidden" data-link-existing="true" value="'
                        .url_for(['module' => 'repository', 'action' => 'add'])
                        .' #authorizedFormOfName">',
                ]
            ); ?>

            <?php echo render_field($form->archivalHistory
                ->help(__('Record the successive transfers of ownership, responsibility and/or custody of the unit of description and indicate those actions, such as history of the arrangement, production of contemporary finding aids, re-use of the records for other purposes or software migrations, that have contributed to its present structure and arrangement. Give the dates of these actions, insofar as they can be ascertained. If the archival history is unknown, record that information. (ISAD 3.2.3)')), $resource); ?>

            <?php echo render_field($form->acquisition
                ->help(__('Record the source from which the unit of description was acquired and the date and/or method of acquisition if any or all of this information is not confidential. If the source is unknown, record that information. Optionally, add accession numbers or codes. (ISAD 3.2.4)'))
                ->label(__('Immediate source of acquisition or transfer')), $resource); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="content-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#content-collapse" aria-expanded="false" aria-controls="content-collapse">
            <?php echo __('Content and structure area'); ?>
          </button>
        </h2>
        <div id="content-collapse" class="accordion-collapse collapse" aria-labelledby="content-heading">
          <div class="accordion-body">
            <?php echo render_field($form->scopeAndContent
                ->help(__('Give a summary of the scope (such as, time periods, geography) and content, (such as documentary forms, subject matter, administrative processes) of the unit of description, appropriate to the level of description. (ISAD 3.3.1)')), $resource); ?>

            <?php echo render_field($form->appraisal
                ->help(__('Record appraisal, destruction and scheduling actions taken on or planned for the unit of description, especially if they may affect the interpretation of the material. (ISAD 3.3.2)'))
                ->label(__('Appraisal, destruction and scheduling')), $resource); ?>

            <?php echo render_field($form->accruals
                ->help(__('Indicate if accruals are expected. Where appropriate, give an estimate of their quantity and frequency. (ISAD 3.3.3)')), $resource); ?>

            <?php echo render_field($form->arrangement
                ->help(__('Specify the internal structure, order and/or the system of classification of the unit of description. Note how these have been treated by the archivist. For electronic records, record or reference information on system design. (ISAD 3.3.4)'))
                ->label(__('System of arrangement')), $resource); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="conditions-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#conditions-collapse" aria-expanded="false" aria-controls="conditions-collapse">
            <?php echo __('Conditions of access and use area'); ?>
          </button>
        </h2>
        <div id="conditions-collapse" class="accordion-collapse collapse" aria-labelledby="conditions-heading">
          <div class="accordion-body">
            <?php echo render_field($form->accessConditions
                ->help(__('Specify the law or legal status, contract, regulation or policy that affects access to the unit of description. Indicate the extent of the period of closure and the date at which the material will open when appropriate. (ISAD 3.4.1)'))
                ->label(__('Conditions governing access')), $resource); ?>

            <?php echo render_field($form->reproductionConditions
                ->help(__('Give information about conditions, such as copyright, governing the reproduction of the unit of description after access has been provided. If the existence of such conditions is unknown, record this. If there are no conditions, no statement is necessary. (ISAD 3.4.2)'))
                ->label(__('Conditions governing reproduction')), $resource); ?>

            <?php echo render_field(
                $form->language
                    ->help(__('Record the language(s) of the materials comprising the unit of description. (ISAD 3.4.3)'))
                    ->label(__('Languages of the material')),
                null,
                ['class' => 'form-autocomplete']
            ); ?>

            <?php echo render_field(
                $form->script
                    ->help(__('Record the script(s) of the materials comprising the unit of description. (ISAD 3.4.3)'))
                    ->label(__('Scripts of the material')),
                null,
                ['class' => 'form-autocomplete']
            ); ?>

            <?php echo render_field($form->languageNotes
                ->help(__('Note any distinctive alphabets, scripts, symbol systems or abbreviations employed. (ISAD 3.4.3)'))
                ->label(__('Language and script notes')), $isad); ?>

            <?php echo render_field($form->physicalCharacteristics
                ->help(__('Indicate any important physical conditions, such as preservation requirements, that affect the use of the unit of description. Note any software and/or hardware required to access the unit of description.'))
                ->label(__('Physical characteristics and technical requirements')), $resource); ?>

            <?php echo render_field($form->findingAids
                ->help(__('Give information about any finding aids that the repository or records creator may have that provide information relating to the context and contents of the unit of description. If appropriate, include information on where to obtain a copy. (ISAD 3.4.5)')), $resource); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="allied-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#allied-collapse" aria-expanded="false" aria-controls="allied-collapse">
            <?php echo __('Allied materials area'); ?>
          </button>
        </h2>
        <div id="allied-collapse" class="accordion-collapse collapse" aria-labelledby="allied-heading">
          <div class="accordion-body">
            <?php echo render_field($form->locationOfOriginals
                ->help(__('If the original of the unit of description is available (either in the institution or elsewhere) record its location, together with any significant control numbers. If the originals no longer exist, or their location is unknown, give that information. (ISAD 3.5.1)'))
                ->label(__('Existence and location of originals')), $resource); ?>

            <?php echo render_field($form->locationOfCopies
                ->help(__('If the copy of the unit of description is available (either in the institution or elsewhere) record its location, together with any significant control numbers. (ISAD 3.5.2)'))
                ->label(__('Existence and location of copies')), $resource); ?>

            <?php echo render_field($form->relatedUnitsOfDescription
                ->help(__('Record information about units of description in the same repository or elsewhere that are related by provenance or other association(s). Use appropriate introductory wording and explain the nature of the relationship . If the related unit of description is a finding aid, use the finding aids element of description (3.4.5) to make the reference to it. (ISAD 3.5.3)'))
                ->label(__('Related units of description')), $resource); ?>

            <?php
                $extraInputs = '<input class="list" type="hidden" value="'
                    .url_for(['module' => 'informationobject', 'action' => 'autocomplete'])
                    .'">';
                if (QubitAcl::check(QubitInformationObject::getRoot(), 'create')) {
                    $extraInputs .= '<input class="add" type="hidden" data-link-existing="true" value="'
                        .url_for(['module' => 'informationobject', 'action' => 'add'])
                        .' #title">';
                }
                echo render_field(
                    $form->relatedMaterialDescriptions->label(__('Related descriptions'))->help(__(
                        'To create a relationship between this description and another description held in AtoM,'
                        .' begin typing the name of the related description and select it from the autocomplete'
                        .' drop-down menu when it appears below. Multiple relationships can be created.'
                    )),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>

            <?php echo get_partial('object/notes', $sf_data->getRaw('publicationNotesComponent')->getVarHolder()->getAll()); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="notes-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#notes-collapse" aria-expanded="false" aria-controls="notes-collapse">
            <?php echo __('Notes area'); ?>
          </button>
        </h2>
        <div id="notes-collapse" class="accordion-collapse collapse" aria-labelledby="notes-heading">
          <div class="accordion-body">
            <?php echo get_partial('object/notes', $sf_data->getRaw('notesComponent')->getVarHolder()->getAll()); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="access-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#access-collapse" aria-expanded="false" aria-controls="access-collapse">
            <?php echo __('Access points'); ?>
          </button>
        </h2>
        <div id="access-collapse" class="accordion-collapse collapse" aria-labelledby="access-heading">
          <div class="accordion-body">
            <?php
                $taxonomy = QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID);
                $taxonomyUrl = url_for([$taxonomy, 'module' => 'taxonomy']);
                $extraInputs = '<input class="list" type="hidden" value="'
                    .url_for(['module' => 'term', 'action' => 'autocomplete', 'taxonomy' => $taxonomyUrl])
                    .'">';
                if (QubitAcl::check($taxonomy, 'createTerm')) {
                    $extraInputs .= '<input class="add" type="hidden" data-link-existing="true" value="'
                        .url_for(['module' => 'term', 'action' => 'add', 'taxonomy' => $taxonomyUrl])
                        .' #name">';
                }
                echo render_field(
                    $form->subjectAccessPoints->label(__('Subject access points')),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>

            <?php
                $taxonomy = QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID);
                $taxonomyUrl = url_for([$taxonomy, 'module' => 'taxonomy']);
                $extraInputs = '<input class="list" type="hidden" value="'
                    .url_for(['module' => 'term', 'action' => 'autocomplete', 'taxonomy' => $taxonomyUrl])
                    .'">';
                if (QubitAcl::check($taxonomy, 'createTerm')) {
                    $extraInputs .= '<input class="add" type="hidden" data-link-existing="true" value="'
                        .url_for(['module' => 'term', 'action' => 'add', 'taxonomy' => $taxonomyUrl])
                        .' #name">';
                }
                echo render_field(
                    $form->placeAccessPoints->label(__('Place access points')),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>

            <?php
                $taxonomy = QubitTaxonomy::getById(QubitTaxonomy::GENRE_ID);
                $taxonomyUrl = url_for([$taxonomy, 'module' => 'taxonomy']);
                $extraInputs = '<input class="list" type="hidden" value="'
                    .url_for(['module' => 'term', 'action' => 'autocomplete', 'taxonomy' => $taxonomyUrl])
                    .'">';
                if (QubitAcl::check($taxonomy, 'createTerm')) {
                    $extraInputs .= '<input class="add" type="hidden" data-link-existing="true" value="'
                        .url_for(['module' => 'term', 'action' => 'add', 'taxonomy' => $taxonomyUrl])
                        .' #name">';
                }
                echo render_field(
                    $form->genreAccessPoints->label(__('Genre access points')),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>

            <?php
                $extraInputs = '<input class="list" type="hidden" value="'
                    .url_for(['module' => 'actor', 'action' => 'autocomplete', 'showOnlyActors' => 'true'])
                    .'">';
                if (QubitAcl::check(QubitActor::getRoot(), 'create')) {
                    $extraInputs .= '<input class="add" type="hidden" data-link-existing="true" value="'
                        .url_for(['module' => 'actor', 'action' => 'add'])
                        .' #authorizedFormOfName">';
                }
                echo render_field(
                    $form->nameAccessPoints->label(__('Name access points (subjects)')),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="description-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#description-collapse" aria-expanded="false" aria-controls="description-collapse">
            <?php echo __('Description control area'); ?>
          </button>
        </h2>
        <div id="description-collapse" class="accordion-collapse collapse" aria-labelledby="description-heading">
          <div class="accordion-body">
            <?php echo render_field($form->descriptionIdentifier
                ->help(__('Record a unique description identifier in accordance with local and/or national conventions. If the description is to be used internationally, record the code of the country in which the description was created in accordance with the latest version of ISO 3166 - Codes for the representation of names of countries. Where the creator of the description is an international organisation, give the organisational identifier in place of the country code.'))
                ->label(__('Description identifier'))
            ); ?>

            <?php echo render_field($form->institutionResponsibleIdentifier
                ->help(__('Record the full authorised form of name(s) of the agency(ies) responsible for creating, modifying or disseminating the description or, alternatively, record a code for the agency in accordance with the national or international agency code standard.'))
                ->label(__('Institution identifier')), $resource); ?>

            <?php echo render_field($form->rules
                ->help(__('Record the international, national and/or local rules or conventions followed in preparing the description. (ISAD 3.7.2)'))
                ->label(__('Rules or conventions')), $resource); ?>

            <?php echo render_field($form->descriptionStatus
                ->label(__('Status'))
                ->help(__('Record the current status of the description, indicating whether it is a draft, finalized and/or revised or deleted.'))
            ); ?>

            <?php echo render_field($form->descriptionDetail
                ->help(__('Record whether the description consists of a minimal, partial or full level of detail in accordance with relevant international and/or national guidelines and/or rules.'))
                ->label(__('Level of detail'))
            ); ?>

            <?php echo render_field($form->revisionHistory
                ->help(__('Record the date(s) the entry was prepared and/or revised.'))
                ->label(__('Dates of creation, revision and deletion')), $resource); ?>

            <?php echo render_field(
                $form->languageOfDescription
                    ->help(__('Indicate the language(s) used to create the description of the archival material.'))
                    ->label(__('Language(s)')),
                null,
                ['class' => 'form-autocomplete']
            ); ?>

            <?php echo render_field(
                $form->scriptOfDescription
                    ->help(__('Indicate the script(s) used to create the description of the archival material.'))
                    ->label(__('Script(s)')),
                null,
                ['class' => 'form-autocomplete']
            ); ?>

            <?php echo render_field($form->sources
                ->help(__('Record citations for any external sources used in the archival description (such as the Scope and Content, Archival History, or Notes fields).'))
                ->label(__('Sources')), $resource); ?>

            <?php echo get_partial('object/notes', $sf_data->getRaw('archivistsNotesComponent')->getVarHolder()->getAll()); ?>
          </div>
        </div>
      </div>
      <?php echo get_partial('informationobject/adminInfo', ['form' => $form, 'resource' => $resource]); ?>
    </div>

    <?php echo get_partial('informationobject/editActions', ['resource' => (null !== $parent ? $parent : $resource)]); ?>

  </form>

<?php end_slot(); ?>
