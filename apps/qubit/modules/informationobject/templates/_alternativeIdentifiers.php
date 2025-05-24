<div class="section" id="alternative-identifiers-table"<?php echo 0 < count($alternativeIdentifiers) ? '' : 'style="display:none"'; ?>>

  <h3><?php echo __('Alternative identifier(s)'); ?></h3>

  <table class="table table-bordered multiRow">
    <thead>
      <tr>
        <th style="width: 50%">
          <?php echo __('Label'); ?>
        </th><th style="width: 50%">
          <?php echo __('Identifier'); ?>
        </th>
      </tr>
    </thead><tbody>

      <?php $i = 0;
      foreach ($alternativeIdentifiers as $item) { ?>
        <?php $form->getWidgetSchema()->setNameFormat("alternativeIdentifiers[{$i}][%s]"); ?>

        <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd'; ?> related_obj_<?php echo $item->id; ?>">
          <td>
            <div class="animateNicely">
              <input type="hidden" name="alternativeIdentifiers[<?php echo $i; ?>][id]" value="<?php echo $item->id; ?>"/>
              <?php $form->setDefault('label', $item->name); ?>
              <?php echo $form->label; ?>
            </div>
          </td><td>
            <div class="animateNicely">
              <?php $form->setDefault('identifier', $item->getValue(['sourceCulture' => true])); ?>
              <?php echo $form->identifier; ?>
            </div>
          </td>
        </tr>

        <?php ++$i; ?>
      <?php } ?>

      <?php $form->getWidgetSchema()->setNameFormat("alternativeIdentifiers[{$i}][%s]"); ?>

      <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd'; ?>">
        <td>
          <div class="animateNicely">
            <?php $form->setDefault('label', ''); ?>
            <?php echo $form->label; ?>
          </div>
        </td><td>
          <div class="animateNicely">
            <?php $form->setDefault('identifier', ''); ?>
            <?php echo $form->identifier; ?>
          </div>
        </td>
      </tr>

    </tbody>

    <tfoot>
      <tr>
        <td colspan="3"><a href="#" class="multiRowAddButton"><?php echo __('Add new'); ?></a></td>
      </tr>
    </tfoot>

  </table>

  <div class="description">
    <?php echo __('<strong>Label:</strong> Enter a name for the alternative identifier field that indicates its purpose and usage.<br/><strong>Identifier:</strong> Enter a legacy reference code, alternative identifier, or any other alpha-numeric string associated with the record.'); ?>
  </div>

</div>
