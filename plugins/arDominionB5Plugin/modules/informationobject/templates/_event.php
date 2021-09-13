<div
  class="atom-table-modal"
  data-current-resource="<?php echo url_for([$resource]); ?>"
  data-required-fields="<?php echo $form->type->renderId(); ?>"
  data-delete-field-name="deleteEvents"
  data-iframe-error="<?php echo __('The following resources could not be created:'); ?>">
  <div class="alert alert-danger d-none load-error" role="alert">
    <?php echo __('Could not load event data.'); ?>
  </div>

  <div class="table-responsive mb-3">
    <table class="table table-bordered mb-0">
      <thead class="table-light">
        <tr>
          <th style="width: 30%">
            <?php echo __('Name'); ?>
          </th>
          <th style="width: 20%">
            <?php echo __('Role/event'); ?>
          </th>
          <th style="width: 25%">
            <?php echo __('Place'); ?>
          </th>
          <th style="width: 25%">
            <?php echo __('Date(s)'); ?>
          </th>
          <th>
            <span class="visually-hidden"><?php echo __('Actions'); ?></span>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr class="row-template d-none">
          <td data-field-id="<?php echo $form->actor->renderId(); ?>"></td>
          <td data-field-id="<?php echo $form->type->renderId(); ?>"></td>
          <td data-field-id="<?php echo $form->place->renderId(); ?>"></td>
          <td data-field-id="<?php echo $form->date->renderId(); ?>"></td>
          <td class="text-nowrap">
            <?php if (!isset($sf_request->source)) { ?>
              <button type="button" class="btn atom-btn-white me-1 edit-row">
                <i class="fas fa-fw fa-pencil-alt" aria-hidden="true"></i>
                <span class="visually-hidden"><?php echo __('Edit row'); ?></span>
              </button>
            <?php } ?>
            <button type="button" class="btn atom-btn-white delete-row">
              <i class="fas fa-fw fa-times" aria-hidden="true"></i>
              <span class="visually-hidden"><?php echo __('Delete row'); ?></span>
            </button>
          </td>
        </tr>
        <?php foreach ($resource->eventsRelatedByobjectId as $item) { ?>
          <tr id="<?php echo url_for([$item, 'module' => 'event']); ?>">
            <td data-field-id="<?php echo $form->actor->renderId(); ?>">
              <?php if (isset($item->actor)) { ?>
                <?php echo render_title($item->actor); ?>
              <?php } ?>
            </td>
            <td data-field-id="<?php echo $form->type->renderId(); ?>">
              <?php echo render_value_inline($item->type); ?>
            </td>
            <td data-field-id="<?php echo $form->place->renderId(); ?>">
              <?php if (null !== $relation = QubitObjectTermRelation::getOneByObjectId($item->id)) { ?>
                <?php echo render_value_inline($relation->term); ?>
              <?php } ?>
            </td>
            <td data-field-id="<?php echo $form->date->renderId(); ?>">
              <?php echo render_value_inline(Qubit::renderDateStartEnd(
                  $item->getDate(['cultureFallback' => true]),
                  $item->startDate,
                  $item->endDate
              )); ?>
            </td>
            <td class="text-nowrap">
              <?php if (!isset($sf_request->source)) { ?>
                <button type="button" class="btn atom-btn-white me-1 edit-row">
                  <i class="fas fa-fw fa-pencil-alt" aria-hidden="true"></i>
                  <span class="visually-hidden"><?php echo __('Edit row'); ?></span>
                </button>
              <?php } ?>
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
          <td colspan="5">
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
    aria-labelledby="related-events-heading"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="h5 modal-title" id="related-events-heading">
            <?php echo __('Event'); ?>
          </h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal">
            <span class="visually-hidden"><?php echo __('Close'); ?></span>
          </button>
        </div>

        <div class="modal-body pb-2">
          <?php echo $form->renderHiddenFields(); ?>

          <?php
              $extraInputs = '<input class="list" type="hidden" value="'
                  .url_for(['module' => 'actor', 'action' => 'autocomplete'])
                  .'">';
              if (QubitAcl::check(QubitActor::getRoot(), 'create')) {
                  $extraInputs .= '<input class="add" type="hidden"'
                      .' data-link-existing="true" value="'
                      .url_for(['module' => 'actor', 'action' => 'add'])
                      .' #authorizedFormOfName">';
              }
              echo render_field(
                  $form->actor->label(__('Actor name')),
                  null,
                  ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
              );
          ?>

          <?php echo render_field($form->type->label(__('Event type'))); ?>

          <?php
              $extraInputs = '<input class="list" type="hidden" value="'
                  .url_for([
                      'module' => 'term',
                      'action' => 'autocomplete',
                      'taxonomy' => url_for([
                          QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID),
                          'module' => 'taxonomy',
                      ]),
                  ])
                  .'">';
              if (QubitAcl::check(QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID), 'createTerm')) {
                  $extraInputs .= '<input class="add" type="hidden" data-link-existing="true" value="'
                      .url_for([
                          'module' => 'term',
                          'action' => 'add',
                          'taxonomy' => url_for([
                              QubitTaxonomy::getById(QubitTaxonomy::PLACE_ID),
                              'module' => 'taxonomy',
                          ]),
                      ])
                      .' #name">';
              }
              echo render_field(
                  $form->place,
                  null,
                  ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
              );
          ?>

          <div class="date">
            <?php echo render_field($form->date); ?>
            <?php echo render_field($form->startDate); ?>
            <?php echo render_field($form->endDate); ?>
          </div>

          <?php echo render_field($form->description->label(__('Event note'))); ?>
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
