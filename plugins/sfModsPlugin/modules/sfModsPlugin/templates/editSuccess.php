<?php decorate_with('layout_2col.php'); ?>
<?php use_helper('Date'); ?>

<?php slot('sidebar'); ?>

  <?php include_component('repository', 'contextMenu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <?php echo get_component('informationobject', 'descriptionHeader', ['resource' => $resource, 'title' => (string) $mods]); ?>

  <?php if (isset($sf_request->source)) { ?>
    <div class="messages status">
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

    <div id="content">

      <fieldset class="collapsible" id="elementsArea">

        <legend><?php echo __('Elements area'); ?></legend>

        <?php echo $form->identifier
            ->help(__('Contains a unique standard number or code that distinctively identifies a resource.'))
            ->renderRow(); ?>

        <?php echo get_partial('informationobject/identifierOptions', ['mask' => $mask, 'hideAltIdButton' => true]); ?>

        <?php echo render_field($form->title
            ->help(__('A word, phrase, character, or group of characters, normally appearing in a resource, that names it or the work contained in it. Choice and format of titles should be governed by a content standard such as the Anglo-American Cataloging Rules, 2nd edition (AACR2), Cataloguing Cultural Objects (CCO), or Describing Archives: A Content Standard (DACS). Details such as capitalization, choosing among the forms of titles presented on an item, and use of abbreviations should be determined based on the rules in a content standard. One standard should be chosen and used consistently for all records in a set.')), $resource); ?>

        <section>

          <h3><?php echo __('Names and origin info'); ?></h3>

          <?php echo get_partial('informationobject/relatedEvents', ['resource' => $resource]); ?>

        </section>

        <section>

          <h3><?php echo __('Add new name and/or origin info'); ?></h3>

          <?php echo get_partial('informationobject/event', $sf_data->getRaw('eventComponent')->getVarHolder()->getAll()); ?>

        </section>

        <?php echo $form->type
            ->help(__('A term that specifies the characteristics and general type of content of the resource. Assign as many types as are applicable. The Type of resource options are limited to the values in the MODS typeOfResource top-level element.'))
            ->label(__('Type of resource'))
            ->renderRow(); ?>

        <section>

          <h3><?php echo __('Add new child levels (if describing a collection)'); ?></h3>

          <table class="table table-bordered multiRow">
            <thead>
              <tr>
                <th style="width: 20%">
                  <?php echo __('Identifier'); ?>
                </th><th style="width: 80%">
                  <?php echo __('Title'); ?>
                </th>
              </tr>
            </thead><tbody>
              <tr>
                <td>
                  <input type="text" name="updateChildLevels[0][identifier]"/>
                </td><td>
                  <input type="text" name="updateChildLevels[0][title]"/>
                </td>
              </tr>
            </tbody>

            <tfoot>
              <tr>
                <td colspan="3"><a href="#" class="multiRowAddButton"><?php echo __('Add new'); ?></a></td>
              </tr>
            </tfoot>

          </table>

          <div class="description">
            <?php echo __('Use these two fields to add lower levels to a collection-level description. Click Add new to add as many child levels as necessary. identifier: enter a unique standard number or code that distinctively identifies the resource. Title: enter A word, phrase, character, or group of characters, normally appearing in a resource, that names it or the work contained in it.'); ?>
          </div>

        </section>

        <?php echo $form->language
            ->help(__('A designation of the language in which the content of a resource is expressed. Select as many languages as required.'))
            ->renderRow(['class' => 'form-autocomplete']); ?>

        <div class="form-item">
          <?php echo $form->subjectAccessPoints
              ->label(__('Subject'))
              ->renderLabel(); ?>
          <?php echo $form->subjectAccessPoints->render(['class' => 'form-autocomplete']); ?>
          <?php echo $form->subjectAccessPoints
              ->help(__('A term or phrase representing the primary topic(s) on which a work is focused. Search for an existing term in the Subjects taxonomy by typing the first few characters of the term name. Alternatively, type a new name to create and link to a new subject term.'))
              ->renderHelp(); ?>
          <?php if (QubitAcl::check(QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID), 'createTerm')) { ?>
            <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(['module' => 'term', 'action' => 'add', 'taxonomy' => url_for([QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID), 'module' => 'taxonomy'])]); ?> #name"/>
          <?php } ?>
          <input class="list" type="hidden" value="<?php echo url_for(['module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for([QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID), 'module' => 'taxonomy'])]); ?>"/>
        </div>

        <div class="form-item">
          <?php echo $form->placeAccessPoints
              ->label(__('Places'))
              ->renderLabel(); ?>
          <?php echo $form->placeAccessPoints->render(['class' => 'form-autocomplete']); ?>
          <?php if (QubitAcl::check(QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'createTerm')) { ?>
            <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(['module' => 'term', 'action' => 'add', 'taxonomy' => url_for([QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'module' => 'taxonomy'])]); ?> #name"/>
          <?php } ?>
          <input class="list" type="hidden" value="<?php echo url_for(['module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for([QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'module' => 'taxonomy'])]); ?>"/>
          <?php echo $form->placeAccessPoints
              ->help(__('Search for an existing term in the Places taxonomy by typing the first few characters of the term name. Alternatively, type a new term to create and link to a new place term.'))
              ->renderHelp(); ?>
        </div>

        <div class="form-item">
          <?php echo $form->nameAccessPoints
              ->label(__('Names'))
              ->renderLabel(); ?>
          <?php echo $form->nameAccessPoints->render(['class' => 'form-autocomplete']); ?>
          <?php if (QubitAcl::check(QubitActor::getRoot(), 'create')) { ?>
            <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(['module' => 'actor', 'action' => 'add']); ?> #authorizedFormOfName"/>
          <?php } ?>
          <input class="list" type="hidden" value="<?php echo url_for(['module' => 'actor', 'action' => 'autocomplete', 'showOnlyActors' => 'true']); ?>"/>
          <?php echo $form->nameAccessPoints
              ->help(__('"Choose provenance, author and other non-subject access points from the archival description, as appropriate. All access points must be apparent from the archival description to which they relate." (RAD 21.0B) The values in this field are drawn from the Authorized form of name field in authority records. Search for an existing name by typing the first few characters of the name. Alternatively, type a new name to create and link to a new authority record.'))
              ->renderHelp(); ?>
        </div>

        <?php echo render_field($form->accessConditions
            ->help(__('Information about restrictions imposed on access to a resource. See MODS accessCondition top-level element for more information on how to use this field.')), $resource, ['class' => 'resizable']); ?>

        <div class="form-item">
          <?php echo $form->repository->renderLabel(); ?>
          <?php echo $form->repository->render(['class' => 'form-autocomplete']); ?>
          <?php echo $form->repository
              ->help(__('Identifies the institution or repository holding the resource. Search for an existing repository name by typing the first few letters of the name. ALternatively, type a new name to create and link to a new repository record.'))
              ->renderHelp(); ?>
          <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(['module' => 'repository', 'action' => 'add']); ?> #authorizedFormOfName"/>
          <input class="list" type="hidden" value="<?php echo url_for($sf_data->getRaw('repoAcParams')); ?>"/>
        </div>

        <?php echo render_field($form->scopeAndContent
            ->help(__('An abstract, table of contents or description of the resource\'s scope and contents.'))
            ->label(__('Description')), $resource, ['class' => 'resizable']); ?>

      </fieldset> <!-- #elementsArea -->

      <?php echo get_partial('informationobject/adminInfo', ['form' => $form, 'resource' => $resource]); ?>

    </div>

    <?php echo get_partial('informationobject/editActions', ['resource' => (null !== $parent ? $parent : $resource)]); ?>

  </form>

<?php end_slot(); ?>
