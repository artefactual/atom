<?php decorate_with('layout_1col.php'); ?>
<?php use_helper('Date'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo __('Edit %1% - ISAAR', ['%1%' => sfConfig::get('app_ui_label_actor')]); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php echo render_title($resource); ?>
    </span>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'actor', 'action' => 'edit']), ['id' => 'editForm']); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for(['module' => 'actor', 'action' => 'add']), ['id' => 'editForm']); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <?php if (!$form->isValid() && $form->hasErrors()) { ?>
      <div class="alert alert-danger" role="alert">
        <ul class="<?php echo render_b5_show_list_css_classes(); ?>">
          <?php foreach ($form->getErrorSchema() as $error) { ?>
            <?php $error = sfOutputEscaper::unescape($error); ?>
            <li><?php echo $error->getMessage(); ?></li>
          <?php } ?>
        </ul>
      </div>
    <?php } ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="identity-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#identity-collapse" aria-expanded="false" aria-controls="identity-collapse">
            <?php echo __('Identity area'); ?>
          </button>
        </h2>
        <div id="identity-collapse" class="accordion-collapse collapse" aria-labelledby="identity-heading">
          <div class="accordion-body">
            <?php echo render_field($form->entityType
                ->help(__('"Specify the type of entity that is being described in this authority record." (ISAAR 5.1.1) Select Corporate body, Family or Person from the drop-down menu.'))
                ->label(__('Type of entity').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
            ); ?>

            <?php echo render_field($form->authorizedFormOfName
                ->help(__('"Record the standardized form of name for the entity being described in accordance with any relevant national or international conventions or rules applied by the agency that created the authority record. Use dates, place, jurisdiction, occupation, epithet and other qualifiers as appropriate to distinguish the authorized form of name from those of other entities with similar names." (ISAAR 5.1.2)'))
                ->label(__('Authorized form of name').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource); ?>

            <?php echo render_field($form->parallelName
                ->help(__('"Purpose: To indicate the various forms in which the Authorized form of name occurs in other languages or script form(s). Rule: record the parallel form(s) of name in accordance with any relevant national or international conventions or rules applied by the agency that created the authority record, including any necessary sub elements and/or qualifiers required by those conventions or rules." (ISAAR 5.1.3)'))
                ->label(__('Parallel form(s) of name'))
            ); ?>

            <?php echo render_field($form->standardizedName
                ->help(__('"Record the standardized form of name for the entity being described in accordance with other conventions or rules. Specify the rules and/or if appropriate the name of the agency by which these standardized forms of name have been constructed." (ISAAR 5.1.4)'))
                ->label(__('Standardized form(s) of name according to other rules'))
            ); ?>

            <?php echo render_field($form->otherName
                ->help(__('The purpose of this field is to "indicate any other name(s) for the corporate body, person or family not used elsewhere in the Identity Area." Examples are acronyms, previous names, pseudonyms, maiden names and titles of nobility or honour. (ISAAR 5.1.5)'))
                ->label(__('Other form(s) of name'))
            ); ?>

            <?php echo render_field($form->corporateBodyIdentifiers
                ->help(__('"Record where possible any official number or other identifier (e.g. a company registration number) for the corporate body and reference the jurisdiction and scheme under which it has been allocated." (ISAAR 5.1.6)'))
                ->label(__('Identifiers for corporate bodies')), $resource); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="description-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#description-collapse" aria-expanded="false" aria-controls="description-collapse">
            <?php echo __('Description area'); ?>
          </button>
        </h2>
        <div id="description-collapse" class="accordion-collapse collapse" aria-labelledby="description-heading">
          <div class="accordion-body">
            <?php echo render_field($form->datesOfExistence
                ->help(__('"Record the dates of existence of the entity being described. For corporate bodies include the date of establishment/foundation/enabling legislation and dissolution. For persons include the dates or approximate dates of birth and death or, when these dates are not known, floruit dates. Where parallel systems of dating are used, equivalences may be recorded according to relevant conventions or rules. Specify in the Rules and/or conventions element (5.4.3) the system(s) of dating used, e.g. ISO 8601." (ISAAR 5.2.1)'))
                ->label(__('Dates of existence').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource); ?>

            <?php echo render_field($form->history
                ->help(__('"Record in narrative form or as a chronology the main life events, activities, achievements and/or roles of the entity being described. This may include information on gender, nationality, family and religious or political affiliations. Wherever possible, supply dates as an integral component of the narrative description." (ISAAR 5.2.2)')), $resource); ?>

            <?php echo render_field($form->places
                ->help(__('"Purpose: to indicate the predominant places and/or jurisdictions where the corporate body, person or family was based, lived or resided or had some other connection. Rule: record the name of the predominant place(s)/jurisdiction(s), together with the nature and covering dates of the relationship with the entity." (ISAAR 5.2.3)')), $resource); ?>

            <?php echo render_field($form->legalStatus
                ->help(__('"Record the legal status and where appropriate the type of corporate body together with the covering dates when this status applied." (ISAAR 5.2.4)')), $resource); ?>

            <?php echo render_field($form->functions
                ->help(__('"Record the functions, occupations and activities performed by the entity being described, together with the covering dates when useful. If necessary, describe the nature of the function, occupation or activity." (ISAAR 5.2.5)'))
                ->label(__('Functions, occupations and activities')), $resource); ?>

            <?php echo render_field($form->mandates
                ->help(__('"Record any document, law, directive or charter which acts as a source of authority for the powers, functions and responsibilities of the entity being described, together with information on the jurisdiction(s) and covering dates when the mandate(s) applied or were changed." (ISAAR 5.2.6)'))
                ->label(__('Mandates/sources of authority')), $resource); ?>

            <?php echo render_field($form->internalStructures
                ->help(__('"Describe the internal structure of a corporate body and the dates of any changes to that structure that are significant to the understanding of the way that corporate body conducted its affairs (e.g. by means of dated organization charts). Describe the genealogy of a family (e.g. by means of a family tree) in a way that demonstrates the inter-relationships of its members with covering dates." (ISAAR 5.2.7)'))
                ->label(__('Internal structures/genealogy')), $resource); ?>

            <?php echo render_field($form->generalContext
                ->help(__('"Provide any significant information on the social, cultural, economic, political and/or historical context in which the entity being described operated." (ISAAR 5.2.8)')), $resource); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="relationships-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#relationships-collapse" aria-expanded="false" aria-controls="relationships-collapse">
            <?php echo __('Relationships area'); ?>
          </button>
        </h2>
        <div id="relationships-collapse" class="accordion-collapse collapse" aria-labelledby="relationships-heading">
          <div class="accordion-body">
            <?php echo get_partial('relatedAuthorityRecord', $sf_data->getRaw('relatedAuthorityRecordComponent')->getVarHolder()->getAll()); ?>
            <?php echo get_partial('event', $sf_data->getRaw('eventComponent')->getVarHolder()->getAll()); ?>
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

            <?php echo get_partial('actor/occupations', $sf_data->getRaw('occupationsComponent')->getVarHolder()->getAll()); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="control-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#control-collapse" aria-expanded="false" aria-controls="control-collapse">
            <?php echo __('Control area'); ?>
          </button>
        </h2>
        <div id="control-collapse" class="accordion-collapse collapse" aria-labelledby="control-heading">
          <div class="accordion-body">
            <?php echo render_field($form->descriptionIdentifier
                ->help(__('"Record a unique authority record identifier in accordance with local and/or national conventions. If the authority record is to be used internationally, record the country code of the country in which the authority record was created in accordance with the latest version of ISO 3166 Codes for the representation of names of countries. Where the creator of the authority record is an international organization, give the organizational identifier in place of the country code." (ISAAR 5.4.1)'))
                ->label(__('Authority record identifier').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource); ?>

            <?php echo render_field(
                $form->maintainingRepository
                    ->label(__('Maintaining repository'))
                    ->help(__('"Record the full authorized form of name(s) of the agency(ies) responsible for creating, modifying or disseminating the authority record or, alternatively, record a code for the agency in accordance with the national or international agency code standard. Include reference to any systems of identification used to identify the institutions (e.g. ISO 15511)." (ISAAR 5.4.2)')),
                null,
                [
                    'class' => 'form-autocomplete',
                    'extraInputs' => '<input class="add" type="hidden" data-link-existing="true" value="'
                        .url_for(['module' => 'repository', 'action' => 'add'])
                        .' #authorizedFormOfName">'
                        .'<input class="list" type="hidden" value="'
                        .url_for(['module' => 'repository', 'action' => 'autocomplete'])
                        .'">',
                ]
            ); ?>

            <?php echo render_field($form->institutionResponsibleIdentifier
                ->help(__('"Record the full authorized form of name(s) of the agency(ies) responsible for creating, modifying or disseminating the authority record or, alternatively, record a code for the agency in accordance with the national or international agency code standard. Include reference to any systems of identification used to identify the institutions (e.g. ISO 15511)." (ISAAR 5.4.2)'))
                ->label(__('Institution identifier')), $resource); ?>

            <?php echo render_field($form->rules
                ->help(__('"Purpose: To identify the national or international conventions or rules applied in creating the archival authority record. Rule: Record the names and where useful the editions or publication dates of the conventions or rules applied. Specify separately which rules have been applied for creating the Authorized form of name. Include reference to any system(s) of dating used to identify dates in this authority record (e.g. ISO 8601)." (ISAAR 5.4.3)'))
                ->label(__('Rules and/or conventions used')), $resource); ?>

            <?php echo render_field($form->descriptionStatus
                ->help(__('The purpose of this field is "[t]o indicate the drafting status of the authority record so that users can understand the current status of the authority record." (ISAAR 5.4.4). Select Final, Revised or Draft from the drop-down menu.'))
                ->label('Status')
            ); ?>

            <?php echo render_field($form->descriptionDetail
                ->help(__('Select Full, Partial or Minimal from the drop-down menu. "In the absence of national guidelines or rules, minimal records are those that consist only of the four essential elements of an ISAAR(CPF) compliant authority record (see 4.8), while full records are those that convey information for all relevant ISAAR(CPF) elements of description." (ISAAR 5.4.5)'))
                ->label('Level of detail')
            ); ?>

            <?php echo render_field($form->revisionHistory
                ->help(__('"Record the date the authority record was created and the dates of any revisions to the record." (ISAAR 5.4.6)'))
                ->label(__('Dates of creation, revision and deletion')), $resource); ?>

            <?php if (isset($resource->updatedAt)) { ?>
              <div class="mb-3">
                <h3 class="fs-6 mb-2">
                  <?php echo __('Last updated'); ?>
                </h3>
                <span class="text-muted">
                  <?php echo format_date($resource->updatedAt, 'f'); ?>
                </span>
              </div>
            <?php } ?>

            <?php echo render_field(
                $form->language
                    ->label('Language(s)')
                    ->help(__('Select the language(s) of the authority record from the drop-down menu; enter the first few letters to narrow the choices. (ISAAR 5.4.7)')),
                null,
                ['class' => 'form-autocomplete']
            ); ?>

            <?php echo render_field(
                $form->script
                    ->label('Script(s)')
                    ->help(__('Select the script(s) of the authority record from the drop-down menu; enter the first few letters to narrow the choices. (ISAAR 5.4.7)')),
                null,
                ['class' => 'form-autocomplete']
            ); ?>

            <?php echo render_field($form->sources
                ->help(__('"Record the sources consulted in establishing the authority record." (ISAAR 5.4.8)')), $resource); ?>

            <?php echo render_field($form->maintenanceNotes
                ->help(__('"Record notes pertinent to the creation and maintenance of the authority record. The names of persons responsible for creating the authority record may be recorded here." (ISAAR 5.4.9)')), $isaar); ?>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <?php if (0 < strlen($next = $form->next->getValue())) { ?>
        <li><?php echo link_to(__('Cancel'), $next, ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?>
      <?php } elseif (isset($sf_request->id)) { ?>
        <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'actor'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <?php } else { ?>
        <li><?php echo link_to(__('Cancel'), ['module' => 'actor', 'action' => 'browse'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <?php } ?>
      <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
        <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
      <?php } else { ?>
        <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Create'); ?>"></li>
      <?php } ?>
    </ul>

  </form>

<?php end_slot(); ?>
