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
            array('label' => __('Identifier'), 'name' => 'identifier', 'size' => '15%', 'default' => 'up')) ?>
          <?php echo get_partial('default/sortableTableHeader',
            array('label' => __('Title'), 'name' => 'title', 'size' => '45%')) ?>
          <?php echo get_partial('default/sortableTableHeader',
            array('label' => __('Level of description'), 'name' => 'level', 'size' => '15%')) ?>
          <?php echo get_partial('default/sortableTableHeader',
            array('label' => __('Date'), 'name' => 'date', 'size' => '25%')) ?>
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
              <?php if (!empty($doc['dates'])): ?>
                <ul>
                  <?php foreach ($doc['dates']->getRawValue() as $date): ?>
                    <li><?php echo render_es_event_date($date) ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
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
