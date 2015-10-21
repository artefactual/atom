<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1><?php echo __('Are you sure you want to delete %1%?', array('%1%' => render_title($resource))) ?></h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'term', 'action' => 'delete')), array('method' => 'delete')) ?>

    <?php echo $form->renderHiddenFields() ?>

    <div id="content">

      <?php if (0 < $resource->getRelatedObjectCount()): ?>
        <h2><?php echo __('This term is used in %1% descriptions. The term will be deleted from these descriptions.', array('%1%' => $resource->getRelatedObjectCount())) ?></h2>
        <div class="text-section">
          <?php echo __('The related object(s) will <strong>not</strong> be deleted') ?>
        </div>
      <?php endif; ?>

      <?php if (0 < count($resource->events)): ?>
        <h2><?php echo __('It\'s used in %1% events that will also be deleted', array('%1%' => count($resource->events))) ?></h2>
        <div class="delete-list">
          <ul>
            <?php foreach ($resource->events as $item): ?>
              <li><?php echo Qubit::renderDateStartEnd($item->getDate(array('cultureFallback' => true)), $item->startDate, $item->endDate) ?> (<?php echo render_title($resource) ?>) <?php echo link_to(render_title($item->object), array($item->object, 'module' => 'informationobject')) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if (0 < count($resource->descendants)): ?>
        <h2><?php echo __('It has %1% descendants that will also be deleted', array('%1%' => count($resource->descendants))) ?><h2>
        <div class="delete-list">
          <ul>
            <?php foreach ($resource->descendants as $item): ?>
              <li><?php echo link_to(render_title($item), array($item, 'module' => 'term')) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'term'), array('class' => 'c-btn')) ?></li>
        <li><input class="c-btn c-btn-delete" type="submit" value="<?php echo __('Delete') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
