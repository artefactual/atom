<h1><?php echo __('Item list - report criteria') ?></h1>

<h1 class="label"><?php echo render_title($resource) ?></h1>

<?php echo $form->renderGlobalErrors() ?>

<?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'itemList'))) ?>

  <div class="section">

    <?php echo render_field($form->sortBy
      ->label(__('Sort by')), $resource) ?>

    <?php echo render_field($form->includeThumbnails
      ->label(__('Include thumbnails')), $resource) ?>

  </div>

<div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">
        <li><input class="form-submit" type="submit" value="<?php echo __('Continue') ?>"/></li>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject')) ?></li>
      </ul>
    </div>

  </div>

</form>
