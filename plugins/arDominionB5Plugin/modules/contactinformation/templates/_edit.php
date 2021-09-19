<h3 class="fs-6 mb-2">
  <?php echo __('Related contact information'); ?>
</h3>

<div
  class="atom-table-modal"
  data-current-resource="<?php echo url_for([$resource]); ?>"
  data-delete-field-name="deleteContactInformations"
  data-checked-text="<?php echo __('Yes'); ?>"
  data-unchecked-text="<?php echo __('No'); ?>"
  data-iframe-error="<?php echo __('The following resources could not be created:'); ?>">
  <div class="alert alert-danger d-none load-error" role="alert">
    <?php echo __('Could not load contact information data.'); ?>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered mb-0">
      <thead class="table-light">
        <tr>
          <th style="width: 80%">
            <?php echo __('Contact person'); ?>
          </th>
          <th style="width: 20%">
            <?php echo __('Primary'); ?>
          </th>
          <th>
            <span class="visually-hidden"><?php echo __('Actions'); ?></span>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr class="row-template d-none">
          <td data-field-id="<?php echo $form->contactPerson->renderId(); ?>"></td>
          <td data-field-id="<?php echo $form->primaryContact->renderId(); ?>"></td>
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
        <?php foreach ($resource->contactInformations as $item) { ?>
          <tr id="<?php echo url_for([$item, 'module' => 'contactinformation']); ?>">
            <td data-field-id="<?php echo $form->contactPerson->renderId(); ?>">
              <?php echo render_title($item->contactPerson); ?>
            </td>
            <td data-field-id="<?php echo $form->primaryContact->renderId(); ?>">
              <?php echo $item->primaryContact ? __('Yes') : __('No'); ?>
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
          <td colspan="3">
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
    aria-labelledby="related-contact-information-heading"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="h5 modal-title" id="related-contact-information-heading">
            <?php echo __('Related contact information'); ?>
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
              <?php echo render_field($form->primaryContact->label(__('Primary contact'))); ?>
              <?php echo render_field($form->contactPerson->label(__('Contact person'))); ?>
              <?php echo render_field($form->telephone->label(__('Phone'))); ?>
              <?php echo render_field($form->fax->label(__('Fax'))); ?>
              <?php echo render_field($form->email->label(__('Email'))); ?>
              <?php echo render_field($form->website->label(__('URL'))); ?>
            </div>
            <div class="tab-pane fade" id="pills-phys" role="tabpanel" aria-labelledby="pills-phys-tab">
              <?php echo render_field($form->streetAddress->label(__('Street address'))); ?>
              <?php echo render_field($form->region->label(__('Region/province'))); ?>
              <?php echo render_field($form->countryCode->label(__('Country'))); ?>
              <?php echo render_field($form->postalCode->label(__('Postal code'))); ?>
              <?php echo render_field($form->city->label(__('City'))); ?>
              <?php echo render_field($form->latitude->label(__('Latitude'))); ?>
              <?php echo render_field($form->longitude->label(__('Longitude'))); ?>
            </div>
            <div class="tab-pane fade" id="pills-other" role="tabpanel" aria-labelledby="pills-other-tab">
              <?php echo render_field($form->contactType->label(__('Contact type'))); ?>
              <?php echo render_field($form->note->label(__('Note'))); ?>
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
