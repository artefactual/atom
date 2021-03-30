<?php decorate_with('layout_2col'); ?>

<?php slot('sidebar'); ?>
  <div class="sidebar-lowering">

    <h2><?php echo __('Browse %1%:', ['%1%' => render_title($resource)]); ?></h2>

    <?php echo get_component('term', 'treeView', ['browser' => false]); ?>

 </div>
<?php end_slot(); ?>

<?php slot('title'); ?>
  <div class="multiline-header">
    <?php if (isset($icon)) { ?>
      <?php echo image_tag('/images/icons-large/icon-'.$icon.'.png', ['alt' => '']); ?>
    <?php } ?>
    <h1 aria-describedby="results-label"><?php echo __('Showing %1% results', ['%1%' => $pager->getNbResults()]); ?></h1>
    <span class="sub" id="results-label"><?php echo render_title($resource); ?></span>
  </div>
<?php end_slot(); ?>

<?php slot('before-content'); ?>

  <section class="header-options">
    <div class="row">
      <div class="span5">
        <?php echo get_component('search', 'inlineSearch', [
            'label' => __('Search %1%', ['%1%' => render_title($resource)]),
            'route' => url_for(['module' => 'taxonomy', 'action' => 'index', 'slug' => $resource->slug]),
            'fields' => [
                'allLabels' => __('All labels'),
                'preferredLabel' => __('Preferred label'),
                'useForLabels' => __('"Use for" labels'), ], ]); ?>
      </div>

      <div class="pickers">
        <?php echo get_partial('default/sortPickers',
          [
              'options' => [
                  'lastUpdated' => __('Date modified'),
                  'alphabetic' => __('Name'), ], ]); ?>
      </div>
    </div>
  </section>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <table class="table table-bordered sticky-enabled">
    <thead>
      <tr>
        <th>
          <?php echo __('%1% term', ['%1%' => render_title($resource)]); ?>
        </th><th>
          <?php echo __('Scope note'); ?>
        </th>
        <?php if ($addIoCountColumn) { ?>
          <th><?php echo __('%1 count', ['%1' => sfConfig::get('app_ui_label_informationobject')]); ?></th>
        <?php } ?>
        <?php if ($addActorCountColumn) { ?>
          <th><?php echo __('%1 count', ['%1' => sfConfig::get('app_ui_label_actor')]); ?></th>
        <?php } ?>
      </tr>
    </thead><tbody>
      <?php foreach ($pager->getResults() as $hit) { ?>
        <?php $doc = $hit->getData(); ?>
        <tr>
          <td>
            <?php if ($doc['isProtected']) { ?>
              <?php echo link_to(render_title(get_search_i18n($doc, 'name', ['allowEmpty' => false])), ['module' => 'term', 'slug' => $doc['slug']], ['class' => 'readOnly']); ?>
            <?php } else { ?>
              <?php echo link_to(render_title(get_search_i18n($doc, 'name', ['allowEmpty' => false])), ['module' => 'term', 'slug' => $doc['slug']]); ?>
            <?php } ?>

            <?php if (0 < $doc['numberOfDescendants']) { ?>
              <span class="note2">(<?php echo $doc['numberOfDescendants']; ?>)</span>
            <?php } ?>

            <?php if (isset($doc['useFor']) && count($doc['useFor']) > 0) { ?>
              <p>
                <?php $labels = []; ?>
                <?php echo __('Use for: '); ?>

                <?php foreach ($doc['useFor'] as $label) { ?>
                  <?php $labels[] = render_value_inline(get_search_i18n($label, 'name', ['allowEmpty' => false])); ?>
                <?php } ?>

                <?php echo implode(', ', $labels); ?>
              </p>
            <?php } ?>

          </td><td>
            <?php if (isset($doc['scopeNotes']) && count($doc['scopeNotes']) > 0) { ?>
              <ul>
                <?php foreach ($doc['scopeNotes'] as $note) { ?>
                  <li><?php echo render_value_inline(get_search_i18n($note, 'content')); ?></li>
                <?php } ?>
              </ul>
            <?php } ?>

          </td>
          <?php if ($addIoCountColumn) { ?>
            <td><?php echo QubitTerm::countRelatedInformationObjects($hit->getId()); ?></td>
          <?php } ?>
          <?php if ($addActorCountColumn) { ?>
            <td><?php echo TermNavigateRelatedComponent::getEsDocsRelatedToTermCount('QubitActor', $hit->getId()); ?></td>
          <?php } ?>
        </tr>
      <?php } ?>
    </tbody>
  </table>

<?php end_slot(); ?>

<?php slot('after-content'); ?>

  <?php echo get_partial('default/pager', ['pager' => $pager]); ?>

  <section class="actions">
    <ul>
      <?php if (QubitAcl::check($resource, 'createTerm')) { ?>
        <li><?php echo link_to(__('Add new'), ['module' => 'term', 'action' => 'add', 'taxonomy' => url_for([$resource, 'module' => 'taxonomy'])], ['class' => 'c-btn']); ?></li>
      <?php } ?>
    </ul>
  </section>

<?php end_slot(); ?>
