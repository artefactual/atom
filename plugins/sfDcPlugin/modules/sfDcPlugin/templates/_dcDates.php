<h3 class="fs-6 mb-2">
  <?php echo __('Date(s)'); ?>
</h3>

<div class="table-responsive mb-2">
  <table class="table table-bordered mb-0 multi-row">
    <thead class="table-light">
      <tr>
        <th id="dc-dates-date-head" class="w-40">
          <?php echo __('Date'); ?>
        </th>
        <th id="dc-dates-start-head" class="w-30">
          <?php echo __('Start'); ?>
        </th>
        <th id="dc-dates-end-head" class="w-30">
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
        <?php $form->getWidgetSchema()->setNameFormat("editDates[{$i}][%s]");
        ++$i; ?>

        <tr class="date related_obj_<?php echo $item->id; ?>">
          <td>
            <input
              type="hidden"
              name="<?php echo $form->getWidgetSchema()->generateName('id'); ?>"
              value="<?php echo $item->id; ?>">
            <?php $form->setDefault('date', $item->getDate(['cultureFallback' => true])); ?>
            <?php echo render_field($form->date, null, [
                'aria-labelledby' => 'dc-dates-date-head',
                'aria-describedby' => 'dc-dates-table-help',
                'onlyInputs' => true,
            ]); ?>
          </td>
          <td>
            <?php $form->setDefault('startDate', Qubit::renderDate($item->startDate)); ?>
            <?php echo render_field($form->startDate, null, [
                'aria-labelledby' => 'dc-dates-start-head',
                'aria-describedby' => 'dc-dates-table-help',
                'onlyInputs' => true,
            ]); ?>
          </td>
          <td>
            <?php $form->setDefault('endDate', Qubit::renderDate($item->endDate)); ?>
            <?php echo render_field($form->endDate, null, [
                'aria-labelledby' => 'dc-dates-end-head',
                'aria-describedby' => 'dc-dates-table-help',
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

      <?php $form->getWidgetSchema()->setNameFormat("editDates[{$i}][%s]"); ?>

      <tr class="date">
        <td>
          <?php $form->setDefault('date', ''); ?>
          <?php echo render_field($form->date, null, [
              'aria-labelledby' => 'dc-dates-date-head',
              'aria-describedby' => 'dc-dates-table-help',
              'onlyInputs' => true,
          ]); ?>
        </td>
        <td>
          <?php $form->setDefault('startDate', ''); ?>
          <?php echo render_field($form->startDate, null, [
              'aria-labelledby' => 'dc-dates-start-head',
              'aria-describedby' => 'dc-dates-table-help',
              'onlyInputs' => true,
          ]); ?>
        </td>
        <td>
          <?php $form->setDefault('endDate', ''); ?>
          <?php echo render_field($form->endDate, null, [
              'aria-labelledby' => 'dc-dates-end-head',
              'aria-describedby' => 'dc-dates-table-help',
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
        <td colspan="4">
          <button type="button" class="multi-row-add btn atom-btn-white">
            <i class="fas fa-plus me-1" aria-hidden="true"></i>
            <?php echo __('Add new'); ?>
          </button>
        </td>
      </tr>
    </tfoot>
  </table>
</div>

<div class="form-text mb-3" id="dc-dates-table-help">
  <?php echo __(
      'Identify and record the date(s) of the unit of description. Identify the type of date given. Record as a single date or a range of dates as appropriate. The Date display field can be used to enter free-text date information, including typographical marks to express approximation, uncertainty, or qualification. Use the start and end fields to make the dates searchable. Do not use any qualifiers or typographical symbols to express uncertainty. Acceptable date formats: YYYYMMDD, YYYY-MM-DD, YYYY-MM, YYYY.'
  ); ?>
</div>
