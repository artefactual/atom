<h3 class="fs-6 mb-2">
  <?php echo __('Related corporate bodies, persons or families'); ?>
</h3>

<div
  class="atom-table-modal"
  data-current-resource="<?php echo url_for([$resource]); ?>"
  data-current-resource-text="<?php echo render_title($resource); ?>"
  data-required-fields="<?php echo $form->resource->renderId().','.$form->type->renderId(); ?>"
  data-delete-field-name="deleteRelations"
  data-iframe-error="<?php echo __('The following resources could not be created:'); ?>">
  <div class="alert alert-danger d-none load-error" role="alert">
    <?php echo __('Could not load relation data.'); ?>
  </div>

  <div class="table-responsive mb-3">
    <table class="table table-bordered mb-0">
      <thead class="table-light">
        <tr>
          <th style="width: 25%">
            <?php echo __('Name'); ?>
          </th>
          <th style="width: 15%">
            <?php echo __('Category'); ?>
          </th>
          <th style="width: 15%">
            <?php echo __('Type'); ?>
          </th>
          <th style="width: 15%">
            <?php echo __('Dates'); ?>
          </th>
          <th style="width: 30%">
            <?php echo __('Description'); ?>
          </th>
          <th>
            <span class="visually-hidden"><?php echo __('Actions'); ?></span>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr class="row-template d-none">
          <td data-field-id="<?php echo $form->resource->renderId(); ?>"></td>
          <td data-field-id="<?php echo $form->type->renderId(); ?>"></td>
          <td data-field-id="<?php echo $form->subType->renderId(); ?>"></td>
          <td data-field-id="<?php echo $form->date->renderId(); ?>"></td>
          <td data-field-id="<?php echo $form->description->renderId(); ?>"></td>
          <td class="text-nowrap">
            <button type="button" class="btn atom-btn-white me-1 edit-row">
              <i class="fas fa-fw fa-pencil-alt" aria-hidden="true"></i>
              <span class="visually-hidden"><?php echo __('Edit row'); ?></span>
            </button>
            <button type="button" class="btn atom-btn-white delete-row">
              <i class="fas fa-fw fa-times" aria-hidden="true"></i>
              <span class="visually-hidden"><?php echo __('Delete row'); ?></span>
            </button>
          </td>
        </tr>
        <?php foreach ($resource->getActorRelations() as $item) { ?>
          <tr id="<?php echo url_for([$item, 'module' => 'relation']); ?>">
            <td data-field-id="<?php echo $form->resource->renderId(); ?>">
              <?php if ($resource->id == $item->objectId) { ?>
                <?php echo render_title($item->subject); ?>
              <?php } else { ?>
                <?php echo render_title($item->object); ?>
              <?php } ?>
            </td>
            <td data-field-id="<?php echo $form->type->renderId(); ?>">
              <?php if (QubitTerm::ROOT_ID == $item->type->parentId) { ?>
                <?php echo render_value_inline($item->type); ?>
              <?php } else { ?>
                <?php echo render_title($item->type->parent); ?>
              <?php } ?>
            </td>
            <td data-field-id="<?php echo $form->subType->renderId(); ?>">
              <?php if (QubitTerm::ROOT_ID != $item->type->parentId) { ?>
                <?php if ($resource->id != $item->objectId) { ?>
                  <?php echo render_title($item->type).' '.render_title($resource); ?>
                <?php } elseif (
                    0 < count($converseTerms = QubitRelation::getBySubjectOrObjectId(
                        $item->type->id,
                        ['typeId' => QubitTerm::CONVERSE_TERM_ID]
                    ))
                ) { ?>
                  <?php echo render_title($converseTerms[0]->getOpposedObject($item->type))
                    .' '.
                    render_title($resource); ?>
                <?php } ?>
              <?php } ?>
            </td>
            <td data-field-id="<?php echo $form->date->renderId(); ?>">
              <?php echo render_value_inline(Qubit::renderDateStartEnd(
                  $item->date,
                  $item->startDate,
                  $item->endDate
              )); ?>
            </td>
            <td data-field-id="<?php echo $form->description->renderId(); ?>">
              <?php echo render_value_inline($item->description); ?>
            </td>
            <td class="text-nowrap">
              <button type="button" class="btn atom-btn-white me-1 edit-row">
                <i class="fas fa-fw fa-pencil-alt" aria-hidden="true"></i>
                <span class="visually-hidden"><?php echo __('Edit row'); ?></span>
              </button>
              <button type="button" class="btn atom-btn-white delete-row">
                <i class="fas fa-fw fa-times" aria-hidden="true"></i>
                <span class="visually-hidden"><?php echo __('Delete row'); ?></span>
              </button>
            </td>
          </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="6">
            <button type="button" class="btn atom-btn-white add-row">
              <i class="fas fa-plus me-1" aria-hidden="true"></i>
              <?php echo __('Add new'); ?>
            </button>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div 
    class="modal fade"
    data-bs-backdrop="static"
    tabindex="-1"
    aria-labelledby="related-authority-record-heading"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="h5 modal-title" id="related-authority-record-heading">
            <?php echo __('Related corporate body, person or family'); ?>
          </h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal">
            <span class="visually-hidden"><?php echo __('Close'); ?></span>
          </button>
        </div>

        <div class="modal-body pb-2">
          <div class="alert alert-danger d-none validation-error" role="alert">
            <?php echo __('Please complete all required fields.'); ?>
          </div>

          <?php echo $form->renderHiddenFields(); ?>

          <?php
              $extraInputs = '<input class="list" type="hidden" value="'
                  .url_for([
                      'module' => 'actor',
                      'action' => 'autocomplete',
                      'showOnlyActors' => 'true',
                  ])
                  .'">';
              echo render_field(
                  $form->resource
                      ->label(__('Authorized form of name'))
                      ->help(__(
                        '"Record the authorized form of name and any relevant unique identifiers,'
                        .' including the authority record identifier, for the related entity."'
                        .' (ISAAR 5.3.1) Select the name from the drop-down menu; enter the'
                        .' first few letters to narrow the choices.'
                      )),
                  null,
                  ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
              );
          ?>

          <?php echo render_field($form->type->label(__('Category of relationship'))->help(__(
              '"Purpose: To identify the general category of relationship between the entity being'
              .' described and another corporate body, person or family." (ISAAR 5.3.2). Select a'
              .' category from the drop-down menu: hierarchical, temporal, family or associative.'
          ))); ?>

          <?php
              $extraInputs = '<input class="list" type="hidden" value="'
                  .url_for([
                      'module' => 'term',
                      'action' => 'autocomplete',
                      'taxonomy' => url_for([
                          QubitTaxonomy::getById(QubitTaxonomy::ACTOR_RELATION_TYPE_ID),
                          'module' => 'taxonomy',
                      ]),
                      'addWords' => isset($resource->id)
                          ? sfOutputEscaper::unescape(render_title($resource))
                          : '',
                  ])
                  .'">';
              echo render_field(
                  $form->subType
                      ->label(__('Relationship type'))
                      ->help(__(
                        '"Select a descriptive term from the drop-down menu to clarify'
                        .' the type of relationship between these two actors."'
                      )),
                  null,
                  ['class' => 'form-autocomplete', 'disabled' => 'disabled', 'extraInputs' => $extraInputs]
              );
          ?>

          <?php echo render_field($form->description->label(__('Description of relationship'))->help(__(
              '"Record a precise description of the nature of the relationship between the entity'
              .' described in this authority record and the other related entity....Record in the'
              .' Rules and/or conventions element (5.4.3) any classification scheme used as a source'
              .' of controlled vocabulary terms to describe the relationship. A narrative description'
              .' of the history and/or nature of the relationship may also be provided here." (ISAAR 5.3.3).'
              .' Note that the text entered in this field will also appear in the related authority record.'
          ))); ?>

          <div class="date">
            <?php echo render_field($form->date->help(__(
                '"Record when relevant the commencement date of the relationship or succession date and,'
                .' when relevant, the cessation date of the relationship." (ISAAR 5.3.4) Enter the date'
                .' as you would like it to appear in the show page for the authority record, using qualifiers'
                .' and/or typographical symbols to express uncertainty if desired.'
            ))); ?>
            <?php echo render_field($form->startDate); ?>
            <?php echo render_field($form->endDate); ?>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <?php echo __('Cancel'); ?>
          </button>
          <button type="button" class="btn btn-success modal-submit">
            <?php echo __('Submit'); ?>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
