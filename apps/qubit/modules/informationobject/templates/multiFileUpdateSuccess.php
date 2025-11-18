<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo __('Update digital object titles'); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php echo render_title(new sfIsadPlugin($resource)); ?>
    </span>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $digitalObjectTitleForm->renderGlobalErrors(); ?>
  <?php echo $digitalObjectTitleForm->renderFormTag(url_for([$resource, 'module' => 'informationobject', 'action' => 'multiFileUpdate', 'items' => $sf_request->items]), ['method' => 'post', 'id' => 'bulk-title-update-form']); ?>
    <?php echo $digitalObjectTitleForm->renderHiddenFields(); ?>

    <div class="table-responsive mb-3">
      <table class="table table-bordered mb-0">
        <thead>
          <tr>
            <th><?php echo __('Object'); ?></th>
            <th id="title-label"><?php echo __('Title'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($digitalObjectTitleForm->getInformationObjects() as $io) { ?>
            <tr>
              <td class="thumbnail-container">
                <?php foreach ($io->digitalObjectsRelatedByobjectId as $do) { ?>
                  <?php if (
                      (null !== $thumbnail = $do->getRepresentationByUsage(QubitTerm::THUMBNAIL_ID))
                      && QubitAcl::check($io, 'readThumbnail')
                  ) { ?>
                    <?php echo image_tag($thumbnail->getFullPath(), ['alt' => __($do->getDigitalObjectAltText() ?: 'Original %1% not accessible', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]), 'class' => 'img-thumbnail']); ?>
                  <?php } else { ?>
                    <?php echo image_tag(QubitDigitalObject::getGenericIconPathByMediaTypeId($do->mediaTypeId), ['alt' => __($do->getDigitalObjectAltText() ?: 'Original %1% not accessible', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]), 'class' => 'img-thumbnail']); ?>
                  <?php } ?>
                <?php } ?>
              </td>
              <td>
                <?php if ($sf_user->getCulture() != $io->getSourceCulture() && !strlen($io->title)) { ?>
                  <div class="default-translation">
                    <?php echo render_value_inline($digitalObjectTitleForm[$io->id]->getValue(), $io); ?>
                  </div>
                <?php } ?>

                <?php echo render_field(
                    $digitalObjectTitleForm[$io->id],
                    null,
                    ['onlyInputs' => true, 'aria-labelledby' => 'title-label', 'class' => 'mb-3']
                ); ?>

                <?php if (isset($io->digitalObjectsRelatedByobjectId[0]->name)) { ?>
                  <div class="mb-3">
                    <h3 class="fs-6 mb-2">
                      <?php echo __('Filename'); ?>
                    </h3>
                    <span class="text-muted">
                      <?php echo $io->digitalObjectsRelatedByobjectId[0]->name; ?>
                    </span>
                  </div>
                <?php } ?>

                <?php if (isset($io->levelOfDescription)) { ?>
                  <div class="mb-3">
                    <h3 class="fs-6 mb-2">
                      <?php echo __('Level of description'); ?>
                    </h3>
                    <span class="text-muted">
                      <?php echo render_value_inline($io->levelOfDescription); ?>
                    </span>
                  </div>
                <?php } ?>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" id="rename-form-submit" type="submit" value="<?php echo __('Save'); ?>">
    </section>
  </form>

<?php end_slot(); ?>
