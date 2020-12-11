<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo __('Update digital object titles') ?>
    <span class="sub"><?php echo render_title(new sfIsadPlugin($resource)) ?> </span>
  </h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $digitalObjectTitleForm->renderGlobalErrors() ?>
  <?php echo $digitalObjectTitleForm->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'multiFileUpdate', 'items' => $sf_request->items)), array('method' => 'post', 'id' => 'bulk-title-update-form')) ?>
    <?php echo $digitalObjectTitleForm->renderHiddenFields() ?>

    <div id="content">

      <table class="table sticky-enabled">
        <thead>
          <tr>
            <th><?php echo __('Object')?></th>
            <th><?php echo __('Title')?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($digitalObjectTitleForm->getInformationObjects() as $io): ?>
            <tr>
              <td class="thumbnail-container">
                <?php foreach ($io->digitalObjectsRelatedByobjectId as $do): ?>
                  <?php if (
                    (null !== $thumbnail = $do->getRepresentationByUsage(QubitTerm::THUMBNAIL_ID))
                    && QubitAcl::check($io, 'readThumbnail')
                  ): ?>
                    <?php echo image_tag($thumbnail->getFullPath(), array('alt' => __($do->getDigitalObjectAltText() ?: 'Original %1% not accessible', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))) ?>
                  <?php else: ?>
                    <?php echo image_tag(QubitDigitalObject::getGenericIconPathByMediaTypeId($do->mediaTypeId), array('alt' => __($do->getDigitalObjectAltText() ?: 'Original %1% not accessible', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))) ?>
                  <?php endif; ?>
                <?php endforeach; ?>
              </td>
              <td>
                <?php if ($sf_user->getCulture() != $io->getSourceCulture() && !strlen($io->title)): ?>
                  <div class="default-translation">
                    <?php echo render_value($digitalObjectTitleForm[$io->id]->getValue(), $io) ?>
                  </div>
                <?php endif; ?>

                <?php echo $digitalObjectTitleForm[$io->id]
                    ->label(__('Title')) ?>
                <?php echo __($io->digitalObjectsRelatedByobjectId[0]->name) ?>
                <?php echo render_show(__('Level of description'), render_value_inline($io->levelOfDescription)) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" id="rename-form-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>
  </form>

<?php end_slot() ?>
