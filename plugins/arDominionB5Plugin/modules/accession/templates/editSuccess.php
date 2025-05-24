<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo __('Edit accession record'); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php echo render_title($resource); ?>
    </span>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php if (isset($accession)) { ?>
    <div class="alert alert-info" role="alert">
      <?php echo __('You are creating an accrual to accession %1%', ['%1%' => $accession]); ?>
    </div>
  <?php } ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'accession', 'action' => 'edit']), ['id' => 'editForm']); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for(['module' => 'accession', 'action' => 'add']), ['id' => 'editForm']); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="basic-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#basic-collapse" aria-expanded="false" aria-controls="basic-collapse">
            <?php echo __('Basic info'); ?>
          </button>
        </h2>
        <div id="basic-collapse" class="accordion-collapse collapse" aria-labelledby="basic-heading">
          <div class="accordion-body">
            <?php echo render_field($form->identifier
                ->help(__('Accession number should be a combination of values recorded in the field and should be a unique accession number for the repository'))
                ->label(__('Accession number'))
            ); ?>

            <div id="identifier-check-server-error" class="alert alert-danger hidden"><?php echo __('Server error while checking identifier availability.'); ?></div>

            <?php echo get_component('accession', 'alternativeIdentifiers', ['resource' => $resource]); ?>

            <?php echo render_field(
                $form->date
                    ->help(__('Accession date represents the date of receipt of the materials and is added during the donation process.'))
                    ->label(__('Acquisition date').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'),
                null,
                ['type' => 'date']
            ); ?>

            <?php echo render_field($form->sourceOfAcquisition
                ->help(__('Identify immediate source of acquisition or transfer, and date and method of acquisition IF the information is NOT confidential.'))
                ->label(__('Immediate source of acquisition').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource, ['class' => 'resizable']); ?>

            <?php echo render_field($form->locationInformation
                ->help(__('A description of the physical location in the repository where the accession can be found.'))
                ->label(__('Location information').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource, ['class' => 'resizable']); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="donor-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#donor-collapse" aria-expanded="false" aria-controls="donor-collapse">
            <?php echo __('Donor/Transferring body area'); ?>
          </button>
        </h2>
        <div id="donor-collapse" class="accordion-collapse collapse" aria-labelledby="donor-heading">
          <div class="accordion-body">
            <?php echo get_partial('relatedDonor', $sf_data->getRaw('relatedDonorComponent')->getVarHolder()->getAll()); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="admin-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#admin-collapse" aria-expanded="false" aria-controls="admin-collapse">
            <?php echo __('Administrative area'); ?>
          </button>
        </h2>
        <div id="admin-collapse" class="accordion-collapse collapse" aria-labelledby="admin-heading">
          <div class="accordion-body">
            <?php echo render_field($form->acquisitionType
                ->help(__('Term describing the type of accession transaction and referring to the way in which the accession was acquired.'))
            ); ?>

            <?php echo render_field($form->resourceType
                ->help(__('Select the type of resource represented in the accession, either public or private.'))
            ); ?>

            <?php echo render_field($form->title
                ->help(__('The title of the accession, usually the creator name and term describing the format of the accession materials.')), $resource); ?>

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
                    $form->creators->label(__('Creators'))->help(__(
                        'The name of the creator of the accession or the name of the department that created the accession.'
                    )),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>

            <?php echo get_partial('sfIsadPlugin/event', $sf_data->getRaw('eventComponent')->getVarHolder()->getAll() + ['help' => __('"Identify and record the date(s) of the unit of description. Identify the type of date given. Record as a single date or a range of dates as appropriate.” (ISAD 3.1.3). The Date display field can be used to enter free-text date information, including typographical marks to express approximation, uncertainty, or qualification. Use the start and end fields to make the dates searchable. Do not use any qualifiers or typographical symbols to express uncertainty. Acceptable date formats: YYYYMMDD, YYYY-MM-DD, YYYY-MM, YYYY.')]); ?>

            <?php echo get_component('accession', 'events', ['resource' => $resource]); ?>

            <?php echo render_field($form->archivalHistory
                ->help(__('Information on the history of the accession. When the accession is acquired directly from the creator, do not record an archival history but record the information as the Immediate Source of Acquisition.'))
                ->label(__('Archival/Custodial history')), $resource, ['class' => 'resizable']); ?>

            <?php echo render_field($form->scopeAndContent
                ->help(__('A description of the intellectual content and document types represented in the accession.')), $resource, ['class' => 'resizable']); ?>

            <?php echo render_field($form->appraisal
                ->help(__('Record appraisal, destruction and scheduling actions taken on or planned for the unit of description, especially if they may affect the interpretation of the material.'))
                ->label(__('Appraisal, destruction and scheduling')), $resource, ['class' => 'resizable']); ?>

            <?php echo render_field($form->physicalCharacteristics
                ->help(__('A description of the physical condition of the accession and if any preservation or special handling is required.'))
                ->label(__('Physical condition')), $resource, ['class' => 'resizable']); ?>

            <?php echo render_field($form->receivedExtentUnits
                ->help(__('The number of units as a whole number and the measurement of the received volume of records in the accession.'))
                ->label(__('Received extent units')), $resource, ['class' => 'resizable']); ?>

            <?php echo render_field($form->processingStatus
                ->help(__('An indicator of the accessioning process.'))
            ); ?>

            <?php echo render_field($form->processingPriority
                ->help(__('Indicates the priority the repository assigns to completing the processing of the accession.'))
            ); ?>

            <?php echo render_field($form->processingNotes
                ->help(__('Notes about the processing plan, describing what needs to be done for the accession to be processed completely.')), $resource, ['class' => 'resizable']); ?>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="io-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#io-collapse" aria-expanded="false" aria-controls="io-collapse">
            <?php echo __('%1% area', ['%1%' => sfConfig::get('app_ui_label_informationobject')]); ?>
          </button>
        </h2>
        <div id="io-collapse" class="accordion-collapse collapse" aria-labelledby="io-heading">
          <div class="accordion-body">
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
                    $form->informationObjects->label(sfConfig::get('app_ui_label_informationobject')),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <?php if (isset($resource->id)) { ?>
        <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'accession'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
        <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
      <?php } else { ?>
        <li><?php echo link_to(__('Cancel'), ['module' => 'accession', 'action' => 'browse'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
        <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Create'); ?>"></li>
      <?php } ?>
    </ul>

  </form>
<?php end_slot(); ?>
