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
    </thead><tbody>

      <?php $i = 0;
      foreach ($resource->getDates() as $item) { ?>

        <?php $form->getWidgetSchema()->setNameFormat("editEvents[{$i}][%s]");
        ++$i; ?>

        <tr class="date <?php echo 0 == ++$i % 2 ? 'even' : 'odd'; ?> related_obj_<?php echo $item->id; ?>">
          <td>
            <div class="animateNicely">
              <input name="<?php echo $form->getWidgetSchema()->generateName('id'); ?>" type="hidden" value="<?php echo url_for([$item, 'module' => 'event']); ?>"/>
              <?php $save = $form->type->choices;
              $form->type->choices += [url_for([$item->type, 'module' => 'term']) => $item->type];
              echo $form->getWidgetSchema()->renderField('type', url_for([$item->type, 'module' => 'term']));
              $form->type->choices = $save; ?>
            </div>
          </td><td>
            <div class="animateNicely">
              <?php echo $form->getWidgetSchema()->renderField('date', $item->getDate(['cultureFallback' => true])); ?>
            </div>
          </td><td>
            <div class="animateNicely">
              <?php echo $form->getWidgetSchema()->renderField('startDate', Qubit::renderDate($item->startDate)); ?>
            </div>
          </td><td>
            <div class="animateNicely">
              <?php echo $form->getWidgetSchema()->renderField('endDate', Qubit::renderDate($item->endDate)); ?>
            </div>
          </td>
        </tr>

      <?php } ?>

      <?php $form->getWidgetSchema()->setNameFormat("editEvents[{$i}][%s]");
      ++$i; ?>

      <tr class="date <?php echo 0 == ++$i % 2 ? 'even' : 'odd'; ?>">
        <td>
          <div class="animateNicely">
            <?php echo $form->type; ?>
          </div>
        </td><td>
          <?php echo $form->date; ?>
        </td><td>
          <?php echo $form->startDate; ?>
        </td><td>
          <?php echo $form->endDate; ?>
        </td>
      </tr>

      <tfoot>
        <tr>
          <td colspan="5"><a href="#" class="multiRowAddButton"><?php echo __('Add new'); ?></a></td>
        </tr>
      </tfoot>
    </tbody>
  </table>

  <?php if (isset($help)) { ?>
    <div class="description">
      <?php echo $help; ?>
    </div>
  <?php } ?>

</div>
