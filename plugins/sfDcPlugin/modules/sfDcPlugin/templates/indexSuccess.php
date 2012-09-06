<?php echo get_partial('informationobject/printPreviewBar', array('resource' => $resource)) ?>

<h1><?php echo __('View resource metadata') ?></h1>

<h1 class="label printable">
  <?php echo link_to_if(QubitAcl::check($resource, 'update'), render_title($dc), array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('title' => __('Edit resource metadata'))) ?>
  <?php echo get_partial('informationobject/actionIcons') ?>
</h1>

<?php if (isset($errorSchema)): ?>
  <div class="messages error">
    <ul>
      <?php foreach ($errorSchema as $error): ?>
        <li><?php echo $error ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if (0 < count($resource->digitalObjects)): ?>
  <?php echo get_component('digitalobject', 'show', array('link' => $digitalObjectLink, 'resource' => $resource->digitalObjects[0], 'usageType' => QubitTerm::REFERENCE_ID)) ?>
<?php endif; ?>

<?php echo render_show(__('Identifier'), render_value($resource->identifier)) ?>

<?php echo render_show(__('Title'), render_value($resource->getTitle(array('cultureFallback' => true)))) ?>

<?php  foreach ($resource->getCreators() as $item): ?>
  <div class="field">
    <h3><?php echo __('Creator') ?></h3>
    <div>
      <?php echo link_to(render_title($item), array($item, 'module' => 'actor')) ?><?php if (0 < strlen($value = $item->getDatesOfExistence(array('cultureFallback' => true)))): ?> <span class="note2">(<?php echo $value ?>)</span><?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>

<?php  foreach ($resource->getPublishers() as $item): ?>
  <div class="field">
    <h3><?php echo __('Publisher') ?></h3>
    <div>
      <?php echo link_to(render_title($item), array($item, 'module' => 'actor')) ?><?php if ($value = $item->getDatesOfExistence(array('cultureFallback' => true))): ?> <span class="note2">(<?php echo $value ?>)</span><?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>

<?php  foreach ($resource->getContributors() as $item): ?>
  <div class="field">
    <h3><?php echo __('Contributor') ?></h3>
    <div>
      <?php echo link_to(render_title($item), array($item, 'module' => 'actor')) ?><?php if ($value = $item->getDatesOfExistence(array('cultureFallback' => true))): ?> <span class="note2">(<?php echo $value ?>)</span><?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>

<?php echo get_partial('informationobject/dates', array('resource' => $resource)) ?>

<?php foreach ($resource->getSubjectAccessPoints() as $item): ?>
  <?php echo render_show(__('Subject'), link_to($item->term, array($item->term, 'module' => 'term', 'action' => 'browseTerm'))) ?>
<?php endforeach; ?>

<?php echo render_show(__('Description'), render_value($resource->getScopeAndContent(array('cultureFallback' => true)))) ?>

<?php foreach ($dc->type as $item): ?>
  <?php echo render_show(__('Type'), $item) ?>
<?php endforeach; ?>

<?php foreach ($dc->format as $item): ?>
  <?php echo render_show(__('Format'), render_value($item)) ?>
<?php endforeach; ?>

<?php echo render_show(__('Source'), render_value($resource->getLocationOfOriginals(array('cultureFallback' => true)))) ?>

<?php foreach ($resource->language as $code): ?>
  <?php echo render_show(__('Language'), format_language($code)) ?>
<?php endforeach; ?>

<?php echo render_show_repository(__('Relation (isLocatedAt)'), $resource) ?>

<?php foreach ($dc->coverage as $item): ?>
  <?php echo render_show(__('Coverage (spatial)'), link_to(render_title($item), array($item, 'module' => 'term', 'action' => 'browseTerm'))) ?>
<?php endforeach; ?>

<?php echo render_show(__('Rights'), render_value($resource->getAccessConditions(array('cultureFallback' => true)))) ?>

<?php if ($sf_user->isAuthenticated()): ?>

  <div class="section" id="rightsArea">

    <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Rights area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'rightsArea', 'title' => __('Edit rights area'))) ?>

    <?php echo get_component('right', 'relatedRights', array('resource' => $resource)) ?>

  </div> <!-- /.section#rightsArea -->

<?php endif; ?>

<?php if (0 < count($resource->digitalObjects)): ?>

  <?php echo get_partial('digitalobject/metadata', array('resource' => $resource->digitalObjects[0])) ?>

  <?php echo get_partial('digitalobject/rights', array('resource' => $resource->digitalObjects[0])) ?>

<?php endif; ?>

<div class="section" id="accessionArea">

  <h2><?php echo __('Accession area') ?></h2>

  <?php echo get_component('informationobject', 'accessions', array('resource' => $resource)) ?>

</div> <!-- /.section#accessionArea -->

<?php echo get_partial('informationobject/actions', array('resource' => $resource)) ?>
