<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>
  <h1><?php echo __('List %1%', array('%1%' => render_title($resource))) ?></h1>
<?php end_slot() ?>

<?php slot('before-content') ?>

  <section class="header-options">
    <div class="row">
      <div class="span6">
        <?php echo get_component('search', 'inlineSearch', array(
          'label' => __('Search %1%', array('%1%' => render_title($resource))))) ?>
      </div>
    </div>
  </section>

<?php end_slot() ?>

<?php slot('content') ?>

  <table class="table table-bordered sticky-enabled">
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
        <tr>
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

<?php end_slot() ?>

<?php slot('after-content') ?>

  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

  <section class="actions">
    <ul>
      <?php if (QubitAcl::check($resource, 'createTerm')): ?>
        <li><?php echo link_to(__('Add new'), array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array($resource, 'module' => 'taxonomy'))), array('class' => 'c-btn')) ?></li>
      <?php endif; ?>
    </ul>
  </section>

<?php end_slot() ?>
