<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo __('Edit %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php if ($object instanceof QubitInformationObject) { ?>
        <?php echo render_title(QubitInformationObject::getStandardsBasedInstance($object)); ?>
      <?php } else { ?>
        <?php echo render_title($object); ?>
      <?php } ?>
    </span>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'digitalobject', 'action' => 'edit'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="border border-bottom-0 rounded-0 rounded-top bg-white">
      <?php echo get_component('digitalobject', 'show', ['resource' => $resource, 'usageType' => QubitTerm::REFERENCE_ID]); ?>
    </div>

    <div class="accordion mb-3">
      <div class="accordion-item rounded-0">
        <h2 class="accordion-header" id="master-heading">
          <button class="accordion-button rounded-0" type="button" data-bs-toggle="collapse" data-bs-target="#master-collapse" aria-expanded="true" aria-controls="master-collapse">
            <?php echo __('Master'); ?>
          </button>
        </h2>
        <div id="master-collapse" class="accordion-collapse collapse show" aria-labelledby="master-heading">
          <div class="accordion-body">
            <?php if (isset($resource->name)) { ?>
              <div class="mb-3">
                <h3 class="fs-6 mb-2">
                  <?php echo __('Filename'); ?>
                </h3>
                <span class="text-muted">
                  <?php echo render_value($resource->name); ?>
                </span>
              </div>
            <?php } ?>

            <?php if (isset($resource->byteSize)) { ?>
              <div class="mb-3">
                <h3 class="fs-6 mb-2">
                  <?php echo __('Filesize'); ?>
                </h3>
                <span class="text-muted">
                  <?php echo hr_filesize($resource->byteSize); ?>
                </span>
              </div>
            <?php } ?>

            <?php echo render_field($form->mediaType); ?>

            <?php echo render_field($form->digitalObjectAltText->label(__('Alt text'))); ?>

            <?php if ($showCompoundObjectToggle) { ?>
              <?php echo render_field($form->displayAsCompound->label(__(
                  'View children as a compound %1%?',
                  ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]
              ))); ?>
            <?php } ?>

            <?php echo render_field($form->latitude->label(__('Latitude'))); ?>

            <?php echo render_field($form->longitude->label(__('Longitude'))); ?>
          </div>
        </div>
      </div>
      <?php foreach ($representations as $usageId => $representation) { ?>
        <div class="accordion-item">
          <h2 class="accordion-header" id="heading-<?php echo $usageId; ?>">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $usageId; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $usageId; ?>">
              <?php echo __('%1% representation', ['%1%' => QubitTerm::getById($usageId)]); ?>
            </button>
          </h2>
          <div id="collapse-<?php echo $usageId; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $usageId; ?>">
            <div class="accordion-body">
              <?php if (isset($representation)) { ?>

                <?php echo get_component('digitalobject', 'editRepresentation', ['resource' => $resource, 'representation' => $representation]); ?>

              <?php } else { ?>

                <?php echo render_field($form["repFile_{$usageId}"]->label(__(
                    'Select a %1% to upload',
                    ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]
                ))); ?>

                <?php if ($resource->canThumbnail()) { ?>
                  <?php echo render_field($form["generateDerivative_{$usageId}"]->label(__(
                      'Or auto-generate a new representation from master image'
                  ))); ?>
                <?php } ?>
              <?php } ?>
            </div>
          </div>
        </div>
      <?php } ?>
      <?php if (QubitTerm::VIDEO_ID == $resource->mediaTypeId || QubitTerm::AUDIO_ID == $resource->mediaTypeId) { ?>
      
        <?php foreach ($videoTracks as $usageId => $videoTrack) { ?>

          <?php if (QubitTerm::VIDEO_ID == $resource->mediaTypeId && QubitTerm::SUBTITLES_ID == $usageId) { ?>
            
            <?php echo include_partial('editSubtitles', ['resource' => $resource, 'subtitles' => $videoTrack, 'form' => $form, 'usageId' => $usageId]); ?>

          <?php } elseif (QubitTerm::SUBTITLES_ID != $usageId) { ?>

            <div class="accordion-item">
              <h2 class="accordion-header" id="heading-<?php echo $usageId; ?>">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $usageId; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $usageId; ?>">
                  <?php echo __('%1%', ['%1%' => QubitTerm::getById($usageId)]); ?>
                </button>
              </h2>
              <div id="collapse-<?php echo $usageId; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $usageId; ?>">
                <div class="accordion-body">
                  <?php if (isset($videoTrack)) { ?>
                    <?php echo get_component('digitalobject', 'editRepresentation', ['resource' => $resource, 'representation' => $videoTrack]); ?>
                  <?php } else { ?>
                    <?php echo render_field($form["trackFile_{$usageId}"]->label(__(
                        'Select a file to upload (.vtt|.srt)'
                    ))); ?>
                  <?php } ?>
                </div>
              </div>
            </div>             
          <?php } ?>
        <?php } ?>
      <?php } ?>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
        <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'digitalobject', 'action' => 'delete'], ['class' => 'btn atom-btn-outline-danger', 'role' => 'button']); ?></li>
      <?php } ?>
      <li><?php echo link_to(__('Cancel'), [$object, 'module' => $sf_request->module], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
