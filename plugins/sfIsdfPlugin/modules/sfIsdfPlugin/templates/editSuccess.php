<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo __('Edit %1% - ISDF', array('%1%' => sfConfig::get('app_ui_label_function'))) ?>
    <span class="sub"><?php echo render_title($resource->getLabel()) ?></span>
  </h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderGlobalErrors() ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
    <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'function', 'action' => 'edit')), array('id' => 'editForm')) ?>
  <?php else: ?>
    <?php echo $form->renderFormTag(url_for(array('module' => 'function', 'action' => 'add')), array('id' => 'editForm')) ?>
  <?php endif; ?>

    <?php echo $form->renderHiddenFields() ?>

    <section id="content">

      <fieldset class="collapsible collapsed" id="identityArea">

        <legend><?php echo __('Identity area') ?></legend>

        <?php echo $form->type
          ->help(__('"Specify whether the description is a function or one of its subdivisions." (ISDF 5.1.1) Select the type from the drop-down menu; these values are drawn from the ISDF Function Types taxonomy.'))
          ->label(__('Type').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
          ->renderRow() ?>

        <?php echo render_field($form->authorizedFormOfName
          ->help(__('"Record the authorised name of the function being described. In cases where the name is not enough, add qualifiers to make it unique such as the territorial or administrative scope, or the name of the institution which performs it. This element is to be used in conjunction with the Function description identifier element (5.4.1)." (ISDF 5.1.2)'))
          ->label(__('Authorized form of name').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource) ?>

        <?php echo $form->parallelName
          ->help(__('"Purpose: To indicate the various forms in which the authorized form(s) of name occurs in other languages or script forms. Rule: Record the parallel form(s) of name in accordance with any relevant national or international conventions or rules applied by the agency that created the description, including any necessary sub elements and/or qualifiers required by those conventions or rules. Specify in the Rules and/or conventions element (5.4.3.) which rules have been applied." (ISDF 5.1.3)'))
          ->label(__('Parallel form(s) of name'))
          ->renderRow() ?>

        <?php echo $form->otherName
          ->help(__('"Record any other names for the function being described." (ISDF 5.1.4)'))
          ->label(__('Other form(s) of name'))
          ->renderRow() ?>

        <?php echo render_field($form->classification
          ->help(__('"Record any term and/or code from a classification scheme of functions. Record the classification scheme used in the element Rules and/or conventions used (5.4.3)." (ISDF 5.1.5)')), $resource) ?>

      </fieldset>

      <fieldset class="collapsible collapsed" id="descriptionArea">

        <legend><?php echo __('Context area') ?></legend>

        <?php echo render_field($form->dates
          ->help(__('"Provide a date or date span which covers the dates when the function was started and when it finished. If a function is ongoing, no end date is needed." (ISDF 5.2.1)')), $resource) ?>

        <?php echo render_field($form->description
          ->help(__('"Record a narrative description of the purpose of the function." (ISDF 5.2.2)')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->history
          ->help(__('"Record in narrative form or as a chronology the main events relating to the function." (ISDF 5.2.3)')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->legislation
          ->help(__('"Record any law, directive or charter which creates, changes or ends the function." (ISDF 5.2.4)')), $resource, array('class' => 'resizable')) ?>

      </fieldset>

      <fieldset class="collapsible collapsed" id="relationshipsArea">

        <legend><?php echo __('Relationships area') ?></legend>

        <?php echo get_partial('relatedFunction', $sf_data->getRaw('relatedFunctionComponent')->getVarHolder()->getAll()) ?>

        <?php echo get_partial('relatedAuthorityRecord', $sf_data->getRaw('relatedAuthorityRecordComponent')->getVarHolder()->getAll()) ?>

        <?php echo get_partial('relatedResource', $sf_data->getRaw('relatedResourceComponent')->getVarHolder()->getAll()) ?>

      </fieldset>

      <fieldset class="collapsible collapsed" id="controlArea">

        <legend><?php echo __('Control area') ?></legend>

        <?php echo render_field($form->descriptionIdentifier
          ->help(__('"Record a unique description identifier in accordance with local and/or national conventions. If the description is to be used internationally, record the code of the country in which the description was created in accordance with the latest version of ISO 3166 Codes for the representation of names of countries. Where the creator of the description is an international organisation, give the organisational identifier in place of the country code." (ISDF 5.4.1)'))
          ->label(__('Description identifier').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource) ?>

        <?php echo render_field($form->institutionIdentifier
          ->help(__('"Record the full authorised form of name(s) of agency(ies) responsible for creating, modifying or disseminating the description or, alternatively, record a recognized code for the agency." (ISDF 5.4.2)'))
          ->label(__('Institution identifier')), $resource) ?>

        <?php echo render_field($form->rules
          ->help(__('"Purpose: To identify the national or international conventions or rules applied in creating the archival description. Rule: Record the names and where useful the editions or publication dates of the conventions or rules applied." (ISDF 5.4.3)'))
          ->label(__('Rules and/or conventions used')), $resource, array('class' => 'resizable')) ?>

        <?php echo $form->descriptionStatus
          ->help(__('The purpose of this field is "[t]o indicate the drafting status of the description so that users can understand the current status of the description." (ISDF 5.4.4). Select Final, Revised or Draft from the drop-down menu.'))
          ->label(__('Status'))
          ->renderRow() ?>

        <?php echo $form->descriptionDetail
          ->help(__('Select Full, Partial or Minimal from the drop-down menu. "In the absence of national guidelines or rules, minimum records are those that consist only of the three essential elements of an ISDF compliant record (see 4.7), while full records are those that convey information for all relevant ISDF elements of description." (ISDF 5.4.5)'))
          ->label(__('Level of detail'))
          ->renderRow() ?>

        <?php echo render_field($form->revisionHistory
          ->help(__('"Record the date the description was created and the dates of any revisions to the description." (ISDF 5.4.6)'))
          ->label(__('Dates of creation, revision or deletion')), $resource, array('class' => 'resizable')) ?>

        <?php echo $form->language
          ->help(__('Select the language(s) of this record from the drop-down menu; enter the first few letters to narrow the choices. (ISDF 5.4.7)'))
          ->label(__('Language(s)'))
          ->renderRow(array('class' => 'form-autocomplete')) ?>

        <?php echo $form->script
          ->help(__('Select the script(s) of this record from the drop-down menu; enter the first few letters to narrow the choices. (ISDF 5.4.7)'))
          ->label(__('Script(s)'))
          ->renderRow(array('class' => 'form-autocomplete')) ?>

        <?php echo render_field($form->sources
          ->help(__('"Record the sources consulted in establishing the function description." (ISDF 5.4.8)')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->maintenanceNotes
          ->help(__('"Record notes pertinent to the creation and maintenance of the description." (ISDF 5.4.9)')), $isdf, array('class' => 'resizable')) ?>

      </fieldset>

    </section>

    <section class="actions">
      <ul>
        <?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
          <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'function'), array('class' => 'c-btn')) ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
        <?php else: ?>
          <li><?php echo link_to(__('Cancel'), array('module' => 'function', 'action' => 'list'), array('class' => 'c-btn')) ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Create') ?>"/></li>
        <?php endif; ?>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
