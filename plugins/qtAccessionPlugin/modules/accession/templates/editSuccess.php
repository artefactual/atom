<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1 class="multiline">
    <?php echo __('Edit accession record'); ?>
    <span class="sub"><?php echo render_title($resource); ?></span>
  </h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php if (isset($accession)) { ?>
    <div class="messages status">
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

    <section id="content">

      <fieldset class="collapsible" id="basicInfo">

        <legend><?php echo __('Basic info'); ?></legend>

        <?php echo image_tag('loading.small.gif', ['class' => 'hidden pull-right', 'id' => 'spinner', 'alt' => __('Loading...')]); ?>

        <?php echo $form->identifier
            ->help(__('Accession number should be a combination of values recorded in the field and should be a unique accession number for the repository'))
            ->label(__('Accession number'))
            ->renderRow(); ?>

        <div id="identifier-check-server-error" class="alert alert-danger hidden"><?php echo __('Server error while checking identifier availability.'); ?></div>

        <?php echo get_partial('informationobject/identifierOptions', ['hideGenerateButton' => true]); ?>
        <?php echo get_component('accession', 'alternativeIdentifiers', ['resource' => $resource]); ?>

        <?php echo $form->date
            ->help(__('Accession date represents the date of receipt of the materials and is added during the donation process.'))
            ->label(__('Acquisition date').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
            ->renderRow(); ?>

        <?php echo render_field($form->sourceOfAcquisition
            ->help(__('Identify immediate source of acquisition or transfer, and date and method of acquisition IF the information is NOT confidential.'))
            ->label(__('Immediate source of acquisition').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource, ['class' => 'resizable']); ?>

        <?php echo render_field($form->locationInformation
            ->help(__('A description of the physical location in the repository where the accession can be found.'))
            ->label(__('Location information').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource, ['class' => 'resizable']); ?>

      </fieldset>

      <fieldset class="collapsible collapsed" id="donorArea">

        <legend><?php echo __('Donor/Transferring body area'); ?></legend>

        <?php echo get_partial('relatedDonor', $sf_data->getRaw('relatedDonorComponent')->getVarHolder()->getAll()); ?>

      </fieldset>

      <fieldset class="collapsible collapsed" id="administrativeArea">

        <legend><?php echo __('Administrative area'); ?></legend>

        <?php echo $form->acquisitionType
            ->help(__('Term describing the type of accession transaction and referring to the way in which the accession was acquired.'))
            ->renderRow(); ?>

        <?php echo $form->resourceType
            ->help(__('Select the type of resource represented in the accession, either public or private.'))
            ->renderRow(); ?>

        <?php echo render_field($form->title
            ->help(__('The title of the accession, usually the creator name and term describing the format of the accession materials.')), $resource); ?>

        <div class="form-item">
          <?php echo $form->creators
              ->label(__('Creators'))
              ->renderLabel(); ?>
          <?php echo $form->creators->render(['class' => 'form-autocomplete']); ?>
          <?php echo $form->creators
              ->help(__('The name of the creator of the accession or the name of the department that created the accession.'))
              ->renderHelp(); ?>
          <?php if (QubitAcl::check(QubitActor::getRoot(), 'create')) { ?>
            <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(['module' => 'actor', 'action' => 'add']); ?> #authorizedFormOfName"/>
          <?php } ?>
          <input class="list" type="hidden" value="<?php echo url_for(['module' => 'actor', 'action' => 'autocomplete', 'showOnlyActors' => 'true']); ?>"/>
        </div>

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

        <?php echo $form->processingStatus
            ->help(__('An indicator of the accessioning process.'))
            ->renderRow(); ?>

        <?php echo $form->processingPriority
            ->help(__('Indicates the priority the repository assigns to completing the processing of the accession.'))
            ->renderRow(); ?>

        <?php echo render_field($form->processingNotes
            ->help(__('Notes about the processing plan, describing what needs to be done for the accession to be processed completely.')), $resource, ['class' => 'resizable']); ?>

      </fieldset>

      <fieldset class="collapsible collapsed" id="informationObjectArea">

        <legend><?php echo __('%1% area', ['%1%' => sfConfig::get('app_ui_label_informationobject')]); ?></legend>

        <div class="form-item">
          <?php echo $form->informationObjects
              ->label(sfConfig::get('app_ui_label_informationobject'))
              ->renderLabel(); ?>
          <?php echo $form->informationObjects->render(['class' => 'form-autocomplete']); ?>
          <?php if (QubitAcl::check(QubitActor::getRoot(), 'create')) { ?>
            <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(['module' => 'informationobject', 'action' => 'add']); ?> #title"/>
          <?php } ?>
          <input class="list" type="hidden" value="<?php echo url_for(['module' => 'informationobject', 'action' => 'autocomplete']); ?>"/>
        </div>

      </fieldset>

    </section>

    <section class="actions">
      <ul>
        <?php if (isset($resource->id)) { ?>
          <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'accession'], ['class' => 'c-btn']); ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
        <?php } else { ?>
          <li><?php echo link_to(__('Cancel'), ['module' => 'accession', 'action' => 'browse'], ['class' => 'c-btn']); ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Create'); ?>"/></li>
        <?php } ?>
      </ul>
    </section>

  </form>
<?php end_slot(); ?>
