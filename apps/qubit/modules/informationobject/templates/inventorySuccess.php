<?php decorate_with('layout_wide') ?>

<div class="row-fluid">
  <div class="span12">

    <div class="row-fluid">
      <div class="span6">
        <h1 class="multiline">
          <?php echo render_title($resource) ?>
          <span class="sub">
            <?php echo __('Inventory list') ?>
          </span>
        </h1>
      </div>
      <div class="span6 h1-side">
        <?php echo link_to(__('Return to archival description &raquo;'), array($resource, 'module' => 'informationobject'), array('class' => 'btn')) ?>
      </div>
    </div>

    <?php if (QubitInformationObject::ROOT_ID != $resource->parentId): ?>
      <?php echo include_partial('default/breadcrumb', array('resource' => $resource, 'objects' => $resource->getAncestors()->andSelf()->orderBy('lft'))) ?>
    <?php endif; ?>

    <?php if ($pager->hasResults()): ?>

      <table class="table table-bordered table-striped">
        <tr>
          <?php echo get_partial('default/sortableTableHeader',
            array('label' => __('Identifier'), 'name' => 'identifier', 'size' => '14%', 'default' => 'up')) ?>
          <?php echo get_partial('default/sortableTableHeader',
            array('label' => __('Title'), 'name' => 'title', 'size' => '40%')) ?>
          <?php echo get_partial('default/sortableTableHeader',
            array('label' => __('Level of description'), 'name' => 'level', 'size' => '14%')) ?>
          <?php echo get_partial('default/sortableTableHeader',
            array('label' => __('Date'), 'name' => 'date', 'size' => '24%')) ?>
          <th width="8%"><?php echo sfConfig::get('app_ui_label_digitalobject') ?></th>
          <th></th>
        </tr>
        <?php foreach ($pager->getResults() as $hit): ?>
          <?php $doc = $hit->getData() ?>
          <tr>
            <td>
              <?php echo $doc['identifier'] ?>
            </td>
            <td><?php echo link_to(get_search_i18n($doc, 'title'), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?></td>
            <td>
              <?php $level = QubitTerm::getById($doc['levelOfDescriptionId']) ?>
              <?php if ($level !== null): ?>
                <?php echo $level->getName() ?>
              <?php endif; ?>
            </td>
            <td>
              <?php echo render_search_result_date($doc['dates']) ?>
            </td>
            <td>
              <?php if ($doc['hasDigitalObject']): ?>
                <?php if (null !== $io = QubitInformationObject::getById($hit->getId())): ?>
                  <?php if (null !== $link = $io->getDigitalObjectLink()): ?>
                    <?php echo link_to(__('View'), $link) ?>
                  <?php endif; ?>
                <?php endif; ?>
              <?php endif; ?>
            </td>
            <td>
              <?php echo get_component('object', 'clipboardButton', array('slug' => $doc['slug'], 'wide' => true)) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>

    <?php else: ?>

      <div>
        <h2><?php echo __('We couldn\'t find any results matching your search.') ?></h2>
      </div>

    <?php endif; ?>

    <section>
      <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
    </section>

  </div>
</div>
