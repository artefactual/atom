<?php decorate_with('layout_2col.php') ?>
<?php use_helper('Date') ?>

<?php slot('sidebar') ?>

  <?php include_component('repository', 'contextMenu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo render_title($dacs) ?></h1>

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

        <legend><?php echo __('Identity elements') ?></legend>

        <?php echo render_show(__('Reference code'), $dacs->referenceCode) ?>

        <?php echo $form->identifier
          ->help(__('TODO tooltip'))
          ->label(__('Identifier').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
          ->renderRow() ?>

        <div class="form-item">
          <?php echo $form->repository->label(__('Name and location of repository'))->renderLabel() ?>
          <?php echo $form->repository->render(array('class' => 'form-autocomplete')) ?>
          <input class="add" type="hidden" value="<?php echo url_for(array('module' => 'repository', 'action' => 'add')) ?> #authorizedFormOfName"/>
          <input class="list" type="hidden" value="<?php echo url_for($repoAcParams) ?>"/>
          <?php echo $form->repository
            ->help(__('Record the name of the organization which has custody of the archival material. Search for an existing name in the archival institution records by typing the first few characters of the name. Alternatively, type a new name to create and link to a new archival institution record.'))
            ->renderHelp(); ?>
        </div>

        <?php echo $form->levelOfDescription
          ->help(__('TODO tooltip'))
          ->label(__('Levels of description'))
          ->renderRow() ?>

        <?php echo render_field($form->title
          ->help(__('TODO tooltip'))
          ->label(__('Title').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource) ?>

        <?php echo get_partial('sfIsadPlugin/event', $eventComponent->getVarHolder()->getAll()) ?>

        <?php echo render_field($form->extentAndMedium
          ->help(__('Record information about physical characteristics that affect the use of the unit being described in the Physical Access Element.'))
          ->label(__('Extent').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource, array('class' => 'resizable')) ?>

        <div class="form-item">
          <?php echo $form->creators
            ->label(__('Name of creator(s)').' <span class="form-required" title="'.__('This archival description, or one of its higher levels, requires at least one creator.').'">*</span>')
            ->renderLabel() ?>
          <?php echo $form->creators->render(array('class' => 'form-autocomplete')) ?>
          <?php echo $form->creators
            ->help(__('Optionally, describe the nature of the relationship between the entities named in the creator element and the materials being described (e.g., creator, author, subject, custodian, copyright owner, controller, or owner.).'))
            ->renderHelp() ?>
          <input class="add" type="hidden" value="<?php echo url_for(array('module' => 'actor', 'action' => 'add')) ?> #authorizedFormOfName"/>
          <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'actor', 'action' => 'autocomplete')) ?>"/>
        </div>

        <?php echo render_field($form->archivalHistory
          ->label(__('Administrative/biographical history'))
          ->help(__('TODO tooltip')), $resource, array('class' => 'resizable')) ?>

        <?php echo get_partial('informationobject/childLevels', array('help' => __('TODO tooltip'))) ?>

      </fieldset> <!-- /#identityArea -->

      <fieldset class="collapsible collapsed" id="contentAndStructureArea">

        <legend><?php echo __('Content and structure elements') ?></legend>

        <?php echo render_field($form->scopeAndContent
          ->help(__('TODO tooltip'))
          ->label(__('Scope and content').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource) ?>

        <?php echo render_field($form->arrangement
          ->help(__('TODO tooltip'))
          ->label(__('System of arrangement')), $resource, array('class' => 'resizable')) ?>

      </fieldset> <!-- /#contentAndStructureArea -->

      <fieldset class="collapsible collapsed" id="conditionsOfAccessAndUseArea">

        <legend><?php echo __('Conditions of access and use elements') ?></legend>

        <?php echo render_field($form->accessConditions
          ->help(__('TODO tooltip'))
          ->label(__('Conditions governing access')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->physicalCharacteristics
          ->help(__('TODO tooltip'))
          ->label(__('Physical access')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->technicalAccess
          ->help(__('TODO tooltip'))
          ->label(__('Technical access')), $dacs) ?>

        <?php echo render_field($form->reproductionConditions
          ->help(__('TODO tooltip'))
          ->label(__('Conditions governing reproduction and use')), $resource, array('class' => 'resizable')) ?>

        <?php echo $form->language
          ->help(__('TODO tooltip'))
          ->label(__('Languages of the material'))
          ->renderRow(array('class' => 'form-autocomplete')) ?>

        <?php echo $form->script
          ->help(__('TODO tooltip'))
          ->label(__('Scripts of the material'))
          ->renderRow(array('class' => 'form-autocomplete')) ?>

        <?php echo render_field($form->languageNotes
          ->help(__('TODO tooltip'))
          ->label(__('Language and script notes')), $dacs, array('class' => 'resizable')) ?>

        <?php echo render_field($form->findingAids
          ->help(__('TODO tooltip')), $resource, array('class' => 'resizable')) ?>

      </fieldset> <!-- /#conditionsOfAccessAndUseArea -->

      <fieldset class="collapsible collapsed" id="acquisitionAndAppraisalArea">

        <legend><?php echo __('Acquisition and appraisal elements') ?></legend>

        <?php echo render_field($form->archivalHistory
          ->label(__('Custodial history'))
          ->help(__('TODO tooltip')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->acquisition
          ->help(__('TODO tooltip'))
          ->label(__('Immediate source of acquisition or transfer')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->appraisal
          ->help(__('TODO tooltip'))
          ->label(__('Appraisal, destruction and scheduling information')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->accruals
          ->help(__('TODO tooltip')), $resource, array('class' => 'resizable')) ?>

      </fieldset> <!-- /#acquisitionAndAppraisalArea -->

      <fieldset class="collapsible collapsed" id="alliedMaterialsArea">

        <legend><?php echo __('Related materials elements') ?></legend>

        <?php echo render_field($form->locationOfOriginals
          ->help(__('TODO tooltip'))
          ->label(__('Existence and location of originals')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->locationOfCopies
          ->help(__('TODO tooltip'))
          ->label(__('Existence and location of copies')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->relatedUnitsOfDescription
          ->help(__('TODO tooltip'))
          ->label(__('Related archival materials')), $resource, array('class' => 'resizable')) ?>

        <?php echo get_partial('informationobject/notes', $publicationNotesComponent->getVarHolder()->getAll()) ?>

      </fieldset> <!-- /#alliedMaterialsArea -->

      <fieldset class="collapsible collapsed" id="notesArea">

        <legend><?php echo __('Notes element') ?></legend>

        <?php echo get_partial('informationobject/notes', $notesComponent->getVarHolder()->getAll()) ?>

      </fieldset> <!-- /#notesArea -->

      <fieldset class="collapsible collapsed" id="descriptionControlArea">

        <legend><?php echo __('Description control element') ?></legend>

        <?php echo render_field($form->sources
          ->help(__('TODO tooltip'))
          ->label(__('Sources used')), $dacs, array('class' => 'resizable')) ?>

        <?php echo render_field($form->rules
          ->help(__('TODO tooltip'))
          ->label(__('Rules or conventions')), $resource, array('class' => 'resizable')) ?>

        <!-- TODO: Make $archivistsNotesComponent to include ISAD 3.7.3 Date(s) of description as the first note and editable -->

        <?php echo get_partial('informationobject/notes', $archivistsNotesComponent->getVarHolder()->getAll()) ?>

      </fieldset> <!-- /#descriptionControlArea -->

      <fieldset class="collapsible collapsed" id="accessPointsArea">

        <legend><?php echo __('Access points') ?></legend>

        <div class="form-item">
          <?php echo $form->subjectAccessPoints
            ->label(__('Subject access points'))
            ->renderLabel() ?>
          <?php echo $form->subjectAccessPoints->render(array('class' => 'form-autocomplete')) ?>
          <?php if (QubitAcl::check(QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID), 'createTerm')): ?>
            <input class="add" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID), 'module' => 'taxonomy')))) ?> #name"/>
          <?php endif; ?>
          <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID), 'module' => 'taxonomy')))) ?>"/>
        </div>

        <div class="form-item">
          <?php echo $form->placeAccessPoints
            ->label(__('Place access points'))
            ->renderLabel() ?>
          <?php echo $form->placeAccessPoints->render(array('class' => 'form-autocomplete')) ?>
          <?php if (QubitAcl::check(QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'createTerm')): ?>
            <input class="add" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'module' => 'taxonomy')))) ?> #name"/>
          <?php endif; ?>
          <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'module' => 'taxonomy')))) ?>"/>
        </div>

        <div class="form-item">
          <?php echo $form->nameAccessPoints
            ->label(__('Name access points (subjects)'))
            ->renderLabel() ?>
          <?php echo $form->nameAccessPoints->render(array('class' => 'form-autocomplete')) ?>
          <?php if (QubitAcl::check(QubitActor::getRoot(), 'create')): ?>
            <input class="add" type="hidden" value="<?php echo url_for(array('module' => 'actor', 'action' => 'add')) ?> #authorizedFormOfName"/>
          <?php endif; ?>
          <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'actor', 'action' => 'autocomplete', 'showOnlyActors' => 'true')) ?>"/>
        </div>

      </fieldset>

      <fieldset class="collapsible collapsed" id="rightsArea">

        <legend><?php echo __('Rights area') ?></legend>

        <?php echo get_partial('right/edit', $rightEditComponent->getVarHolder()->getAll()) ?>

      </fieldset>

      <?php echo get_partial('informationobject/adminInfo', array('form' => $form, 'resource' => $resource)) ?>

    </div>

    <?php echo get_partial('informationobject/editActions', array('resource' => $resource)) ?>

  </form>

<?php end_slot() ?>
