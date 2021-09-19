<?php decorate_with('layout_2col'); ?>

<?php slot('sidebar'); ?>
  <?php echo get_component('term', 'treeView', ['browser' => false]); ?>
<?php end_slot(); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex align-items-center mb-3">
    <?php if (isset($icon)) { ?>
      <i class="fas fa-3x fa-<?php echo $icon; ?> me-3" aria-hidden="true"></i>
    <?php } ?>
    <div class="d-flex flex-column">
      <h1 class="mb-0" aria-describedby="heading-label">
        <?php echo __('Showing %1% results', ['%1%' => $pager->getNbResults()]); ?>
      </h1>
      <span class="small" id="heading-label">
        <?php echo render_title($resource); ?>
      </span>
    </div>
  </div>
<?php end_slot(); ?>

<?php slot('before-content'); ?>
  <div class="d-flex flex-wrap gap-2 mb-3">
    <?php echo get_component('search', 'inlineSearch', [
        'label' => __('Search %1%', ['%1%' => strtolower(render_title($resource))]),
        'landmarkLabel' => __(render_title($resource)),
        'route' => url_for([
            'module' => 'taxonomy',
            'action' => 'index',
            'slug' => $resource->slug,
        ]),
        'fields' => [
            'allLabels' => __('All labels'),
            'preferredLabel' => __('Preferred label'),
            'useForLabels' => __('"Use for" labels'),
        ],
    ]); ?>

    <div class="d-flex flex-wrap gap-2 ms-auto">
      <?php echo get_partial('default/sortPickers', ['options' => [
          'lastUpdated' => __('Date modified'),
          'alphabetic' => __('Name'),
      ]]); ?>
    </div>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>
  <div class="table-responsive mb-3">
    <table class="table table-bordered mb-0">
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
      </thead>
      <tbody>
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
  </div>
<?php end_slot(); ?>

<?php slot('after-content'); ?>

  <?php echo get_partial('default/pager', ['pager' => $pager]); ?>

  <?php if (QubitAcl::check($resource, 'createTerm')) { ?>
    <section class="actions mb-3">
      <?php echo link_to(__('Add new'), ['module' => 'term', 'action' => 'add', 'taxonomy' => url_for([$resource, 'module' => 'taxonomy'])], ['class' => 'btn atom-btn-outline-light']); ?>
    </section>
  <?php } ?>

<?php end_slot(); ?>
