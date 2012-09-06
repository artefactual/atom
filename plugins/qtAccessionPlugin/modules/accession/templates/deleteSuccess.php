<h1><?php echo __('Are you sure you want to delete %1%?', array('%1%' => render_title($resource))) ?></h1>

<?php if (0 < count($resource->deaccessions)): ?>
  <h2><?php echo __('It has %1% deaccessions that will also be deleted:', array('%1%' => count($resource->deaccessions))) ?></h2>
  <ul>
    <?php foreach ($resource->deaccessions as $item): ?>
      <li><?php echo link_to(render_title($item), array($item, 'module' => 'deaccession')) ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<?php if (0 < count($accruals)): ?>
  <h2><?php echo __('It has %1% accruals. They will not be deleted.', array('%1%' => count($accruals))) ?></h2>
  <ul>
    <?php foreach ($accruals as $item): ?>
      <li><?php echo link_to(render_title($item), array($item, 'module' => 'accession')) ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'accession', 'action' => 'delete')), array('method' => 'delete')) ?>

  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'accession')) ?></li>
        <li><input class="form-submit" type="submit" value="<?php echo __('Confirm') ?>"/></li>
      </ul>
    </div>

  </div>

</form>
