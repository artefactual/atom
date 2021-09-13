<div class="accordion-item">
  <h2 class="accordion-header" id="admin-heading">
    <button
      class="accordion-button collapsed"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#admin-collapse"
      aria-expanded="false"
      aria-controls="admin-collapse">
      <?php echo __('Administration area'); ?>
    </button>
  </h2>
  <div id="admin-collapse" class="accordion-collapse collapse" aria-labelledby="admin-heading">
    <div class="accordion-body">
      <div class="row">

        <div class="col-md-6">
          <div class="mb-3">
            <h3 class="fs-6 mb-2">
              <?php echo __('Source language'); ?>
            </h3>
            <span class="text-muted">
              <?php if (isset($resource->sourceCulture)) { ?>
                <?php if ($sf_user->getCulture() == $resource->sourceCulture) { ?>
                  <?php echo format_language($resource->sourceCulture); ?>
                <?php } else { ?>
                  <div class="default-translation">
                    <?php echo link_to(
                        format_language($resource->sourceCulture),
                        ['sf_culture' => $resource->sourceCulture]
                            + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()
                    ); ?>
                  </div>
                <?php } ?>
              <?php } else { ?>
                <?php echo format_language($sf_user->getCulture()); ?>
              <?php } ?>
            </span>
          </div>

          <?php if (isset($resource->updatedAt)) { ?>
            <div class="mb-3">
              <h3 class="fs-6 mb-2">
                <?php echo __('Last updated'); ?>
              </h3>
              <span class="text-muted">
                <?php echo format_date($resource->updatedAt, 'f'); ?>
              </span>
            </div>
          <?php } ?>

          <?php if (0 < count($keymapEntries = $resource->fetchAllKeymapEntries())) { ?>
            <div class="mb-3">
              <h3 class="fs-6 mb-2">
                <?php echo __('Source name'); ?>
              </h3>
              <span class="text-muted">
                <?php foreach ($keymapEntries as $keymap) { ?>
                  <p><?php echo $keymap->sourceName; ?></p>
                <?php } ?>
              </span>
            </div>
          <?php } ?>
        </div>

        <div class="col-md-6">
          <?php echo render_field($form->displayStandard->label(__('Display standard'))); ?>
          <?php echo render_field($form->displayStandardUpdateDescendants->label(__(
              'Make this selection the new default for existing children'
          ))); ?>
        </div>

      </div>
    </div>
  </div>
</div>
