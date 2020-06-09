<?php decorate_with('layout_3col') ?>

<?php slot('sidebar') ?>
  <?php include_component('informationobject', 'contextMenu') ?>
<?php end_slot() ?>

<?php slot('title') ?>

  <?php echo get_component('informationobject', 'descriptionHeader', array('resource' => $resource, 'title' => (string)$dc, 'hideLevelOfDescription' => true)) ?>

  <?php if (isset($errorSchema)): ?>
    <div class="messages error">
      <ul>
        <?php foreach ($errorSchema as $error): ?>
          <?php $error = sfOutputEscaper::unescape($error) ?>
          <li><?php echo $error->getMessage() ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (QubitInformationObject::ROOT_ID != $resource->parentId): ?>
    <?php echo include_partial('default/breadcrumb', array('resource' => $resource, 'objects' => $resource->getAncestors()->andSelf()->orderBy('lft'))) ?>
  <?php endif; ?>

  <?php echo get_component('default', 'translationLinks', array('resource' => $resource)) ?>

<?php end_slot() ?>

<?php slot('context-menu') ?>

  <?php echo get_partial('informationobject/actionIcons', array('resource' => $resource)) ?>

  <?php echo get_partial('object/subjectAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

  <?php echo get_partial('informationobject/nameAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

  <?php echo get_partial('object/placeAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

  <?php if (check_field_visibility('app_element_visibility_physical_storage')): ?>
    <?php echo get_component('physicalobject', 'contextMenu', array('resource' => $resource)) ?>
  <?php endif; ?>

<?php end_slot() ?>

<?php slot('before-content') ?>

  <?php echo get_component('digitalobject', 'imageflow', array('resource' => $resource)) ?>

<?php end_slot() ?>

<?php if (0 < count($resource->digitalObjectsRelatedByobjectId)): ?>
  <?php echo get_component('digitalobject', 'show', array('link' => $digitalObjectLink, 'resource' => $resource->digitalObjectsRelatedByobjectId[0], 'usageType' => QubitTerm::REFERENCE_ID)) ?>
<?php endif; ?>

<section id="elementsArea">

  <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Elements area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'mainArea', 'title' => __('Edit elements area'))) ?>

  <?php echo render_show(__('Identifier'), $resource->identifier) ?>

  <?php echo render_show(__('Title'), render_value($resource->getTitle(array('cultureFallback' => true)))) ?>

  <?php $actorsShown = array(); ?>
  <?php  foreach ($resource->getCreators() as $item): ?>
    <?php if (!isset($actorsShown[$item->id])): ?>
      <div class="field">
        <h3><?php echo __('Creator') ?></h3>
        <div>
          <?php echo link_to(render_title($item), array($item, 'module' => 'actor')) ?><?php if (0 < strlen($value = $item->getDatesOfExistence(array('cultureFallback' => true)))): ?> <span class="note2">(<?php echo $value ?>)</span><?php endif; ?>
        </div>
      </div>
      <?php $actorsShown[$item->id] = true; ?>
    <?php endif; ?>
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
    <?php echo render_show(__('Subject'), link_to(render_title($item->term), array($item->term, 'module' => 'term'))) ?>
  <?php endforeach; ?>

  <?php echo render_show(__('Description'), render_value($resource->getScopeAndContent(array('cultureFallback' => true)))) ?>

  <?php foreach ($dc->type as $item): ?>
    <?php echo render_show(__('Type'), render_value($item)) ?>
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
    <?php echo render_show(__('Coverage (spatial)'), link_to(render_title($item), array($item, 'module' => 'term'))) ?>
  <?php endforeach; ?>

  <?php echo render_show(__('Rights'), render_value($resource->getAccessConditions(array('cultureFallback' => true)))) ?>

</section> <!-- /section#elementsArea -->

<?php if ($sf_user->isAuthenticated()): ?>

  <section id="rightsArea">

    <?php if (QubitAcl::check($resource, 'update')): ?>
      <h2><?php echo __('Rights area') ?> </h2>
    <?php endif; ?>

    <?php echo get_component('right', 'relatedRights', array('resource' => $resource)) ?>

  </section> <!-- /section#rightsArea -->

<?php endif; ?>

<?php if (0 < count($resource->digitalObjectsRelatedByobjectId)): ?>

  <?php echo get_component('digitalobject', 'metadata', array('resource' => $resource->digitalObjectsRelatedByobjectId[0], 'object' => $resource)) ?>

  <?php echo get_partial('digitalobject/rights', array('resource' => $resource->digitalObjectsRelatedByobjectId[0])) ?>

<?php endif; ?>

<section id="accessionArea">

  <h2><?php echo __('Accession area') ?></h2>

  <?php echo get_component('informationobject', 'accessions', array('resource' => $resource)) ?>

</section> <!-- /section#accessionArea -->

<?php slot('after-content') ?>
  <?php echo get_partial('informationobject/actions', array('resource' => $resource)) ?>
<?php end_slot() ?>

<?php echo get_component('object', 'gaInstitutionsDimension', array('resource' => $resource)) ?>
