<?php decorate_with('layout_wide'); ?>

<div class="row-fluid">
  <div class="span12">

    <div class="row-fluid">
      <div class="span6">
        <h1 class="multiline">
          <?php echo render_title($resource); ?>
          <span class="sub">
            <?php echo __('Inventory list'); ?>
          </span>
        </h1>
      </div>
      <div class="span6 h1-side">
        <?php echo link_to(__('Return to archival description &raquo;'), [$resource, 'module' => 'informationobject'], ['class' => 'btn']); ?>
      </div>
    </div>

    <?php if (QubitInformationObject::ROOT_ID != $resource->parentId) { ?>
      <?php echo include_partial('default/breadcrumb', ['resource' => $resource, 'objects' => $resource->getAncestors()->andSelf()->orderBy('lft')]); ?>
    <?php } ?>

    <?php if ($pager->getNbResults()) { ?>

      <table class="table table-bordered table-striped">
        <tr>
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
                    <?php echo link_to(__('View'), $link); ?>
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

    <?php } else { ?>

      <div>
        <h2><?php echo __('We couldn\'t find any results matching your search.'); ?></h2>
      </div>

    <?php } ?>

    <section>
      <?php echo get_partial('default/pager', ['pager' => $pager]); ?>
    </section>

  </div>
</div>
