<table class="table table-bordered multiRow">
  <thead>
    <tr>
      <th style="width: 50%">
        <?php echo __('Occupation') ?>
      </th>
      <th style="width: 50%">
        <?php echo __('Note') ?>
      </th>
    </tr>
  </thead><tbody>

    <?php $i = 0; foreach ($occupations as $item): ?>
      <?php $form->getWidgetSchema()->setNameFormat("occupations[$i][%s]") ?>

      <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd' ?> related_obj_<?php echo $item->id ?>">
        <td>
          <div class="animateNicely">
            <input type="hidden" name="occupations[<?php echo $i ?>][id]" value="<?php echo $item->id ?>"/>
            <div class="form-item">
              <?php $form->setWidget('occupation', new sfWidgetFormSelect(array('choices' => array(url_for(array($item->term, 'module' => 'term')) => $item->term)))); ?>
              <?php echo $form->occupation->render(array('class' => 'form-autocomplete')) ?>
              <?php if (QubitAcl::check($occupationsTaxonomy, 'createTerm')): ?>
                <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array($occupationsTaxonomy, 'module' => 'taxonomy')))) ?> #name"/>
              <?php endif; ?>
              <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for(array($occupationsTaxonomy, 'module' => 'taxonomy')))) ?>"/>
            </div>
          </div>
        </td><td>
          <div class="animateNicely">
            <?php $note = $item->getNotesByType(array('noteTypeId' => QubitTerm::ACTOR_OCCUPATION_NOTE_ID))->offsetGet(0) ?>
            <?php if (isset($note)): ?>
              <?php $form->setDefault('content', $note->getContent()); ?>
              <?php echo render_field($form->content, $note, array('class' => 'resizable', 'onlyInput' => true)) ?>
            <?php else: ?>
              <?php $form->setDefault('content', ''); ?>
              <?php echo $form->content->render(array('class' => 'resizable')) ?>
            <?php endif; ?>
          </div>
        </td>
      </tr>

      <?php $i++ ?>
    <?php endforeach; ?>

    <?php $form->getWidgetSchema()->setNameFormat("occupations[$i][%s]") ?>

    <tr class="<?php echo 0 == $i % 2 ? 'even' : 'odd' ?>">
      <td>
        <div class="animateNicely">
          <div class="form-item">
            <?php $form->setWidget('occupation', new sfWidgetFormSelect(array('choices' => array()))); ?>
            <?php echo $form->occupation->render(array('class' => 'form-autocomplete')) ?>
            <?php if (QubitAcl::check($occupationsTaxonomy, 'createTerm')): ?>
              <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array($occupationsTaxonomy, 'module' => 'taxonomy')))) ?> #name"/>
            <?php endif; ?>
            <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for(array($occupationsTaxonomy, 'module' => 'taxonomy')))) ?>"/>
          </div>
        </div>
      </td><td>
        <div class="animateNicely">
          <?php $form->setDefault('content', ''); ?>
          <?php echo $form->content->render(array('class' => 'resizable')) ?>
        </div>
      </td>
    </tr>

  </tbody>
</table>
