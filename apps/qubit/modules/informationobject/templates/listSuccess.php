<h1><?php echo __('List %1%', array('%1%' => sfConfig::get('app_ui_label_informationobject'))) ?></h1>

<?php if (isset($sf_request->id)): ?>
  <?php echo get_partial('default/breadcrumb', array('objects' => $resource->ancestors->andSelf())) ?>
<?php endif; ?>

<table class="sticky-enabled">
  <thead>
    <tr>
      <th>
        <?php echo __('Title') ?>
      </th><th>
        <?php if (sfConfig::get('app_multi_repository')): ?>
          <?php echo __(sfConfig::get('app_ui_label_repository')) ?>
        <?php else: ?>
          <?php echo __('Creator(s)') ?>
        <?php endif; ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($informationObjects as $item): ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
        <td>
          <?php echo link_to(render_title($item), array($item, 'module' => 'informationobject')) ?><?php if (QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $item->getPublicationStatus()->status->id): ?> <span class="publicationStatus"><?php echo $item->getPublicationStatus()->status ?></span><?php endif; ?>
        </td><td>
          <?php if (sfConfig::get('app_multi_repository')): ?>
            <?php if (isset($item->repository)): ?>
              <?php echo link_to(render_title($item->repository), array($item->repository, 'module' => 'repository')) ?>
            <?php endif; ?>
          <?php else: ?>
            <ul>
              <?php foreach ($item->getCreators() as $creator): ?>
                <li><?php echo link_to(render_title($creator), array($creator, 'module' => 'actor')) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
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
      <?php if (QubitAcl::check($resource, 'create')): ?>
        <li><?php echo link_to(__('Add new'), array('module' => 'informationobject', 'action' => 'add', 'parent' => url_for(array($resource, 'module' => 'informationobject')))) ?></li>
      <?php endif; ?>
    </ul>
  </div>

</div>
