<?php decorate_with('layout_1col.php'); ?>
<?php use_helper('Date'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo __('Edit %1% - ISDIAH', ['%1%' => sfConfig::get('app_ui_label_repository')]); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php echo render_title($resource->getLabel()); ?>
    </span>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'repository', 'action' => 'edit']), ['id' => 'editForm']); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for(['module' => 'repository', 'action' => 'add'])); ?>
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
            <?php echo render_field($form->identifier
                ->help(__('"Record the numeric or alpha-numeric code identifying the institution in accordance with the relevant international and national standards." (ISDIAH 5.1.1)'))
                ->label(__('Identifier').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
            ); ?>

            <?php echo render_field($form->authorizedFormOfName
                ->help(__('"Record the standardised form of name of the institution, adding appropriate qualifiers (for instance dates, place, etc.), if necessary. Specify separately in the Rules and/or conventions used element (5.6.3) which set of rules has been applied for this element." (ISDIAH 5.1.2)'))
                ->label(__('Authorized form of name').' <span class="form-required" title="'.__('This is a mandatory element').'">*</span>'), $resource); ?>

            <?php echo render_field($form->parallelName
                ->help(__('"Purpose: To indicate the various forms in which the authorised form of name of an institution occurs in other languages or script form(s). Rule: Record the parallel form(s) of name of the institution in accordance with any relevant national or international conventions or rules applied by the agency that created the description, including any necessary sub elements and/or qualifiers required by those conventions or rules. Specify in the Rules and/or conventions used element (5.6.3) which rules have been applied." (ISDIAH 5.1.3)'))
                ->label(__('Parallel form(s) of name'))
            ); ?>

            <?php echo render_field($form->otherName
                ->help(__('"Record any other name(s) by which the institution may be known. This could include other forms of the same name, acronyms, other institutional names, or changes of name over time, including, if possible, relevant dates." (ISDIAH 5.1.4)'))
                ->label(__('Other form(s) of name'))
            ); ?>

            <?php
                $taxonomy = QubitTaxonomy::getById(QubitTaxonomy::REPOSITORY_TYPE_ID);
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
                    $form->type->help(__(
                        'Record the type of the institution. (ISDIAH 5.1.5) Select as many types as desired'
                        .' from the drop-down menu; these values are drawn from the Repository Types taxonomy.'
                    )),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="contact-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#contact-collapse" aria-expanded="false" aria-controls="contact-collapse">
            <?php echo __('Contact area'); ?>
          </button>
        </h2>
        <div id="contact-collapse" class="accordion-collapse collapse" aria-labelledby="contact-heading">
          <div class="accordion-body">
            <?php echo get_partial('contactinformation/edit', $sf_data->getRaw('contactInformationEditComponent')->getVarHolder()->getAll()); ?>
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
            <?php echo render_field($form->history
                ->help(__('"Record any relevant information about the history of the institution. This element may include information on dates of establishment, changes of names, changes of legislative mandates, or of any other sources of authority for the institution." (ISDIAH 5.3.1)')), $resource); ?>

            <?php echo render_field($form->geoculturalContext
                ->help(__('"Identify the geographical area the institution belongs to. Record any other relevant information about the cultural context of the institution." (ISDIAH 5.3.2)'))
                ->label(__('Geographical and cultural context')), $resource); ?>

            <?php echo render_field($form->mandates
                ->help(__('"Record any document, law, directive or charter which acts as a source of authority for the powers, functions and responsibilities of the institution, together with information on the jurisdiction(s) and covering dates when the mandate(s) applied or were changed." (ISDIAH 5.3.3)'))
                ->label(__('Mandates/Sources of authority')), $resource); ?>

            <?php echo render_field($form->internalStructures
                ->help(__('"Describe, in narrative form or using organisational charts, the current administrative structure of the institution." (ISDIAH 5.3.4)'))
                ->label(__('Administrative structure')), $resource); ?>

            <?php echo render_field($form->collectingPolicies
                ->help(__('"Record information about the records management and collecting policies of the institution. Define the scope and nature of material which the institution accessions. Indicate whether the repository seeks to acquire archival materials by transfer, gift, purchase and/or loan. If the policy includes active survey and/or rescue work, this might be spelt out." (ISDIAH 5.3.5)'))
                ->label(__('Records management and collecting policies')), $resource); ?>

            <?php echo render_field($form->buildings
                ->help(__('"Record information on the building(s) of the institution (general and architectural characteristics of the building, capacity of storage areas, etc). Where possible, provide information which can be used for generating statistics." (ISDIAH 5.3.6)')), $resource); ?>

            <?php echo render_field($form->holdings
                ->help(__('"Record a short description of the holdings of the institution, describing how and when they were formed. Provide information on volume of holdings, media formats, thematic coverage, etc." (ISDIAH 5.3.7)'))
                ->label(__('Archival and other holdings')), $resource); ?>

            <?php echo render_field($form->findingAids
                ->help(__('"Record the title and other pertinent details of the published and/or unpublished finding aids and guides prepared by the institution and of any other relevant publications. Use ISO 690 Information and documentation – Bibliographic references and other national or international cataloguing rules." (ISDIAH 5.3.8)'))
                ->label(__('Finding aids, guides and publications')), $resource); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="access-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#access-collapse" aria-expanded="false" aria-controls="access-collapse">
            <?php echo __('Access area'); ?>
          </button>
        </h2>
        <div id="access-collapse" class="accordion-collapse collapse" aria-labelledby="access-heading">
          <div class="accordion-body">
            <?php echo render_field($form->openingTimes
                ->help(__('"Record the opening hours of the institution and annual, seasonal and public holidays, and any other planned closures. Record times associated with the availability and/or delivery of services (for example, exhibition spaces, reference services, etc.)." (ISDIAH 5.4.1)')), $resource); ?>

            <?php echo render_field($form->accessConditions
                ->help(__('"Describe access policies, including any restrictions and/or regulations for the use of materials and facilities. Record information about registration, appointments, readers’ tickets, letters of introduction, admission fees, etc. Where appropriate, make reference to the relevant legislation." (ISDIAH 5.4.2)'))
                ->label(__('Conditions and requirements')), $resource); ?>

            <?php echo render_field($form->disabledAccess
                ->help(__('"Record information about travelling to the institution and details for users with disabilities, including building features, specialised equipment or tools, parking or lifts." (ISDIAH 5.4.3)'))
                ->label(__('Accessibility')), $resource); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="services-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#services-collapse" aria-expanded="false" aria-controls="services-collapse">
            <?php echo __('Services area'); ?>
          </button>
        </h2>
        <div id="services-collapse" class="accordion-collapse collapse" aria-labelledby="services-heading">
          <div class="accordion-body">
            <?php echo render_field($form->researchServices
                ->help(__('"Record information about the onsite services provided by the institution such as languages spoken by staff, research and consultation rooms, enquiry services, internal libraries, map, microfiches, audio-visual, computer rooms, etc. Record as well any relevant information about research services, such as research undertaken by the institution, and the fee charge if applicable." (ISDIAH 5.5.1)')), $resource); ?>

            <?php echo render_field($form->reproductionServices
                ->help(__('"Record information about reproduction services available to the public (microfilms, photocopies, photographs, digitised copies). Specify general conditions and restrictions to the services, including applicable fees and publication rules." (ISDIAH 5.5.2)')), $resource); ?>

            <?php echo render_field($form->publicFacilities
                ->help(__('"Record information about spaces available for public use (permanent or temporary exhibitions, free or charged internet connection, cash machines, cafeterias, restaurants, shops, etc.)." (ISDIAH 5.5.3)'))
                ->label(__('Public areas')), $resource); ?>
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
            <?php echo render_field($form->descIdentifier
                ->help(__('"Record a unique description identifier in accordance with local and/or national conventions. If the description is to be used internationally, record the code of the country in which the description was created in accordance with the latest version of ISO 3166 - Codes for the representation of names of countries. Where the creator of the description is an international organisation, give the organisational identifier in place of the country code." (ISIAH 5.6.1)'))
                ->label(__('Description identifier')), $resource); ?>

            <?php echo render_field($form->descInstitutionIdentifier
                ->help(__('"Record the full authorised form of name(s) of the agency(ies) responsible for creating, modifying or disseminating the description or, alternatively, record a code for the agency in accordance with the national or international agency code standard." (ISDIAH 5.6.2)'))
                ->label(__('Institution identifier')), $resource); ?>

            <?php echo render_field($form->descRules
                ->help(__('"Record the names, and, where useful, the editions or publication dates of the conventions or rules applied. Specify, separately, which rules have been applied for creating the Authorised form(s) of name. Include reference to any system(s) of dating used to identify dates in this description (e.g. ISO 8601)." (ISDIAH 5.6.3)'))
                ->label(__('Rules and/or conventions used')), $resource); ?>

            <?php echo render_field($form->descStatus
                ->help(__('The purpose of this field is "[t]o indicate the drafting status of the description so that users can understand the current status of the description." (ISDIAH 5.6.4). Select Final, Revised or Draft from the drop-down menu.'))
                ->label('Status')
            ); ?>

            <?php echo render_field($form->descDetail
                ->help(__('Select Full, Partial or Minimal from the drop-down menu. "In the absence of national guidelines or rules, minimal descriptions are those that consist only of the three essential elements of an ISIAH compliant description (see 4.7), while full records are those that convey information for all relevant ISDIAH elements of description." (ISDIAH 5.6.5)'))
                ->label('Level of detail')
            ); ?>

            <?php echo render_field($form->descRevisionHistory
                ->help(__('"Record the date the description was created and the dates of any revisions to the description." (ISDIAH 5.6.6)'))
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
                $form->language->help(__(
                    'Select the language(s) of this record from the drop-down menu; enter the first few letters to narrow the choices. (ISDIAH 5.6.7)'
                )),
                null,
                ['class' => 'form-autocomplete']
            ); ?>

            <?php echo render_field(
                $form->script->help(__(
                    'Select the script(s) of this record from the drop-down menu; enter the first few letters to narrow the choices. (ISDIAH 5.6.7)'
                )),
                null,
                ['class' => 'form-autocomplete']
            ); ?>

            <?php echo render_field($form->descSources
                ->help(__('"Record the sources consulted in establishing the description of the institution." (ISDIAH 5.6.8)'))
                ->label(__('Sources')), $resource); ?>

            <?php echo render_field($form->maintenanceNotes
                ->help(__('"Record notes pertinent to the creation and maintenance of the description." (ISDIAH 5.6.9)')), $isdiah); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="points-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#points-collapse" aria-expanded="false" aria-controls="points-collapse">
            <?php echo __('Access points'); ?>
          </button>
        </h2>
        <div id="points-collapse" class="accordion-collapse collapse" aria-labelledby="points-heading">
          <div class="accordion-body">
            <?php
                $taxonomy = QubitTaxonomy::getById(QubitTaxonomy::THEMATIC_AREA_ID);
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
                    $form->thematicArea->label(__('Thematic area'))->help(__(
                        'Search for an existing term in the Thematic Areas taxonomy by typing the first few'
                        .' characters of the term name. This should be used to identify major collecting areas.'
                    )),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>

            <?php
                $taxonomy = QubitTaxonomy::getById(QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID);
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
                    $form->geographicSubregion->label(__('Geographic subregion'))->help(__(
                        'Search for an existing term in the Geographic Subregion taxonomy by typing the first'
                        .' few characters of the term name.'
                    )),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
        <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'repository'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
        <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
      <?php } else { ?>
        <li><?php echo link_to(__('Cancel'), ['module' => 'repository', 'action' => 'browse'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
        <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Create'); ?>"></li>
      <?php } ?>
    </ul>

  </form>

<?php end_slot(); ?>
