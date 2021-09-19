<h3 class="fs-6 mb-2">
  <?php echo __('Related resources'); ?>
</h3>

<div
  class="atom-table-modal"
  data-current-resource="<?php echo url_for([$resource]); ?>"
  data-required-fields="<?php echo $form->informationObject->renderId().','.$form->type->renderId(); ?>"
  data-delete-field-name="deleteRelations"
  data-iframe-error="<?php echo __('The following resources could not be created:'); ?>"
  <?php if (isset($resource->slug)) { ?>
    data-lazy-load-url="<?php echo url_for([
        'module' => 'sfIsaarPlugin',
        'action' => 'actorEvents',
        'slug' => $resource->slug,
        'limit' => sfConfig::get('app_hits_per_page', 10),
    ]); ?>"
  <?php } ?>
  data-iframe-error="<?php echo __('The following resources could not be created:'); ?>">
  <div class="alert alert-danger d-none load-error" role="alert">
    <?php echo __('Could not load relation data.'); ?>
  </div>

  <div class="table-responsive">
    <table class="table table-bordered mb-0">
      <thead class="table-light">
        <tr>
          <th style="width: 40%">
            <?php echo __('Title'); ?>
          </th>
          <th style="width: 30%">
            <?php echo __('Relationship'); ?>
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
          <td data-field-id="<?php echo $form->informationObject->renderId(); ?>"></td>
          <td data-field-id="<?php echo $form->type->renderId(); ?>"></td>
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
      </tbody>
      <tfoot>
        <tr>
          <td colspan="4">
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn atom-btn-white add-row">
                <i class="fas fa-plus me-1" aria-hidden="true"></i>
                <?php echo __('Add new'); ?>
              </button>
              <button type="button" class="btn atom-btn-white ms-auto show-more">
                <?php echo __('Show more'); ?>
              </button>
            </div>
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
                  $form->informationObject
                      ->label(__('Title of related resource'))
                      ->help(__(
                        '"Provide the unique identifiers/reference codes and/or titles for the'
                        .' related resources." (ISAAR 6.1) Select the title from the drop-down menu;'
                        .' enter the identifier or the first few letters to narrow the choices.'
                      )),
                  null,
                  ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
              );
          ?>

          <?php echo render_field($form->type->label(__('Nature of relationship'))->help(__(
              '"Describe the nature of the relationships between the corporate body, person or family'
              .' and the related resource." (ISAAR 6.3) Select the type of relationship from the drop-down'
              .' menu; these values are drawn from the Event Types taxonomy.'
          ))); ?>

          <?php echo render_field(
              $form->resourceType
                  ->label(__('Type of related resource'))
                  ->help(__(
                      '"Identify the type of related resources, e.g. Archival materials (fonds, record'
                      .' series, etc), archival description, finding aid, monograph, journal article, web'
                      .' site, photograph, museum collection, documentary film, oral history recording."'
                      .' (ISAAR 6.2) In the current version of the software, Archival material is provided'
                      .' as the only default value.'
                  )),
              null,
              ['disabled' => 'true']
          ); ?>

          <div class="date">
            <?php echo render_field($form->date->help(__(
                '"Provide any relevant dates for the related resources and/or the relationship between the'
                .' corporate body, person or family and the related resource." (ISAAR 6.4) Enter the date'
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
