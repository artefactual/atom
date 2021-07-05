<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1 class="multiline">
    <?php echo __('Edit %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]); ?>

    <?php if ($resource->object instanceof QubitInformationObject) { ?>
      <span class="sub"><?php echo render_title(QubitInformationObject::getStandardsBasedInstance($object)); ?></span>
    <?php } elseif ($resource->object instanceof QubitActor) { ?>
      <span class="sub"><?php echo render_title($object); ?></span>
    <?php } ?>
  </h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'digitalobject', 'action' => 'edit'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <section id="content" class="border-bottom-0 rounded-0 rounded-top">
      <?php echo get_component('digitalobject', 'show', ['resource' => $resource, 'usageType' => QubitTerm::REFERENCE_ID]); ?>
    </section>

    <div class="accordion" id="digitalobject-edit">
      <div class="accordion-item rounded-0">
        <h2 class="accordion-header" id="master-heading">
          <button class="accordion-button rounded-0" type="button" data-bs-toggle="collapse" data-bs-target="#master-collapse" aria-expanded="true" aria-controls="master-collapse">
            <?php echo __('Master'); ?>
          </button>
        </h2>
        <div id="master-collapse" class="accordion-collapse collapse show" aria-labelledby="master-heading" data-bs-parent="#digitalobject-edit">
          <div class="accordion-body">
            <?php echo render_show(__('Filename'), render_value($resource->name)); ?>

            <?php echo render_show(__('Filesize'), hr_filesize($resource->byteSize)); ?>

            <?php echo $form->mediaType->renderRow(); ?>

            <?php echo $form->digitalObjectAltText->label(__('Alt text'))->renderRow(); ?>

            <?php if ($showCompoundObjectToggle) { ?>
              <?php echo $form->displayAsCompound
                ->label(__('View children as a compound %1%?', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]))
                ->renderRow(); ?>
            <?php } ?>

            <?php echo $form->latitude->label('Latitude')->renderRow(); ?>

            <?php echo $form->longitude->label('Longitude')->renderRow(); ?>
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
          <div id="collapse-<?php echo $usageId; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $usageId; ?>" data-bs-parent="#digitalobject-edit">
            <div class="accordion-body">
              <?php if (isset($representation)) { ?>

                <?php echo get_component('digitalobject', 'editRepresentation', ['resource' => $resource, 'representation' => $representation]); ?>

              <?php } else { ?>

                <?php echo $form["repFile_{$usageId}"]
                    ->label(__('Select a %1% to upload', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]))
                    ->renderRow(); ?>

                <?php if ($resource->canThumbnail()) { ?>
                  <?php echo $form["generateDerivative_{$usageId}"]
                    ->label('Or auto-generate a new representation from master image')
                    ->renderRow(); ?>
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
              <div id="collapse-<?php echo $usageId; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $usageId; ?>" data-bs-parent="#digitalobject-edit">
                <div class="accordion-body">
                  <?php if (isset($videoTrack)) { ?>
                    
                    <?php echo get_component('digitalobject', 'editRepresentation', ['resource' => $resource, 'representation' => $videoTrack]); ?>

                  <?php } else { ?>

                    <?php echo $form["trackFile_{$usageId}"]
                        ->label(__('Select a file to upload (.vtt|.srt)'))
                        ->renderRow(); ?>
                
                  <?php } ?>
                </div>
              </div>
            </div>             
          <?php } ?>
        <?php } ?>
      <?php } ?>
    </div>

    <section class="actions">
      <ul>
        <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
          <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'digitalobject', 'action' => 'delete'], ['class' => 'c-btn c-btn-delete']); ?></li>
        <?php } ?>
        <li><?php echo link_to(__('Cancel'), [$object, 'module' => $sf_request->module], ['class' => 'c-btn']); ?></li>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
