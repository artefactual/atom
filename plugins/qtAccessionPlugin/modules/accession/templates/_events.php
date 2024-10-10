<div class="section">

  <h3><?php echo __('Event(s)'); ?></h3>

  <table class="table table-bordered multiRow">
    <thead>
      <tr>
        <th style="width: 33%">
          <?php echo __('Type'); ?>
        </th><th style="width: 210px">
          <?php echo __('Date'); ?>
        </th><th>
          <?php echo __('Agent'); ?>
        </th>
      </tr>
    </thead><tbody>

    <?php $i = 0;
    foreach ($eventData as $event) { ?>
      <?php $form->getWidgetSchema()->setNameFormat("events[{$i}][%s]"); ?>

      <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd'; ?> related_obj_<?php echo $event['id']; ?>">
        <td>
          <div class="animateNicely">
            <input type="hidden" name="events[<?php echo $i; ?>][id]" value="<?php echo $event['id']; ?>"/>
            <?php $form->setDefault('eventType', $event['typeId']); ?>
            <?php echo $form->eventType; ?>
          </div>
        </td><td>
          <div class="animateNicely">
            <?php $form->setDefault('date', $event['date']); ?>
            <?php echo $form->date->renderRow(['class' => 'date-widget', 'icon' => image_path('calendar.png')]); ?>
          </div>
        </td><td>
          <div class="animateNicely">
            <?php $form->setDefault('agent', $event['agent']); ?>
            <?php echo render_field($form->agent, $event['object']); ?>
          </div>
          <div class="animateNicely">
            <?php $form->setDefault('note', $event['note']->getContent()); ?>
            <?php echo render_field($form->note, $event['note'], ['name' => 'content']); ?>
          </div>
        </td>
      </tr>

      <?php ++$i; ?>
    <?php } ?>

    <?php $form->getWidgetSchema()->setNameFormat("events[{$i}][%s]"); ?>

      <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd'; ?>">
        <td>
          <div class="animateNicely">
            <?php $form->setDefault('eventType', ''); ?>
            <?php echo $form->eventType; ?>
          </div>
        </td><td>
          <div class="animateNicely">
            <?php $form->setDefault('date', ''); ?>
            <?php echo $form->date->renderRow(['class' => 'date-widget', 'icon' => image_path('calendar.png')]); ?>
          </div>
         </td><td>
          <div class="animateNicely">
            <?php $form->setDefault('agent', ''); ?>
            <?php echo $form->agent; ?>
          </div>
          <div id="event-note-new" class="animateNicely">
            <?php $form->setDefault('note', ''); ?>
            <?php echo $form->note; ?>
          </div>
        </td>
      </tr>

    </tbody>

    <tfoot>
      <tr>
        <td colspan="4"><a href="#" class="multiRowAddButton"><?php echo __('Add new'); ?></a></td>
      </tr>
    </tfoot>

  </table>

  <div class="description">
    <?php echo __('%1%Type:%2% Select the type of the event.%3%', ['%1%' => '<strong>', '%2%' => '</strong>', '%3%' => '<br/>']); ?>
    <?php echo __('%1%Date:%2% Enter the date of the event.%3%', ['%1%' => '<strong>', '%2%' => '</strong>', '%3%' => '<br/>']); ?>
    <?php echo __('%1%Agent:%2% Enter the agent associated with the event.%3%', ['%1%' => '<strong>', '%2%' => '</strong>', '%3%' => '<br/>']); ?>
    <?php echo __('%1%Note:%2% Enter notes associated with the event.%3%', ['%1%' => '<strong>', '%2%' => '</strong>', '%3%' => '<br/>']); ?>
  </div>

</div>
