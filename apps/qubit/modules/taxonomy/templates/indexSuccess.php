<h1><?php echo __('List %1%', array('%1%' => render_title($resource))) ?></h1>

<table class="sticky-enabled">
  <thead>
    <tr>
      <th>
        <?php echo __('%1% term', array('%1%' => render_title($resource))) ?>
      </th><th>
        <?php echo __('Scope note') ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($terms as $item): ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
        <td>

          <?php if ($item->isProtected()): ?>
            <?php echo link_to(render_title($item->getName(array('cultureFallback' => true))), array($item, 'module' => 'term'), array('class' => 'readOnly')) ?>
          <?php else: ?>
            <?php echo link_to(render_title($item->getName(array('cultureFallback' => true))), array($item, 'module' => 'term')) ?>
          <?php endif; ?>

          <?php if (0 < count($item->descendants)): ?>
            <span class="note2">(<?php echo count($item->descendants) ?>)</span>
          <?php endif; ?>

        </td><td>
          <ul>
            <?php foreach ($item->getNotesByType(array('noteTypeId' => QubitTerm::SCOPE_NOTE_ID)) as $note): ?>
              <li><?php echo $note->getContent(array('cultureFallback' => 'true')) ?></li>
            <?php endforeach; ?>
          </ul>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php echo get_partial('default/pager', array('pager' => $pager)) ?>

<div class="actions section">

  <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

  <div class="content">
    <ul class="clearfix links">
      <?php if (QubitAcl::check($resource, 'createTerm')): ?>
        <li><?php echo link_to(__('Add new'), array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array($resource, 'module' => 'taxonomy')))) ?></li>
      <?php endif; ?>
    </ul>
  </div>

</div>
