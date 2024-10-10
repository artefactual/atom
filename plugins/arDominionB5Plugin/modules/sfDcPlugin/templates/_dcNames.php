<h3 class="fs-6 mb-2">
  <?php echo __('Name(s)'); ?>
</h3>

<div class="table-responsive mb-2">
  <table class="table table-bordered mb-0 multi-row">
    <thead class="table-light">
      <tr>
        <th id="dc-names-actor-head" class="w-60">
          <?php echo __('Actor name'); ?>
        </th>
        <th id="dc-names-type-head" class="w-40">
          <?php echo __('Type'); ?>
        </th>
        <th>
          <span class="visually-hidden"><?php echo __('Delete'); ?></span>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 0;
      foreach ($resource->getActorEvents() as $item) { ?>
        <?php if (isset($item->actor)) { ?>
          <?php $form->getWidgetSchema()->setNameFormat("editNames[{$i}][%s]");
          ++$i; ?>

          <tr class="related_obj_<?php echo $item->id; ?>">
            <td>
              <input
                type="hidden"
                name="<?php echo $form->getWidgetSchema()->generateName('id'); ?>"
                value="<?php echo $item->id; ?>">
              <?php echo render_title($item->actor); ?>
            </td>
            <td>
              <?php echo render_value_inline($item->type); ?>
            </td>
            <td>
              <button type="button" class="multi-row-delete btn atom-btn-white">
                <i class="fas fa-times" aria-hidden="true"></i>
                <span class="visually-hidden"><?php echo __('Delete row'); ?></span>
              </button>
            </td>
          </tr>
        <?php } ?>
      <?php } ?>

      <?php $form->getWidgetSchema()->setNameFormat("editNames[{$i}][%s]"); ?>

      <tr>
        <td>
          <div>
            <?php
                $extraInputs = '<input class="list" type="hidden" value="'
                    .url_for(['module' => 'actor', 'action' => 'autocomplete'])
                    .'">';
                if (QubitAcl::check(QubitActor::getRoot(), 'create')) {
                    $extraInputs .= '<input class="add" type="hidden"'
                        .' data-link-existing="true" value="'
                        .url_for(['module' => 'actor', 'action' => 'add'])
                        .' #authorizedFormOfName">';
                }
                echo render_field($form->actor, null, [
                    'class' => 'form-autocomplete',
                    'extraInputs' => $extraInputs,
                    'aria-labelledby' => 'dc-names-actor-head',
                    'aria-describedby' => 'dc-names-table-help',
                    'onlyInputs' => true,
                ]);
            ?>
          </div>
        </td>
        <td>
          <?php echo render_field($form->type, null, [
              'aria-labelledby' => 'dc-names-type-head',
              'aria-describedby' => 'dc-names-table-help',
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

<div class="form-text mb-3" id="dc-names-table-help">
  <?php echo __('Identify and record the name(s) and type(s) of the unit of description.'); ?>
</div>
