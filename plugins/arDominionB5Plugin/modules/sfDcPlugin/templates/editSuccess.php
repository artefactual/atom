<?php decorate_with('layout_2col.php'); ?>
<?php use_helper('Date'); ?>

<?php slot('sidebar'); ?>

  <?php include_component('repository', 'contextMenu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <?php echo get_component('informationobject', 'descriptionHeader', ['resource' => $resource, 'title' => (string) $dc, 'hideLevelOfDescription' => true]); ?>

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
            <?php echo render_field($form->identifier
                ->help(__('The unambiguous reference code used to uniquely identify this resource.'))
                ->label(__('Identifier').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
            ); ?>

            <?php echo get_partial(
                'informationobject/identifierOptions',
                ['mask' => $mask, 'hideAltIdButton' => true]
            ); ?>

            <?php echo render_field($form->title
                ->help(__('The name given to this resource.'))
                ->label(__('Title').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>'), $resource); ?>

            <?php echo get_partial('dcNames', $sf_data->getRaw('dcNamesComponent')->getVarHolder()->getAll()); ?>

            <?php echo get_partial('dcDates', $sf_data->getRaw('dcDatesComponent')->getVarHolder()->getAll()); ?>

            <?php echo render_field($form->scopeAndContent
                ->help(__('An abstract, table of contents or description of the resource\'s scope and contents.'))
                ->label(__('Description')), $resource); ?>

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
                        'The topic of the resource. Search for an existing term in the Subject taxonomy'
                        .' by typing the first few characters of the term name. Alternatively, type a new'
                        .' name to create and link to a new subject term.'
                    )),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>

            <?php echo render_field($form->type
                ->help(__('<p>The nature or genre of the resource.</p><p>Assign as many types as applicable. The <em>Type</em> options are limited to the DCMI Type vocabulary.</p><p>Assign the <em>Collection</em> value if this resource is the top-level for a set of lower-level (child) resources.</p><p>Please note: if this resource is linked to a digital object, the <em>image</em>, <em>text</em>, <em>sound</em> or <em>moving image</em> types are added automatically upon output, so do not duplicate those values here.</p>'))
            ); ?>

            <h3 class="fs-6 mb-2">
              <?php echo __('Add new child levels (if describing a collection)'); ?>
            </h3>

            <div class="table-responsive mb-2">
              <table class="table table-bordered mb-0 multi-row">
                <thead class="table-light">
                  <tr>
                    <th id="child-identifier-head" style="width: 20%">
                      <?php echo __('Identifier'); ?>
                    </th>
                    <th id="child-title-head" style="width: 80%">
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
                  '<p><strong>Child levels</strong><br/>Use these two fields to add lower'
                  .' levels to a collection level description. Click <em>Add new</em> to'
                  .' create as many child levels as necessary.</p><p>These fields can also'
                  .' be used to add any number of intermediate levels of description (e.g.'
                  .' series, file, etc) between the top and bottom levels in a descriptive'
                  .' hierarchy. Use the hierarchy treeview to re-order hierarchy levels as'
                  .' necessary.</p><p><em>Identifier</em>: The unambiguous reference code'
                  .' used to uniquely identify the child-level resource.</p><p><em>Title</em>:'
                  .' The name given to the child-level resource.</p>'
              ); ?>
            </div>

            <?php echo render_field($form->extentAndMedium
                ->help(__('<p>The file format, physical medium, or dimensions of the resource.</p><p>Please note: if this resource is linked to a digital object, the Internet Media Types (MIME) will be added automatically upon output, so don\'t duplicate those values here.</p>'))
                ->label(__('Format')), $resource); ?>

            <?php echo render_field($form->locationOfOriginals
                ->help(__('Related material(s) from which this resource is derived.'))
                ->label(__('Source')), $resource); ?>

            <?php echo render_field(
                $form->language->help(__('Language(s) of this resource.')),
                null,
                ['class' => 'form-autocomplete']
            ); ?>

            <?php echo render_field(
                $form->repository
                    ->label(
                        __('Relation (isLocatedAt)')
                        .' <span class="form-required" title="'
                        .__(
                            'This is a mandatory element for this resource or one of its'
                            .' higher descriptive levels (if part of a collection hierarchy).'
                        )
                        .'">*</span>'
                    )
                    ->help(__(
                        '<p>The name of the organization which has custody of the resource.</p>'
                        .'<p>Search for an existing name in the organization records by typing the'
                        .' first few characters of the name. Alternatively, type a new name to create'
                        .' and link to a new organization record.</p>'
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
                    $form->placeAccessPoints->label(__('Coverage (spatial)'))->help(__(
                        '<p>The name of a place or geographic area which is a topic of the resource'
                        .' or relevant to its jurisdiction.</p><p>Search for an existing term in the'
                        .' Place taxonomy by typing the first few characters of the place name.'
                        .' Alternatively, type a new name to create and link to a new place.</p><p>Please'
                        .' note: if you entered a place of creation, publication or contribution that will'
                        .' be output automatically, so don’t repeat that place name here.</p>'
                    )),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>

            <?php echo render_field($form->accessConditions
                ->help(__('Information about rights held in and over the resource (e.g. copyright, access conditions, etc.).'))
                ->label(__('Rights')), $resource); ?>
          </div>
        </div>
      </div>
      <?php echo get_partial('informationobject/adminInfo', ['form' => $form, 'resource' => $resource]); ?>
    </div>

    <?php echo get_partial('informationobject/editActions', ['resource' => (null !== $parent ? $parent : $resource)]); ?>

  </form>

<?php end_slot(); ?>
