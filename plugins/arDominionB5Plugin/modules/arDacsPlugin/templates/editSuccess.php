<?php decorate_with('layout_2col.php'); ?>
<?php use_helper('Date'); ?>

<?php slot('sidebar'); ?>

  <?php include_component('repository', 'contextMenu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo render_title($dacs); ?></h1>

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
            <?php echo __('Identity elements'); ?>
          </button>
        </h2>
        <div id="identity-collapse" class="accordion-collapse collapse" aria-labelledby="identity-heading">
          <div class="accordion-body">
            <?php echo render_field($form->identifier
                ->help(__('At the highest level of a multilevel description or in a single level description, provide a unique identifier for the materials being described in accordance with the institution’s administrative control system. Optionally, devise unique identifiers at lower levels of a multilevel description. (DACS 2.1.3) The country and repository code will be automatically added from the linked repository record to form a full reference code.'))
                ->label(__('Identifier').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
            ); ?>

            <?php echo get_partial(
                'informationobject/identifierOptions',
                ['mask' => $mask] + $sf_data->getRaw('alternativeIdentifiersComponent')->getVarHolder()->getAll()
            ); ?>

            <?php echo render_field(
                $form->repository->label(__('Name and location of repository'))->help(__(
                    'Explicitly state the name of the repository, including any parent bodies'
                    .' (DACS 2.2.2). Search for an existing name in the archival institution'
                    .' records by typing the first few characters of the name. Alternatively,'
                    .' type a new name to create and link to a new archival institution record.'
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

            <?php echo render_field($form->levelOfDescription
                ->help(__('Select a level of description from the drop-down menu. Follow any relevant local or institutional guidelines in selecting the proper level of description. See DACS (2013) Chapter 1 for further guidance.'))
                ->label(__('Levels of description'))
            ); ?>

            <?php echo render_field($form->title
                ->help(__('In the absence of a meaningful formal title, compose a brief title that uniquely identifies the material, normally consisting of a name segment, a term indicating the nature of the unit being described, and optionally a topical segment. Do not enclose devised titles in square brackets. (DACS 2.3.3)'))
                ->label(__('Title').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource); ?>

            <?php echo get_partial('sfIsadPlugin/event', $sf_data->getRaw('eventComponent')->getVarHolder()->getAll() + ['help' => __('Record dates of creation, record-keeping activity, publication, or broadcast as appropriate to the materials being described. (DACS 2.4.3) The Date display field can be used to enter free-text date information, including typographical marks to express approximation, uncertainty, or qualification. Use the start and end fields to make the dates searchable. Do not use any qualifiers or typographical symbols. Acceptable date formats: YYYYMMDD, YYYY-MM-DD, YYYY-MM, YYYY.')]); ?>

            <?php echo render_field($form->extentAndMedium
                ->help(__('Record the quantity of the material in terms of its physical extent as linear or cubic feet, number of items, or number of containers or carriers. (DACS 2.5.4). Optionally, record the quantity in terms of material type(s) (DACS 2.5.5), and/or qualify the statement of physical extent to hightlight the existence of material types that re important (DACS 2.5.6).'))
                ->label(__('Extent').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource); ?>

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
                            'Record the name(s) of the creator(s) identified in the name element'
                            .' in the devised title of the materials using standardized vocabularies'
                            .' or with rules for formulating standardized names (DACS 2.6.4). Search'
                            .' for an existing name in the authority records by typing the first few'
                            .' characters of the name. Alternatively, type a new name to create and'
                            .' link to a new authority record.'
                        )),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>

            <?php echo get_partial('informationobject/childLevels', ['help' => __('<strong>Identifier</strong><br />Provide a unique identifier for the materials being described in accordance with the institution’s administrative control system.<br /><strong>Level of description</strong><br />Record the level of this unit of description.<br /><strong>Title</strong><br />In the absence of a meaningful formal title, compose a brief title that uniquely identifies the material.<br /><strong>Date</strong><br />Record a date of creation.')]); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="content-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#content-collapse" aria-expanded="false" aria-controls="content-collapse">
            <?php echo __('Content and structure elements'); ?>
          </button>
        </h2>
        <div id="content-collapse" class="accordion-collapse collapse" aria-labelledby="content-heading">
          <div class="accordion-body">
            <?php echo render_field($form->scopeAndContent
                ->help(__('Record information about the nature of the materials and activities reflected in the unit being described to enable users to judge its potential relevance, including information about functions, activities, transations, and processes; documentary form(s) or intellectual characteristics; content dates; geographic areas and places; subject matter; completeness of the materials; or any other information that assists the user in evaluating the relevance of the materials. (DACS 3.1)'))
                ->label(__('Scope and content').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource); ?>

            <?php echo render_field($form->arrangement
                ->help(__('Describe the current arrangement of the material in terms of the various aggregations within it and their relationships. (DACS 3.2.3)'))
                ->label(__('System of arrangement')), $resource); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="conditions-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#conditions-collapse" aria-expanded="false" aria-controls="conditions-collapse">
            <?php echo __('Conditions of access and use elements'); ?>
          </button>
        </h2>
        <div id="conditions-collapse" class="accordion-collapse collapse" aria-labelledby="conditions-heading">
          <div class="accordion-body">
            <?php echo render_field($form->accessConditions
                ->help(__('Give information about any restrictions on access to the unit being described (or parts thereof) as a result of the nature of the information therein or statutory/contractual requirements. As appropriate, specify the details of the restriction. If there are no restrictions, state that fact. (DACS 4.1.5)'))
                ->label(__('Conditions governing access')), $resource); ?>

            <?php echo render_field($form->physicalCharacteristics
                ->help(__('Provide information about the physical characteristics or condition of the unit being described that limit access to it or restrict its use. (DACS 4.2.5)'))
                ->label(__('Physical access')), $resource); ?>

            <?php echo render_field($form->technicalAccess
                ->help(__('Provide information about any special equipment required to view or access the unit being described, if it is not clear from the Extent element. (DACS 4.3.5)'))
                ->label(__('Technical access')), $dacs); ?>

            <?php echo render_field($form->reproductionConditions
                ->help(__('Give information about copyright status and any other conditions governing the reproduction, publication, and further use (e.g., display, public screening, broadcast, etc.) of the unit being described after access has been provided. (DACS 4.4.5)'))
                ->label(__('Conditions governing reproduction and use')), $resource); ?>

            <?php echo render_field(
                $form->language
                    ->help(__('Record the language(s) of the materials being described. (DACS 4.5.2)'))
                    ->label(__('Languages of the material')),
                null,
                ['class' => 'form-autocomplete']
            ); ?>

            <?php echo render_field(
                $form->script
                    ->help(__('Record the scripts(s) of the materials being described.'))
                    ->label(__('Scripts of the material')),
                null,
                ['class' => 'form-autocomplete']
            ); ?>

            <?php echo render_field($form->languageNotes
                ->help(__('Record information about any distinctive alphabets, scripts, symbol systems, or abbreviations employed (DACS 4.5.3). If there is no language content, record “no linguistic content.” (DACS 4.5.4)'))
                ->label(__('Language and script notes')), $dacs); ?>

            <?php echo render_field($form->findingAids
                ->help(__('Record information about any existing finding aids that provide information relating to the context and contents of the unit being described... including any relevant information about its location or availability, and any other information necessary to assist the user in evaluating its usefulness. Include finding aids prepared by the creator (e.g., registers, indexes, etc.) that are part of the unit being described. (DACS 4.6.2)')), $resource); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="acquisition-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acquisition-collapse" aria-expanded="false" aria-controls="acquisition-collapse">
            <?php echo __('Acquisition and appraisal elements'); ?>
          </button>
        </h2>
        <div id="acquisition-collapse" class="accordion-collapse collapse" aria-labelledby="acquisition-heading">
          <div class="accordion-body">
            <?php echo render_field($form->archivalHistory
                ->label(__('Custodial history'))
                ->help(__('Record the successive transfers of ownership, responsibility, or custody or control of the unit being described from the time it left the possession of the creator until its acquisition by the repository, along with the dates thereof, insofar as this information can be ascertained and is significant to the user’s understanding of the authenticity. (DACS 5.1.3)')), $resource); ?>

            <?php echo render_field($form->acquisition
                ->help(__('Record the source(s) from which the materials being described were acquired, the date(s) of acquisition, and the method of acquisition, if this information is not confidential. (DACS 5.2.3)'))
                ->label(__('Immediate source of acquisition or transfer')), $resource); ?>

            <?php echo render_field($form->appraisal
                ->help(__('Where the destruction or retention of archival materials has a bearing on the interpretation and use of the unit being described, provide information about the materials destroyed or retained and provide the reason(s) for the appraisal decision(s), where known. (DACS 5.3.4)'))
                ->label(__('Appraisal, destruction and scheduling information')), $resource); ?>

            <?php echo render_field($form->accruals
                ->help(__('If known, indicate whether or not further accruals are expected. When appropriate, indicate frequency and volume. (DACS 5.4.2)')), $resource); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="related-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#related-collapse" aria-expanded="false" aria-controls="related-collapse">
            <?php echo __('Related materials elements'); ?>
          </button>
        </h2>
        <div id="related-collapse" class="accordion-collapse collapse" aria-labelledby="related-heading">
          <div class="accordion-body">
            <?php echo render_field($form->locationOfOriginals
                ->help(__('If the materials being described are reproductions and the originals are located elsewhere, give the location of the originals (DACS 6.1.4). Record any identifying numbers that may help in locating the originals in the cited location (DACS 6.1.6).'))
                ->label(__('Existence and location of originals')), $resource); ?>

            <?php echo render_field($form->locationOfCopies
                ->help(__('If a copy of all or part of the material being described is available, in addition to the originals, record information about the medium and location of the copy, any identifying numbers, and any conditions on the use or availability of the copy. If a copy of only a part of the unit being described is available, indicate which part. If the materials being described are available via remote access (electronically or otherwise), provide the relevant information needed to access them. (DACS 6.2.3)'))
                ->label(__('Existence and location of copies')), $resource); ?>

            <?php echo render_field($form->relatedUnitsOfDescription
                ->help(__('If there are materials that have a direct and significant connection to those being described by reason of closely shared responsibility or sphere of activity, provide the title, location, and, optionally, the reference number(s) of the related materials and their relationship with the materials being described. (DACS 6.3.5)'))
                ->label(__('Related archival materials')), $resource); ?>

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
            <?php echo __('Notes element'); ?>
          </button>
        </h2>
        <div id="notes-collapse" class="accordion-collapse collapse" aria-labelledby="notes-heading">
          <div class="accordion-body">
            <?php echo get_partial('object/notes', $sf_data->getRaw('notesComponent')->getVarHolder()->getAll()); ?>
            <?php echo get_partial('object/notes', $sf_data->getRaw('specializedNotesComponent')->getVarHolder()->getAll()); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="description-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#description-collapse" aria-expanded="false" aria-controls="description-collapse">
            <?php echo __('Description control element'); ?>
          </button>
        </h2>
        <div id="description-collapse" class="accordion-collapse collapse" aria-labelledby="description-heading">
          <div class="accordion-body">
            <?php echo render_field($form->sources
                ->help(__('Record relevant information about sources consulted in establishing or revising the description. (DACS 8.1.3)'))
                ->label(__('Sources used')), $dacs); ?>

            <?php echo render_field($form->rules
                ->help(__('Record the international, national or local rules or conventions followed in preparing the description. (DACS 8.1.4)'))
                ->label(__('Rules or conventions')), $resource); ?>

            <!-- TODO: Make $archivistsNotesComponent to include ISAD 3.7.3 Date(s) of description as the first note and editable -->

            <?php echo get_partial('object/notes', $sf_data->getRaw('archivistsNotesComponent')->getVarHolder()->getAll()); ?>
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
      <?php echo get_partial('informationobject/adminInfo', ['form' => $form, 'resource' => $resource]); ?>
    </div>

    <?php echo get_partial('informationobject/editActions', ['resource' => (null !== $parent ? $parent : $resource)]); ?>

  </form>

<?php end_slot(); ?>
