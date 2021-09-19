<h3 class="fs-6 mb-2">
  <?php echo __('Related resources'); ?>
</h3>

<div
  class="atom-table-modal"
  data-current-resource="<?php echo url_for([$resource]); ?>"
  data-required-fields="<?php echo $form->resource->renderId(); ?>"
  data-delete-field-name="deleteRelations"
  data-iframe-error="<?php echo __('The following resources could not be created:'); ?>">
  <div class="alert alert-danger d-none load-error" role="alert">
    <?php echo __('Could not load relation data.'); ?>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered mb-0">
      <thead class="table-light">
        <tr>
          <th style="width: 30%">
            <?php echo __('Identifier/title'); ?>
          </th>
          <th style="width: 40%">
            <?php echo __('Nature of relationship'); ?>
          </th>
          <th style="width: 30%">
            <?php echo __('Dates'); ?>
          </th>
          <th>
            <span class="visually-hidden"><?php echo __('Actions'); ?></span>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr class="row-template d-none">
          <td data-field-id="<?php echo $form->resource->renderId(); ?>"></td>
          <td data-field-id="<?php echo $form->description->renderId(); ?>"></td>
          <td data-field-id="<?php echo $form->date->renderId(); ?>"></td>
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
        <?php foreach ($isdf->relatedResource as $item) { ?>
          <tr id="<?php echo url_for([$item, 'module' => 'relation']); ?>">
            <td data-field-id="<?php echo $form->resource->renderId(); ?>">
              <?php echo render_title($item->object); ?>
            </td>
            <td data-field-id="<?php echo $form->description->renderId(); ?>">
              <?php echo render_value_inline($item->description); ?>
            </td>
            <td data-field-id="<?php echo $form->date->renderId(); ?>">
              <?php echo render_value_inline(Qubit::renderDateStartEnd(
                  $item->date,
                  $item->startDate,
                  $item->endDate
              )); ?>
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
          <td colspan="4">
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
    aria-labelledby="related-resource-heading"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="h5 modal-title" id="related-resource-heading">
            <?php echo __('Related resource'); ?>
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
                  .url_for(['module' => 'informationobject', 'action' => 'autocomplete'])
                  .'">';
              echo render_field(
                  $form->resource
                      ->label(__('Title'))
                      ->help(__(
                        'Select the title from the drop-down menu; enter the identifier'
                        .' or the first few letters to narrow the choices. (ISDF 6.1)'
                      )),
                  null,
                  ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
              );
          ?>

          <?php echo render_field($form->description->label(__('Nature of relationship'))); ?>

          <div class="date">
            <?php echo render_field($form->date); ?>
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
