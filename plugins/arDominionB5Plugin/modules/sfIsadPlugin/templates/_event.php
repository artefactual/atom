<h3 class="fs-6 mb-2">
  <?php echo __('Date(s)'); ?>
  <span class="form-required" title="<?php echo __('This is a mandatory element.'); ?>">*</span>
</h3>

<div class="table-responsive mb-2">
  <table class="table table-bordered mb-0 multi-row">
    <thead class="table-light">
      <tr>
        <th id="isad-events-type-head" class="w-25">
          <?php echo __('Type'); ?>
        </th>
        <th id="isad-events-date-head" class="w-30">
          <?php echo __('Date'); ?>
        </th>
        <th id="isad-events-start-head">
          <?php echo __('Start'); ?>
        </th>
        <th id="isad-events-end-head">
          <?php echo __('End'); ?>
        </th>
        <th>
          <span class="visually-hidden"><?php echo __('Delete'); ?></span>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 0;
      foreach ($resource->getDates() as $item) { ?>
        <?php $form->getWidgetSchema()->setNameFormat("editEvents[{$i}][%s]");
        ++$i; ?>

        <tr class="date related_obj_<?php echo $item->id; ?>">
          <td>
            <input
              type="hidden"
              name="<?php echo $form->getWidgetSchema()->generateName('id'); ?>"
              value="<?php echo url_for([$item, 'module' => 'event']); ?>">
            <?php
                $save = $form->type->choices;
                $form->type->choices += [url_for([$item->type, 'module' => 'term']) => $item->type];
                $form->setDefault('type', url_for([$item->type, 'module' => 'term']));
                echo render_field($form->type, null, [
                    'aria-labelledby' => 'isad-events-type-head',
                    'aria-describedby' => 'isad-events-table-help',
                    'onlyInputs' => true,
                ]);
                $form->type->choices = $save;
            ?>
          </td>
          <td>
            <?php $form->setDefault('date', $item->getDate(['cultureFallback' => true])); ?>
            <?php echo render_field($form->date, null, [
                'aria-labelledby' => 'isad-events-date-head',
                'aria-describedby' => 'isad-events-table-help',
                'onlyInputs' => true,
            ]); ?>
          </td>
          <td>
            <?php $form->setDefault('startDate', Qubit::renderDate($item->startDate)); ?>
            <?php echo render_field($form->startDate, null, [
                'aria-labelledby' => 'isad-events-start-head',
                'aria-describedby' => 'isad-events-table-help',
                'onlyInputs' => true,
            ]); ?>
          </td>
          <td>
            <?php $form->setDefault('endDate', Qubit::renderDate($item->endDate)); ?>
            <?php echo render_field($form->endDate, null, [
                'aria-labelledby' => 'isad-events-end-head',
                'aria-describedby' => 'isad-events-table-help',
                'onlyInputs' => true,
            ]); ?>
          </td>
          <td>
            <button type="button" class="multi-row-delete btn atom-btn-white">
              <i class="fas fa-times" aria-hidden="true"></i>
              <span class="visually-hidden"><?php echo __('Delete row'); ?></span>
            </button>
          </td>
        </tr>
      <?php } ?>

      <?php $form->getWidgetSchema()->setNameFormat("editEvents[{$i}][%s]"); ?>

      <tr class="date">
        <td>
          <?php $form->setDefault('type', ''); ?>
          <?php echo render_field($form->type, null, [
              'aria-labelledby' => 'isad-events-type-head',
              'aria-describedby' => 'isad-events-table-help',
              'onlyInputs' => true,
          ]); ?>
        </td>
        <td>
          <?php $form->setDefault('date', ''); ?>
          <?php echo render_field($form->date, null, [
              'aria-labelledby' => 'isad-events-date-head',
              'aria-describedby' => 'isad-events-table-help',
              'onlyInputs' => true,
          ]); ?>
        </td>
        <td>
          <?php $form->setDefault('startDate', ''); ?>
          <?php echo render_field($form->startDate, null, [
              'aria-labelledby' => 'isad-events-start-head',
              'aria-describedby' => 'isad-events-table-help',
              'onlyInputs' => true,
          ]); ?>
        </td>
        <td>
          <?php $form->setDefault('endDate', ''); ?>
          <?php echo render_field($form->endDate, null, [
              'aria-labelledby' => 'isad-events-end-head',
              'aria-describedby' => 'isad-events-table-help',
              'onlyInputs' => true,
          ]); ?>
        </td>
        <td>
          <button type="button" class="multi-row-delete btn atom-btn-white">
            <i class="fas fa-times" aria-hidden="true"></i>
            <span class="visually-hidden"><?php echo __('Delete row'); ?></span>
          </button>
        </td>
      </tr>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5">
          <button type="button" class="multi-row-add btn atom-btn-white">
            <i class="fas fa-plus me-1" aria-hidden="true"></i>
            <?php echo __('Add new'); ?>
          </button>
        </td>
      </tr>
    </tfoot>
  </table>
</div>

<?php if (isset($help)) { ?>
  <div id="isad-events-table-help" class="form-text mb-3">
    <?php echo $help; ?>
  </div>
<?php } ?>
