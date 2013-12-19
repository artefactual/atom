<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>
  <h1><?php echo __('List %1%', array('%1%' => render_title($resource))) ?></h1>
<?php end_slot() ?>

<?php slot('before-content') ?>

  <section class="header-options">
    <div class="row">
      <div class="span6">
        <?php echo get_component('search', 'inlineSearch', array(
          'label' => __('Search %1%', array('%1%' => render_title($resource))),
          'route' => url_for(array('module' => 'taxonomy', 'action' => 'index', 'slug' => $resource->slug)),
          'fields' => array('All labels', 'Preferred label', '\'Use for\' labels'))) ?>
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
      <?php foreach ($pager->getResults() as $hit): ?>
        <?php $doc = $hit->getData() ?>
        <tr>
          <td>
            <?php if ($doc['isProtected']): ?>
              <?php echo link_to(get_search_i18n($doc, 'name', true, false), array('module' => 'term', 'slug' => $doc['slug']), array('class' => 'readOnly')) ?>
            <?php else: ?>
              <?php echo link_to(get_search_i18n($doc, 'name', true, false), array('module' => 'term', 'slug' => $doc['slug'])) ?>
            <?php endif; ?>

            <?php if (0 < $doc['numberOfDescendants']): ?>
              <span class="note2">(<?php echo $doc['numberOfDescendants'] ?>)</span>
            <?php endif; ?>

            <?php if (isset($doc['useFor']) && count($doc['useFor']) > 0): ?>
              <p><?php echo 'Use for: '.get_search_i18n(array_pop($doc['useFor']), 'name', true, false) ?><?php foreach ($doc['useFor'] as $label): ?><?php echo ', '.get_search_i18n($label, 'name', true, false) ?><?php endforeach; ?></p>
            <?php endif; ?>

          </td><td>
            <?php if (isset($doc['scopeNotes']) && count($doc['scopeNotes']) > 0): ?>
              <ul>
                <?php foreach ($doc['scopeNotes'] as $note): ?>
                  <li><?php echo get_search_i18n($note, 'content') ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
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
