<div class="text-end mb-3">
  <button
    class="btn atom-btn-white text-wrap<?php echo 0 < count($alternativeIdentifierData) ? '' : ' collapsed'; ?>"
    type="button"
    data-bs-toggle="collapse"
    data-bs-target="#alternative-identifiers-table"
    aria-expanded="<?php echo 0 < count($alternativeIdentifierData) ? 'true' : 'false'; ?>"
    aria-controls="alternative-identifiers-table">
    <i class="fas fa-plus me-1" aria-hidden="true"></i>
    <?php echo __('Add alternative identifier(s)'); ?>
  </button>
</div>

<div
  id="alternative-identifiers-table"
  class="collapse<?php echo 0 < count($alternativeIdentifierData) ? ' show' : ''; ?>">
  <h3 class="fs-6 mb-2">
    <?php echo __('Alternative identifier(s)'); ?>
  </h3>

  <div class="table-responsive mb-2">
    <table class="table table-bordered mb-0 multi-row">
      <thead class="table-light">
        <tr>
          <th id="alt-identifiers-type-head" class="w-30">
            <?php echo __('Type'); ?>
          </th>
          <th id="alt-identifiers-identifier-head" class="w-35">
            <?php echo __('Identifier'); ?>
          </th>
          <th id="alt-identifiers-note-head" class="w-35">
            <?php echo __('Notes'); ?>
          </th>
          <th>
            <span class="visually-hidden"><?php echo __('Delete'); ?></span>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 0;
        foreach ($alternativeIdentifierData as $identifier) { ?>
          <?php $form->getWidgetSchema()->setNameFormat("alternativeIdentifiers[{$i}][%s]");
          ++$i; ?>

          <tr class="related_obj_<?php echo $identifier['id']; ?>">
            <td>
              <input
                type="hidden"
                name="<?php echo $form->getWidgetSchema()->generateName('id'); ?>"
                value="<?php echo $identifier['id']; ?>">
              <?php $form->setDefault('identifierType', $identifier['typeId']); ?>
              <?php echo render_field($form->identifierType, null, [
                  'aria-labelledby' => 'alt-identifiers-type-head',
                  'aria-describedby' => 'alt-identifiers-table-help',
                  'onlyInputs' => true,
              ]); ?>
            </td>
            <td>
              <?php $form->setDefault('identifier', $identifier['value']); ?>
              <?php echo render_field($form->identifier, null, [
                  'aria-labelledby' => 'alt-identifiers-identifier-head',
                  'aria-describedby' => 'alt-identifiers-table-help',
                  'onlyInputs' => true,
              ]); ?>
            </td>
            <td>
              <?php $form->setDefault('note', $identifier['note']); ?>
              <?php echo render_field($form->note, $identifier['object'], [
                  'aria-labelledby' => 'alt-identifiers-note-head',
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
            <?php $form->setDefault('identifierType', ''); ?>
            <?php echo render_field($form->identifierType, null, [
                'aria-labelledby' => 'alt-identifiers-type-head',
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
            <?php $form->setDefault('note', ''); ?>
            <?php echo render_field($form->note, null, [
                'aria-labelledby' => 'alt-identifiers-note-head',
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

  <div class="form-text mb-3" id="alt-identifiers-table-help">
    <?php echo __(
        '<strong>Type:</strong> Enter a name for the alternative identifier field that indicates its purpose and usage.<br/><strong>Identifier:</strong> Enter a legacy reference code, alternative identifier, or any other alpha-numeric string associated with the record.'
    ); ?>
  </div>
</div>
