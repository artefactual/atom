<?php decorate_with('layout_2col') ?>

<?php slot('sidebar') ?>
  <?php include_component('informationobject', 'contextMenu') ?>
<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Calculate dates - Error') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array(
    $resource, 'module' => 'informationobject', 'action' => 'calculateDates')
  )) ?>

    <div id="content">

    <?php if ($resource->rgt - $resource->lft == 1): ?>
      <legend class="collapse-processed"><?php echo __('No children found') ?></legend>

      <div class="alert alert-warning">
        <?php echo __(
                'Cannot calculate accumulated dates because this %1% has no children',
                ['%1%' => sfConfig::get('app_ui_label_informationobject')]
              )
        ?>
      </div>
    <?php elseif (0 == count($descendantEventTypes)): ?>
      <legend class="collapse-processed"><?php echo __('No lower level dates found') ?></legend>

      <div class="alert alert-warning">
        <?php echo __('Cannot calculate accumulated dates because no lower level dates exist') ?>
      </div>
    <?php endif; // no $descendantEventTypes ?>

    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(
          __('Cancel'),
          array($resource, 'module' => 'informationobject'),
          array('class' => 'c-btn')
        ) ?></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
