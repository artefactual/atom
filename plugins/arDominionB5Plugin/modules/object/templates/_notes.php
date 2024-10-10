<h3 class="fs-6 mb-2">
  <?php echo $tableName; ?>
</h3>

<div class="table-responsive mb-2">
  <table class="table table-bordered mb-0 multi-row">
    <thead class="table-light">
      <tr>
	<?php if ($hiddenType) { ?>
          <th id="<?php echo $arrayName; ?>-content-head" class="w-100">
            <?php echo __('Content'); ?>
          </th>
	<?php } else { ?>
          <th id="<?php echo $arrayName; ?>-content-head" class="w-70">
            <?php echo __('Content'); ?>
          </th>
          <th id="<?php echo $arrayName; ?>-type-head" class="w-30">
            <?php echo __('Type'); ?>
          </th>
        <?php } ?>
        <th>
          <span class="visually-hidden"><?php echo __('Delete'); ?></span>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 0;
      foreach ($notes as $item) { ?>
        <?php $form->getWidgetSchema()->setNameFormat($arrayName."[{$i}][%s]");
        ++$i; ?>

        <tr class="related_obj_<?php echo $item->id; ?>">
          <td>
            <input
              type="hidden"
              name="<?php echo $form->getWidgetSchema()->generateName('id'); ?>"
              value="<?php echo $item->id; ?>">
            <?php if ($hiddenType) { ?>
              <input
                type="hidden"
                name="<?php echo $form->getWidgetSchema()->generateName('type'); ?>"
                value="<?php echo $hiddenTypeId; ?>">
            <?php } ?>
            <?php $form->setDefault('content', $item->getContent()); ?>
            <?php echo render_field($form->content, $item, [
                'aria-labelledby' => $arrayName.'-content-head',
                'aria-describedby' => $arrayName.'-table-help',
                'onlyInputs' => true,
            ]); ?>
          </td>
          <?php if (!$hiddenType) { ?>
            <td>
              <?php $form->setDefault('type', $item->typeId); ?>
              <?php echo render_field($form->type, null, [
                  'aria-labelledby' => $arrayName.'-type-head',
                  'aria-describedby' => $arrayName.'-table-help',
                  'onlyInputs' => true,
              ]); ?>
            </td>
          <?php } ?>
          <td>
            <button type="button" class="multi-row-delete btn atom-btn-white">
              <i class="fas fa-times" aria-hidden="true"></i>
              <span class="visually-hidden"><?php echo __('Delete row'); ?></span>
            </button>
          </td>
        </tr>
      <?php } ?>

      <?php $form->getWidgetSchema()->setNameFormat($arrayName."[{$i}][%s]"); ?>

      <tr>
        <td>
          <?php if ($hiddenType) { ?>
            <input
              type="hidden"
              name="<?php echo $form->getWidgetSchema()->generateName('type'); ?>"
              value="<?php echo $hiddenTypeId; ?>">
          <?php } ?>
          <?php $form->setDefault('content', ''); ?>
          <?php echo render_field($form->content, null, [
              'aria-labelledby' => $arrayName.'-content-head',
              'aria-describedby' => $arrayName.'-table-help',
              'onlyInputs' => true,
          ]); ?>
        </td>
        <?php if (!$hiddenType) { ?>
          <td>
            <?php $form->setDefault('type', ''); ?>
            <?php echo render_field($form->type, null, [
                'aria-labelledby' => $arrayName.'-type-head',
                'aria-describedby' => $arrayName.'-table-help',
                'onlyInputs' => true,
            ]); ?>
          </td>
        <?php } ?>
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
        <td colspan="<?php echo $hiddenType ? '2' : '3'; ?>">
          <button type="button" class="multi-row-add btn atom-btn-white">
            <i class="fas fa-plus me-1" aria-hidden="true"></i>
            <?php echo __('Add new'); ?>
          </button>
        </td>
      </tr>
    </tfoot>
  </table>
</div>

<div class="form-text mb-3" id="<?php echo $arrayName; ?>-table-help">
  <?php echo $help; ?>
</div>
