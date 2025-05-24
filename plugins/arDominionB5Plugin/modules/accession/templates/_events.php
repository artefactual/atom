<h3 class="fs-6 mb-2">
  <?php echo __('Event(s)'); ?>
</h3>

<div class="table-responsive mb-2">
  <table class="table table-bordered mb-0 multi-row">
    <thead class="table-light">
      <tr>
        <th id="accession-events-type-head" class="w-20">
          <?php echo __('Type'); ?>
        </th>
        <th id="accession-events-date-head" class="w-25">
          <?php echo __('Date'); ?>
        </th>
        <th id="accession-events-agent-head">
          <?php echo __('Agent'); ?>
        </th>
        <th id="accession-events-notes-head">
          <?php echo __('Notes'); ?>
        </th>
        <th>
          <span class="visually-hidden"><?php echo __('Delete'); ?></span>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 0;
      foreach ($eventData as $event) { ?>
        <?php $form->getWidgetSchema()->setNameFormat("events[{$i}][%s]");
        ++$i; ?>

        <tr class="related_obj_<?php echo $event['id']; ?>">
          <td>
            <input
              type="hidden"
              name="<?php echo $form->getWidgetSchema()->generateName('id'); ?>"
              value="<?php echo $event['id']; ?>">
            <?php $form->setDefault('eventType', $event['typeId']); ?>
            <?php echo render_field($form->eventType, null, [
                'aria-labelledby' => 'accession-events-type-head',
                'aria-describedby' => 'accession-events-type-help',
                'onlyInputs' => true,
            ]); ?>
          </td>
          <td>
            <?php $form->setDefault('date', $event['date']); ?>
            <?php echo render_field($form->date, null, [
                'aria-labelledby' => 'accession-events-date-head',
                'aria-describedby' => 'accession-events-date-help',
                'onlyInputs' => true,
            ]); ?>
          </td>
          <td>
            <?php $form->setDefault('agent', $event['agent']); ?>
            <?php echo render_field($form->agent, $event['object'], [
                'aria-labelledby' => 'accession-events-agent-head',
                'aria-describedby' => 'accession-events-agent-help',
                'onlyInputs' => true,
            ]); ?>
          </td>
          <td>
            <?php $form->setDefault('note', $event['note']->getContent()); ?>
            <?php echo render_field($form->note, $event['note'], [
                'aria-labelledby' => 'accession-events-note-head',
                'aria-describedby' => 'accession-events-note-help',
                'onlyInputs' => true,
                'name' => 'content',
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

      <?php $form->getWidgetSchema()->setNameFormat("events[{$i}][%s]"); ?>

      <tr>
        <td>
          <?php $form->setDefault('eventType', ''); ?>
          <?php echo render_field($form->eventType, null, [
              'aria-labelledby' => 'accession-events-type-head',
              'aria-describedby' => 'accession-events-type-help',
              'onlyInputs' => true,
          ]); ?>
        </td>
        <td>
          <?php $form->setDefault('date', ''); ?>
          <?php echo render_field($form->date, null, [
              'aria-labelledby' => 'accession-events-date-head',
              'aria-describedby' => 'accession-events-date-help',
              'onlyInputs' => true,
          ]); ?>
        </td>
        <td>
          <?php $form->setDefault('agent', ''); ?>
          <?php echo render_field($form->agent, null, [
              'aria-labelledby' => 'accession-events-agent-head',
              'aria-describedby' => 'accession-events-agent-help',
              'onlyInputs' => true,
          ]); ?>
        </td>
        <td>
          <?php $form->setDefault('note', ''); ?>
          <?php echo render_field($form->note, null, [
              'aria-labelledby' => 'accession-events-note-head',
              'aria-describedby' => 'accession-events-note-help',
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

<div class="form-text mb-3">
  <span id="accession-events-type-help">
    <?php echo __(
        '%1%Type:%2% Select the type of the event.%3%',
        ['%1%' => '<strong>', '%2%' => '</strong>', '%3%' => '']
    ); ?>
  </span>
  <span id="accession-events-date-help">
    <?php echo __(
        '%1%Date:%2% Enter the date of the event.%3%',
        ['%1%' => '<strong>', '%2%' => '</strong>', '%3%' => '']
    ); ?>
  </span>
  <span id="accession-events-agent-help">
    <?php echo __(
        '%1%Agent:%2% Enter the agent associated with the event.%3%',
        ['%1%' => '<strong>', '%2%' => '</strong>', '%3%' => '']
    ); ?>
  </span>
  <span id="accession-events-note-help">
    <?php echo __(
        '%1%Note:%2% Enter notes associated with the event.%3%',
        ['%1%' => '<strong>', '%2%' => '</strong>', '%3%' => '']
    ); ?>
  </span>
</div>
