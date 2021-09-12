<div id="alternative-identifiers-table"<?php echo 0 < count($alternativeIdentifiers) ? '' : ' class="d-none"'; ?>>
  <h3 class="fs-6 mb-2">
    <?php echo __('Alternative identifier(s)'); ?>
  </h3>

  <div class="table-responsive mb-2">
    <table class="table table-bordered mb-0 multi-row">
      <thead class="table-light">
        <tr>
          <th id="alt-identifiers-label-head" style="width: 50%">
            <?php echo __('Label'); ?>
          </th>
          <th id="alt-identifiers-identifier-head" style="width: 50%">
            <?php echo __('Identifier'); ?>
          </th>
          <th>
            <span class="visually-hidden"><?php echo __('Delete'); ?></span>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 0; foreach ($alternativeIdentifiers as $item) { ?>
          <?php $form->getWidgetSchema()->setNameFormat("alternativeIdentifiers[{$i}][%s]"); ++$i; ?>

          <tr class="related_obj_<?php echo $item->id; ?>">
            <td>
              <input
                type="hidden"
                name="<?php echo $form->getWidgetSchema()->generateName('id'); ?>"
                value="<?php echo $item->id; ?>">
              <?php $form->setDefault('label', $item->name); ?>
              <?php echo render_field($form->label, null, [
                  'aria-labelledby' => 'alt-identifiers-label-head',
                  'aria-describedby' => 'alt-identifiers-table-help',
                  'onlyInputs' => true,
              ]); ?>
            </td>
            <td>
              <?php $form->setDefault('identifier', $item->getValue(['sourceCulture' => true])); ?>
              <?php echo render_field($form->identifier, null, [
                  'aria-labelledby' => 'alt-identifiers-identifier-head',
                  'aria-describedby' => 'alt-identifiers-table-help',
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

        <?php $form->getWidgetSchema()->setNameFormat("alternativeIdentifiers[{$i}][%s]"); ?>

        <tr>
          <td>
            <?php $form->setDefault('label', ''); ?>
            <?php echo render_field($form->label, null, [
                'aria-labelledby' => 'alt-identifiers-label-head',
                'aria-describedby' => 'alt-identifiers-table-help',
                'onlyInputs' => true,
            ]); ?>
          </td>
          <td>
            <?php $form->setDefault('identifier', ''); ?>
            <?php echo render_field($form->identifier, null, [
                'aria-labelledby' => 'alt-identifiers-identifier-head',
                'aria-describedby' => 'alt-identifiers-table-help',
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
          <td colspan="3">
            <button type="button" class="multi-row-add btn atom-btn-white">
              <i class="fas fa-plus me-1" aria-hidden="true"></i>
              <?php echo __('Add new'); ?>
            </button>
          </td>
        </tr>
      </tfoot>
    </table>
  </div>

  <div class="form-text mb-3" id="alt-identifiers-table-help">
    <?php echo __(
        '<strong>Label:</strong> Enter a name for the alternative identifier field'
        .' that indicates its purpose and usage.<br/><strong>Identifier:</strong>'
        .' Enter a legacy reference code, alternative identifier, or any other'
        .' alpha-numeric string associated with the record.'
    ); ?>
  </div>
</div>
