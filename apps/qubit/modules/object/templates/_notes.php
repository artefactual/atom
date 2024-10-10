<div class="section">

  <table class="table table-bordered multiRow">

    <thead>
      <tr>
        <?php if ($hiddenType) { ?>
          <th style="width: 100%">
            <?php echo $tableName; ?>
          </th>
        <?php } else { ?>
          <th style="width: 65%">
            <?php echo $tableName; ?>
          </th>
          <th style="width: 30%">
            <?php echo __('Note type'); ?>
          </th>
        <?php } ?>
      </tr>
    </thead><tbody>

      <?php $i = 0;
      foreach ($notes as $item) { ?>

        <?php $form->getWidgetSchema()->setNameFormat($arrayName."[{$i}][%s]"); ?>

        <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd'; ?> related_obj_<?php echo $item->id; ?>">
          <td>
            <div class="animateNicely">
              <input type="hidden" name="<?php echo $arrayName; ?>[<?php echo $i; ?>][id]" value="<?php echo $item->id; ?>"/>
              <?php if ($hiddenType) { ?>
                <input type="hidden" name="<?php echo $arrayName; ?>[<?php echo $i; ?>][type]" value="<?php echo $hiddenTypeId; ?>"/>
              <?php } ?>
              <?php $form->setDefault('content', $item->getContent()); ?>
              <?php echo render_field($form->content, $item, ['onlyInput' => true, 'class' => 'resizable']); ?>
            </div>
          </td>
          <?php if (!$hiddenType) { ?>
            <td>
              <div class="animateNicely">
                <?php echo $form->getWidgetSchema()->renderField('type', $item->typeId); ?>
              </div>
            </td>
          <?php } ?>
        </tr>

        <?php ++$i; ?>
      <?php } ?>

      <?php $form->getWidgetSchema()->setNameFormat($arrayName."[{$i}][%s]"); ?>

      <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd'; ?>">
        <td>
          <div class="animateNicely">
            <?php if ($hiddenType) { ?>
              <input type="hidden" name="<?php echo $arrayName; ?>[<?php echo $i; ?>][type]" value="<?php echo $hiddenTypeId; ?>"/>
            <?php } ?>
            <?php $form->setDefault('content', ''); ?>
            <?php echo $form->content->render(['class' => 'resizable']); ?>
          </div>
        </td>
        <?php if (!$hiddenType) { ?>
          <td>
            <div class="animateNicely">
              <?php echo $form->type; ?>
            </div>
          </td>
        <?php } ?>
      </tr>

    </tbody>

    <tfoot>
      <tr>
        <td colspan="<?php echo $hiddenType ? 2 : 3; ?>"><a href="#" class="multiRowAddButton"><?php echo __('Add new'); ?></a></td>
      </tr>
    </tfoot>

  </table>

  <div class="description">
    <?php echo $help; ?>
  </div>

</div>
