<h3 class="fs-6 mb-2">
  <?php echo __('Related donors'); ?>
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
          <th style="width: 100%">
            <?php echo __('Name'); ?>
          </th>
          <th>
            <span class="visually-hidden"><?php echo __('Actions'); ?></span>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr class="row-template d-none">
          <td data-field-id="<?php echo $form->resource->renderId(); ?>"></td>
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
        <?php foreach ($relatedDonorRecord as $item) { ?>
          <tr id="<?php echo url_for([$item, 'module' => 'accession', 'action' => 'relatedDonor']); ?>">
            <td data-field-id="<?php echo $form->resource->renderId(); ?>">
              <?php echo render_title($item->object); ?>
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
          <td colspan="2">
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
    aria-labelledby="related-donor-heading"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="h5 modal-title" id="related-donor-heading">
            <?php echo __('Related donor record'); ?>
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
                  .url_for(['module' => 'donor', 'action' => 'autocomplete'])
                  .'"><input class="add" type="hidden" data-link-existing="true" value="'
                  .url_for(['module' => 'donor', 'action' => 'add'])
                  .' #authorizedFormOfName">';
              echo render_field(
                  $form->resource
                      ->label(__('Name'))
                      ->help(__(
                          'This is the legal entity field and provides the contact'
                          .' information for the person(s) or the institution that donated'
                          .' or transferred the materials. It has the option of multiple'
                          .' instances and provides the option of creating more than one'
                          .' contact record using the same form.'
                      )),
                  null,
                  ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
              );
          ?>

          <h5>
            <?php echo __('Primary contact information'); ?>
          </h5>

          <ul class="nav nav-pills mb-3 d-flex gap-2" role="tablist">
            <li class="nav-item" role="presentation">
              <button
                class="btn atom-btn-white active-primary text-wrap active"
                id="pills-main-tab"
                data-bs-toggle="pill"
                data-bs-target="#pills-main"
                type="button"
                role="tab"
                aria-controls="pills-main"
                aria-selected="true">
                <?php echo __('Main'); ?>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button
                class="btn atom-btn-white active-primary text-wrap"
                id="pills-phys-tab"
                data-bs-toggle="pill"
                data-bs-target="#pills-phys"
                type="button"
                role="tab"
                aria-controls="pills-phys"
                aria-selected="false">
                <?php echo __('Physical location'); ?>
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button
                class="btn atom-btn-white active-primary text-wrap"
                id="pills-other-tab"
                data-bs-toggle="pill"
                data-bs-target="#pills-other"
                type="button"
                role="tab"
                aria-controls="pills-other"
                aria-selected="false">
                <?php echo __('Other details'); ?>
              </button>
            </li>
          </ul>

          <div class="tab-content">
            <div class="tab-pane fade show active" id="pills-main" role="tabpanel" aria-labelledby="pills-main-tab">
              <?php echo render_field($form->contactPerson); ?>
              <?php echo render_field($form->telephone); ?>
              <?php echo render_field($form->fax); ?>
              <?php echo render_field($form->email); ?>
              <?php echo render_field($form->website->label(__('URL'))); ?>
            </div>
            <div class="tab-pane fade" id="pills-phys" role="tabpanel" aria-labelledby="pills-phys-tab">
              <?php echo render_field($form->streetAddress); ?>
              <?php echo render_field($form->region->label(__('Region/province'))); ?>
              <?php echo render_field($form->countryCode->label(__('Country'))); ?>
              <?php echo render_field($form->postalCode); ?>
              <?php echo render_field($form->city); ?>
              <?php echo render_field($form->latitude); ?>
              <?php echo render_field($form->longitude); ?>
            </div>
            <div class="tab-pane fade" id="pills-other" role="tabpanel" aria-labelledby="pills-other-tab">
              <?php echo render_field($form->contactType); ?>
              <?php echo render_field($form->note); ?>
            </div>
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
