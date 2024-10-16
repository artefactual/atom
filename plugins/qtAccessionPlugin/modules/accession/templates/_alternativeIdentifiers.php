<div class="section" id="alternative-identifiers-table"<?php echo 0 < count($alternativeIdentifierData) ? '' : 'style="display:none"'; ?>>

  <h3><?php echo __('Alternative identifier(s)'); ?></h3>

  <table class="table table-bordered multiRow">
    <thead>
      <tr>
        <th style="width: 50%">
          <?php echo __('Type'); ?>
        </th><th style="width: 50%">
          <?php echo __('Identifier'); ?>
        </th>
      </tr>
    </thead><tbody>

      <?php $i = 0;
      foreach ($alternativeIdentifierData as $identifier) { ?>
        <?php $form->getWidgetSchema()->setNameFormat("alternativeIdentifiers[{$i}][%s]"); ?>

        <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd'; ?> related_obj_<?php echo $identifier['id']; ?>">
          <td>
            <div class="animateNicely">
              <input type="hidden" name="alternativeIdentifiers[<?php echo $i; ?>][id]" value="<?php echo $identifier['id']; ?>"/>
              <?php $form->setDefault('identifierType', $identifier['typeId']); ?>
              <?php echo $form->identifierType; ?>
            </div>
          </td><td>
            <div class="animateNicely">
              <?php $form->setDefault('identifier', $identifier['value']); ?>
              <?php echo $form->identifier; ?>
            </div>
            <div class="animateNicely">
              <?php $form->setDefault('note', $identifier['note']); ?>
              <?php echo render_field($form->note, $identifier['object']); ?>
            </div>
          </td>
        </tr>

        <?php ++$i; ?>
      <?php } ?>

      <?php $form->getWidgetSchema()->setNameFormat("alternativeIdentifiers[{$i}][%s]"); ?>

      <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd'; ?>">
        <td>
          <div class="animateNicely">
            <?php $form->setDefault('identifierType', ''); ?>
            <?php echo $form->identifierType; ?>
          </div>
        </td><td>
          <div class="animateNicely">
            <?php $form->setDefault('identifier', ''); ?>
            <?php echo $form->identifier; ?>
	  </div>
          <div class="animateNicely">
            <?php $form->setDefault('note', ''); ?>
            <?php echo $form->note; ?>
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
    <?php echo __('<strong>Type:</strong> Enter a name for the alternative identifier field that indicates its purpose and usage.<br/><strong>Identifier:</strong> Enter a legacy reference code, alternative identifier, or any other alpha-numeric string associated with the record.'); ?>
  </div>

</div>
