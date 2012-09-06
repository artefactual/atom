<h1><?php echo __('Edit resource metadata - MODS') ?></h1>

<h1 class="label"><?php echo render_title($mods) ?></h1>

<?php if (isset($sf_request->source)): ?>
  <div class="messages status">
    <?php echo __('This is a duplicate of record %1%', array('%1%' => $sourceInformationObjectLabel)) ?>
  </div>
<?php endif; ?>

<?php echo $form->renderGlobalErrors() ?>

<?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'edit')), array('id' => 'editForm')) ?>
<?php else: ?>
  <?php echo $form->renderFormTag(url_for(array('module' => 'informationobject', 'action' => 'add')), array('id' => 'editForm')) ?>
<?php endif; ?>

  <?php echo $form->renderHiddenFields() ?>

  <?php echo $form->identifier
    ->help(__('Contains a unique standard number or code that distinctively identifies a resource.'))
    ->renderRow() ?>

  <?php echo render_field($form->title
    ->help(__('A word, phrase, character, or group of characters, normally appearing in a resource, that names it or the work contained in it. Choice and format of titles should be governed by a content standard such as the Anglo-American Cataloging Rules, 2nd edition (AACR2), Cataloguing Cultural Objects (CCO), or Describing Archives: A Content Standard (DACS). Details such as capitalization, choosing among the forms of titles presented on an item, and use of abbreviations should be determined based on the rules in a content standard. One standard should be chosen and used consistently for all records in a set.')), $resource) ?>

  <div class="section">

    <h3><?php echo __('Names and origin info') ?></h3>

    <?php echo get_partial('informationobject/relatedEvents', array('resource' => $resource)) ?>

  </div>

  <div class="section">

    <h3><?php echo __('Add new name and/or origin info') ?></h3>

    <?php echo get_partial('informationobject/event', $eventComponent->getVarHolder()->getAll()) ?>

  </div>

  <?php echo $form->type
    ->help(__('A term that specifies the characteristics and general type of content of the resource. Assign as many types as are applicable. The Type of resource options are limited to the values in the MODS typeOfResource top-level element.'))
    ->label(__('Type of resource'))
    ->renderRow() ?>

  <div class="section">

    <h3><?php echo __('Add new child levels (if describing a collection)') ?></h3>

    <table class="multiRow">
      <thead>
        <tr>
          <th style="width: 20%">
            <?php echo __('Identifier') ?>
          </th><th style="width: 80%">
            <?php echo __('Title') ?>
          </th>
        </tr>
      </thead><tbody>
        <tr>
          <td>
            <input name="updateChildLevels[0][identifier]"/>
          </td><td>
            <input name="updateChildLevels[0][title]"/>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="description">
      <?php echo __('Use these two fields to add lower levels to a collection-level description. Click Add new to add as many child levels as necessary. Identifer: enter a unique standard number or code that distinctively identifies the resource. Title: enter A word, phrase, character, or group of characters, normally appearing in a resource, that names it or the work contained in it.') ?>
    </div>

  </div>

  <?php echo $form->language
    ->help(__('A designation of the language in which the content of a resource is expressed. Select as many languages as required.'))
    ->renderRow(array('class' => 'form-autocomplete')) ?>

  <div class="form-item">
    <?php echo $form->subjectAccessPoints
      ->label(__('Subject'))
      ->renderLabel() ?>
    <?php echo $form->subjectAccessPoints->render(array('class' => 'form-autocomplete')) ?>
    <?php echo $form->subjectAccessPoints
      ->help(__('A term or phrase representing the primary topic(s) on which a work is focused. Search for an existing term in the Subjects taxonomy by typing the first few characters of the term name. Alternatively, type a new name to create and link to a new subject term.'))
      ->renderHelp() ?>
    <?php if (QubitAcl::check(QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID), 'createTerm')): ?>
      <input class="add" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID), 'module' => 'taxonomy')))) ?> #name"/>
    <?php endif; ?>
    <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID), 'module' => 'taxonomy')))) ?>"/>
  </div>

  <?php echo render_field($form->accessConditions
    ->help(__('Information about restrictions imposed on access to a resource. See MODS accessCondition top-level element for more information on how to use this field.')), $resource, array('class' => 'resizable')) ?>

  <div class="form-item">
    <?php echo $form->repository->renderLabel() ?>
    <?php echo $form->repository->render(array('class' => 'form-autocomplete')) ?>
    <?php echo $form->repository
      ->help(__('Identifies the institution or repository holding the resource. Search for an existing repository name by typing the first few letters of the name. ALternatively, type a new name to create and link to a new repository record.'))
      ->renderHelp() ?>
    <input class="add" type="hidden" value="<?php echo url_for(array('module' => 'repository', 'action' => 'add')) ?> #authorizedFormOfName"/>
    <input class="list" type="hidden" value="<?php echo url_for($repoAcParams) ?>"/>
  </div>

  <fieldset class="collapsible collapsed" id="rightsArea">

    <legend><?php echo __('Rights area') ?></legend>

    <?php echo get_partial('right/edit', $rightEditComponent->getVarHolder()->getAll()) ?>

  </fieldset>

  <?php echo get_partial('informationobject/adminInfo', array('form' => $form, 'resource' => $resource)) ?>

  <?php echo get_partial('informationobject/editActions', array('resource' => $resource)) ?>

</form>
