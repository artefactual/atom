<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1><?php echo __('Are you sure you want to delete %1%?', array('%1%' => render_title($resource))) ?></h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'accession', 'action' => 'delete')), array('method' => 'delete')) ?>

    <div id="content">

      <?php if (0 < count($resource->deaccessions)): ?>
        <h2><?php echo __('It has %1% deaccessions that will also be deleted:', array('%1%' => count($resource->deaccessions))) ?></h2>
        <div class="delete-list">
          <ul>
            <?php foreach ($resource->deaccessions as $item): ?>
              <li><?php echo link_to(render_title($item), array($item, 'module' => 'deaccession')) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if (0 < count($accruals)): ?>
        <h2><?php echo __('It has %1% accruals. They will not be deleted.', array('%1%' => count($accruals))) ?></h2>
        <div class="delete-list">
          <ul>
            <?php foreach ($accruals as $item): ?>
              <li><?php echo link_to(render_title($item), array($item, 'module' => 'accession')) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'accession'), array('class' => 'c-btn')) ?></li>
        <li><input class="c-btn c-btn-delete" type="submit" value="<?php echo __('Delete') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
