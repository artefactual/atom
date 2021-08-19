<?php $sf_response->addJavaScript('date', 'last'); ?>

<div class="section">

  <h3><?php echo __('Date(s)'); ?> <span class="form-required" title="<?php echo __('This is a mandatory element.'); ?>">*</span></h3>

  <table class="table table-bordered multiRow">
    <thead>
      <tr>
        <th style="width: 25%">
          <?php echo __('Type'); ?>
        </th><th style="width: 30%">
          <?php echo __('Date'); ?>
        </th><th style="width: 20%">
          <?php echo __('Start'); ?>
        </th><th style="width: 20%">
          <?php echo __('End'); ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 0; ?>
      <?php foreach ($events->getEmbeddedForms() as $item) { ?>
        <tr class="date <?php echo (0 == $i % 2) ? 'even' : 'odd'; ?>
          related_obj_<?php echo $i; ?>">
          <td>
            <?php echo $item['type']->renderError(); ?>
            <?php echo $item['type']->render(); ?>
          </td><td>
            <?php echo $item['date']->renderError(); ?>
            <?php echo $item['date']->render(); ?>
          </td><td>
            <?php echo $item['startDate']->renderError(); ?>
            <?php echo $item['startDate']->render(); ?>
          </td><td>
            <?php echo $item['endDate']->renderError(); ?>
            <?php echo $item['endDate']->render(); ?>
          </td>
        </tr>
        <?php ++$i; ?>
      <?php } ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5"><a href="#" class="multiRowAddButton"><?php echo __('Add new'); ?></a></td>
      </tr>
    </tfoot>
  </table>

  <?php if (isset($help)) { ?>
    <div class="description">
      <?php echo $help; ?>
    </div>
  <?php } ?>

</div>
