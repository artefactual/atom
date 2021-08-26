<?php decorate_with('layout_1col'); ?>

<?php slot('title'); ?>
  <div class="d-flex flex-wrap gap-3 mb-3">
    <div class="multiline-header d-inline-flex align-items-center me-2">
      <i class="fas fa-3x fa-list-alt me-3" aria-hidden="true"></i>
      <div class="d-flex flex-column">
        <h1 class="mb-0" aria-describedby="heading-label">
          <?php echo render_title($resource); ?>
        </h1>
        <span class="small" id="heading-label">
          <?php echo __('Inventory list'); ?>
        </span>
      </div>
    </div>

    <div class="ms-auto">
      <?php echo link_to(__('Return to archival description'), [$resource, 'module' => 'informationobject'], ['class' => 'btn btn-sm atom-btn-white text-wrap']); ?>
    </div>
  </div>

  <?php if (QubitInformationObject::ROOT_ID != $resource->parentId) { ?>
    <?php echo include_partial('default/breadcrumb', ['resource' => $resource, 'objects' => $resource->getAncestors()->andSelf()->orderBy('lft')]); ?>
  <?php } ?>
<?php end_slot(); ?>

<?php slot('content'); ?>
    <?php if ($pager->getNbResults()) { ?>
      <div class="table-responsive mb-3">
        <table class="table table-bordered mb-0">
          <tr class="text-nowrap">
            <?php echo get_partial('default/sortableTableHeader',
              ['label' => __('Identifier'), 'name' => 'identifier', 'size' => '14%', 'default' => 'up']); ?>
            <?php echo get_partial('default/sortableTableHeader',
              ['label' => __('Title'), 'name' => 'title', 'size' => '40%']); ?>
            <?php echo get_partial('default/sortableTableHeader',
              ['label' => __('Level of description'), 'name' => 'level', 'size' => '14%']); ?>
            <?php echo get_partial('default/sortableTableHeader',
              ['label' => __('Date'), 'name' => 'date', 'size' => '24%']); ?>
            <th width="8%"><?php echo sfConfig::get('app_ui_label_digitalobject'); ?></th>
            <th></th>
          </tr>
          <?php foreach ($pager->getResults() as $hit) { ?>
            <?php $doc = $hit->getData(); ?>
            <tr>
              <td>
                <?php echo $doc['identifier']; ?>
              </td>
              <td><?php echo link_to(render_value_inline(get_search_i18n($doc, 'title')), ['module' => 'informationobject', 'slug' => $doc['slug']]); ?></td>
              <td>
                <?php $level = QubitTerm::getById($doc['levelOfDescriptionId']); ?>
                <?php if (null !== $level) { ?>
                  <?php echo $level->getName(); ?>
                <?php } ?>
              </td>
              <td>
                <?php echo render_value_inline(render_search_result_date($doc['dates'])); ?>
              </td>
              <td>
                <?php if ($doc['hasDigitalObject']) { ?>
                  <?php if (null !== $io = QubitInformationObject::getById($hit->getId())) { ?>
                    <?php if (null !== $link = $io->getDigitalObjectUrl()) { ?>
                      <?php echo link_to(__('View'), $link, ['class' => 'btn atom-btn-white']); ?>
                    <?php } ?>
                  <?php } ?>
                <?php } ?>
              </td>
              <td>
                <?php echo get_component('clipboard', 'button', ['slug' => $doc['slug'], 'wide' => true, 'type' => 'informationObject']); ?>
              </td>
            </tr>
          <?php } ?>
        </table>
      </div>

      <section>
        <?php echo get_partial('default/pager', ['pager' => $pager]); ?>
      </section>
    <?php } else { ?>

      <div id="content" class="p-3">
        <?php echo __('We couldn\'t find any results matching your search.'); ?>
      </div>

    <?php } ?>

  </div>
</div>
<?php end_slot(); ?>
