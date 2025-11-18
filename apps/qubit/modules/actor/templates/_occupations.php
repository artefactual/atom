<h3 class="fs-6 mb-2">
  <?php echo __('Occupation(s)'); ?>
</h3>

<div class="table-responsive">
  <table class="table table-bordered mb-0 multi-row">
    <thead class="table-light">
      <tr>
        <th id="occupations-occupation-head" class="w-50">
          <?php echo __('Occupation'); ?>
        </th>
        <th id="occupations-content-head" class="w-50">
          <?php echo __('Note'); ?>
        </th>
        <th>
          <span class="visually-hidden"><?php echo __('Delete'); ?></span>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php
          $extraInputs = '<input class="list" type="hidden" value="'
              .url_for([
                  'module' => 'term',
                  'action' => 'autocomplete',
                  'taxonomy' => url_for([$occupationsTaxonomy, 'module' => 'taxonomy']),
              ])
              .'">';
          if (QubitAcl::check($occupationsTaxonomy, 'createTerm')) {
              $extraInputs .= '<input class="add" type="hidden" data-link-existing="true" value="'
                  .url_for([
                      'module' => 'term',
                      'action' => 'add',
                      'taxonomy' => url_for([$occupationsTaxonomy, 'module' => 'taxonomy']),
                  ])
                  .' #name">';
          }
      ?>
      <?php $i = 0;
      foreach ($occupations as $item) { ?>
        <?php $form->getWidgetSchema()->setNameFormat("occupations[{$i}][%s]");
        ++$i; ?>

        <tr class="related_obj_<?php echo $item->id; ?>">
          <td>
            <input
              type="hidden"
              name="<?php echo $form->getWidgetSchema()->generateName('id'); ?>"
              value="<?php echo $item->id; ?>">
            <?php $form->setWidget('occupation', new sfWidgetFormSelect(
                ['choices' => [url_for([$item->term, 'module' => 'term']) => $item->term]])
            ); ?>
            <div>
              <?php echo render_field($form->occupation, null, [
                  'class' => 'form-autocomplete',
                  'extraInputs' => $extraInputs,
                  'aria-labelledby' => 'occupations-occupation-head',
                  'onlyInputs' => true,
              ]); ?>
            </div>
          </td>
          <td>
            <?php $note = $item
                ->getNotesByType(['noteTypeId' => QubitTerm::ACTOR_OCCUPATION_NOTE_ID])
                ->offsetGet(0); ?>
            <?php if (isset($note)) { ?>
              <?php $form->setDefault('content', $note->getContent()); ?>
              <?php echo render_field($form->content, $note, [
                  'aria-labelledby' => 'occupations-content-head',
                  'onlyInputs' => true,
              ]); ?>
            <?php } else { ?>
              <?php $form->setDefault('content', ''); ?>
              <?php echo render_field($form->content, null, [
                  'aria-labelledby' => 'occupations-content-head',
                  'onlyInputs' => true,
              ]); ?>
            <?php } ?>
          </td>
          <td>
            <button type="button" class="multi-row-delete btn atom-btn-white">
              <i class="fas fa-times" aria-hidden="true"></i>
              <span class="visually-hidden"><?php echo __('Delete row'); ?></span>
            </button>
          </td>
        </tr>
      <?php } ?>

      <?php $form->getWidgetSchema()->setNameFormat("occupations[{$i}][%s]"); ?>

      <tr>
        <td>
          <?php $form->setWidget('occupation', new sfWidgetFormSelect(['choices' => []])); ?>
          <div>
            <?php echo render_field($form->occupation, null, [
                'class' => 'form-autocomplete',
                'extraInputs' => $extraInputs,
                'aria-labelledby' => 'occupations-occupation-head',
                'onlyInputs' => true,
            ]); ?>
          </div>
        </td>
        <td>
          <?php $form->setDefault('content', ''); ?>
          <?php echo render_field($form->content, null, [
              'aria-labelledby' => 'occupations-content-head',
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
