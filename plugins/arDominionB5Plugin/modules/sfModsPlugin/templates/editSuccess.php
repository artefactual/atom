<?php decorate_with('layout_2col.php'); ?>
<?php use_helper('Date'); ?>

<?php slot('sidebar'); ?>

  <?php include_component('repository', 'contextMenu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <?php echo get_component('informationobject', 'descriptionHeader', ['resource' => $resource, 'title' => (string) $mods]); ?>

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
        <h2 class="accordion-header" id="elements-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#elements-collapse" aria-expanded="false" aria-controls="elements-collapse">
            <?php echo __('Elements area'); ?>
          </button>
        </h2>
        <div id="elements-collapse" class="accordion-collapse collapse" aria-labelledby="elements-heading">
          <div class="accordion-body">
            <?php echo render_field($form->identifier->help(__(
                'Contains a unique standard number or code that distinctively identifies a resource.'
            ))); ?>

            <?php echo get_partial(
                'informationobject/identifierOptions',
                ['mask' => $mask, 'hideAltIdButton' => true]
            ); ?>

            <?php echo render_field($form->title
                ->help(__('A word, phrase, character, or group of characters, normally appearing in a resource, that names it or the work contained in it. Choice and format of titles should be governed by a content standard such as the Anglo-American Cataloging Rules, 2nd edition (AACR2), Cataloguing Cultural Objects (CCO), or Describing Archives: A Content Standard (DACS). Details such as capitalization, choosing among the forms of titles presented on an item, and use of abbreviations should be determined based on the rules in a content standard. One standard should be chosen and used consistently for all records in a set.')), $resource); ?>

            <h3 class="fs-6 mb-2">
              <?php echo __('Names and origin info'); ?>
            </h3>

            <?php echo get_partial(
                'informationobject/event',
                $sf_data->getRaw('eventComponent')->getVarHolder()->getAll()
            ); ?>

            <?php echo render_field($form->type
                ->help(__('A term that specifies the characteristics and general type of content of the resource. Assign as many types as are applicable. The Type of resource options are limited to the values in the MODS typeOfResource top-level element.'))
                ->label(__('Type of resource'))
            ); ?>

            <h3 class="fs-6 mb-2">
              <?php echo __('Add new child levels (if describing a collection)'); ?>
            </h3>

            <div class="table-responsive mb-2">
              <table class="table table-bordered mb-0 multi-row">
                <thead class="table-light">
		  <tr>
                    <th id="child-identifier-head" class="w-20">
                      <?php echo __('Identifier'); ?>
                    </th>
                    <th id="child-title-head" class="w-80">
                      <?php echo __('Title'); ?>
                    </th>
                    <th>
                      <span class="visually-hidden"><?php echo __('Delete'); ?></span>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <input
                        type="text"
                        name="updateChildLevels[0][identifier]"
                        aria-labelledby="child-identifier-head"
                        aria-describedby="child-table-help"
                        class="form-control">
                    </td>
                    <td>
                    <input
                        type="text"
                        name="updateChildLevels[0][title]"
                        aria-labelledby="child-identifier-title"
                        aria-describedby="child-table-help"
                        class="form-control">
                    </td>
                    <td>
                      <button type="button" class="multi-row-delete btn atom-btn-white">
                        <i class="fas fa-times" aria-hidden="true"></i>
                        <span class="visually-hidden"><?php echo __('Delete row'); ?></span>
                      </button>
                    </td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="3">
                      <button type="button" class="multi-row-add btn atom-btn-white">
                        <i class="fas fa-plus me-1" aria-hidden="true"></i>
                        <?php echo __('Add new'); ?>
                      </button>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <div class="form-text mb-3" id="child-table-help">
              <?php echo __(
                  'Use these two fields to add lower levels to a collection-level description. Click Add new to add as many child levels as necessary. Identifier: enter a unique standard number or code that distinctively identifies the resource. Title: enter A word, phrase, character, or group of characters, normally appearing in a resource, that names it or the work contained in it.'
              ); ?>
            </div>

            <?php echo render_field(
                $form->language->help(__(
                    'A designation of the language in which the content of a resource is expressed. Select as many languages as required.'
                )),
                null,
                ['class' => 'form-autocomplete']
            ); ?>

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
                    $form->subjectAccessPoints->label(__('Subject'))->help(__(
                        'A term or phrase representing the primary topic(s) on which a work is focused. Search for an existing term in the Subjects taxonomy by typing the first few characters of the term name. Alternatively, type a new name to create and link to a new subject term.'
                    )),
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
                    $form->placeAccessPoints->label(__('Places'))->help(__(
                        'Search for an existing term in the Places taxonomy by typing the first few characters of the term name. Alternatively, type a new term to create and link to a new place term.'
                    )),
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
                    $form->nameAccessPoints->label(__('Names'))->help(__(
                        '"Choose provenance, author and other non-subject access points from the archival description, as appropriate. All access points must be apparent from the archival description to which they relate." (RAD 21.0B) The values in this field are drawn from the Authorized form of name field in authority records. Search for an existing name by typing the first few characters of the name. Alternatively, type a new name to create and link to a new authority record.'
                    )),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>

            <?php echo render_field(
                $form->accessConditions->help(__(
                    'Information about restrictions imposed on access to a resource. See MODS accessCondition top-level element for more information on how to use this field.'
                )),
                $resource
            ); ?>

            <?php echo render_field(
                $form->repository->help(__(
                    'Identifies the institution or repository holding the resource. Search for an existing repository name by typing the first few letters of the name. Alternatively, type a new name to create and link to a new repository record.'
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

            <?php echo render_field(
                $form->scopeAndContent
                    ->help(__('An abstract, table of contents or description of the resource\'s scope and contents.'))
                    ->label(__('Description')),
                $resource
            ); ?>
          </div>
        </div>
      </div>
      <?php echo get_partial('informationobject/adminInfo', ['form' => $form, 'resource' => $resource]); ?>
    </div>

    <?php echo get_partial('informationobject/editActions', ['resource' => (null !== $parent ? $parent : $resource)]); ?>

  </form>

<?php end_slot(); ?>
