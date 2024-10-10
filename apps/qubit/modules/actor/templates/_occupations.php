<table class="table table-bordered multiRow">
  <thead>
    <tr>
      <th style="width: 50%">
        <?php echo __('Occupation'); ?>
      </th>
      <th style="width: 50%">
        <?php echo __('Note'); ?>
      </th>
    </tr>
  </thead><tbody>

    <?php $i = 0;
    foreach ($occupations as $item) { ?>
      <?php $form->getWidgetSchema()->setNameFormat("occupations[{$i}][%s]"); ?>

      <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd'; ?> related_obj_<?php echo $item->id; ?>">
        <td>
          <div class="animateNicely">
            <input type="hidden" name="occupations[<?php echo $i; ?>][id]" value="<?php echo $item->id; ?>"/>
            <div class="form-item">
              <?php $form->setWidget('occupation', new sfWidgetFormSelect(['choices' => [url_for([$item->term, 'module' => 'term']) => $item->term]])); ?>
              <?php echo $form->occupation->render(['class' => 'form-autocomplete']); ?>
              <?php if (QubitAcl::check($occupationsTaxonomy, 'createTerm')) { ?>
                <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(['module' => 'term', 'action' => 'add', 'taxonomy' => url_for([$occupationsTaxonomy, 'module' => 'taxonomy'])]); ?> #name"/>
              <?php } ?>
              <input class="list" type="hidden" value="<?php echo url_for(['module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for([$occupationsTaxonomy, 'module' => 'taxonomy'])]); ?>"/>
            </div>
          </div>
        </td><td>
          <div class="animateNicely">
            <?php $note = $item->getNotesByType(['noteTypeId' => QubitTerm::ACTOR_OCCUPATION_NOTE_ID])->offsetGet(0); ?>
            <?php if (isset($note)) { ?>
              <?php $form->setDefault('content', $note->getContent()); ?>
              <?php echo render_field($form->content, $note, ['class' => 'resizable', 'onlyInput' => true]); ?>
            <?php } else { ?>
              <?php $form->setDefault('content', ''); ?>
              <?php echo $form->content->render(['class' => 'resizable']); ?>
            <?php } ?>
          </div>
        </td>
      </tr>

      <?php ++$i; ?>
    <?php } ?>

    <?php $form->getWidgetSchema()->setNameFormat("occupations[{$i}][%s]"); ?>

    <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd'; ?>">
      <td>
        <div class="animateNicely">
          <div class="form-item">
            <?php $form->setWidget('occupation', new sfWidgetFormSelect(['choices' => []])); ?>
            <?php echo $form->occupation->render(['class' => 'form-autocomplete']); ?>
            <?php if (QubitAcl::check($occupationsTaxonomy, 'createTerm')) { ?>
              <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(['module' => 'term', 'action' => 'add', 'taxonomy' => url_for([$occupationsTaxonomy, 'module' => 'taxonomy'])]); ?> #name"/>
            <?php } ?>
            <input class="list" type="hidden" value="<?php echo url_for(['module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for([$occupationsTaxonomy, 'module' => 'taxonomy'])]); ?>"/>
          </div>
        </div>
      </td><td>
        <div class="animateNicely">
          <?php $form->setDefault('content', ''); ?>
          <?php echo $form->content->render(['class' => 'resizable']); ?>
        </div>
      </td>
    </tr>

    <tfoot>
      <tr>
        <td colspan="3"><a href="#" class="multiRowAddButton"><?php echo __('Add new'); ?></a></td>
      </tr>
    </tfoot>

  </tbody>
</table>
