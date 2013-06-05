<?php decorate_with('layout_3col') ?>

<?php slot('sidebar') ?>
  <?php include_component('informationobject', 'contextMenu') ?>
<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo render_title($dc) ?></h1>

  <?php echo get_partial('informationobject/printPreviewBar', array('resource' => $resource)) ?>

  <?php if (isset($errorSchema)): ?>
    <div class="messages error">
      <ul>
        <?php foreach ($errorSchema as $error): ?>
          <li><?php echo $error ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (QubitInformationObject::ROOT_ID != $resource->parentId): ?>
    <?php echo include_partial('default/breadcrumb', array('resource' => $resource, 'objects' => $resource->getAncestors()->andSelf()->orderBy('lft'))) ?>
  <?php endif; ?>

<?php end_slot() ?>

<?php slot('context-menu') ?>

  <?php echo get_partial('informationobject/actionIcons', array('resource' => $resource)) ?>

  <?php if (false): ?>
  <?php $repository = $resource->getRepository(array('inherit' => true)) ?>
  <?php if (null !== $repository && null !== ($contactInformations = $repository->contactInformations)): ?>
    <section>

      <h4><?php echo __('How to access to this content?') ?></h4>

      <div class="content">
        <?php echo __('Contact the archivist at %1%', array('%1%' => $repository->__toString())) ?>
        <a href="#contact-modal" class="btn btn-small" role="button" data-target="#contact-modal" data-backdrop="true" data-toggle="modal"><?php echo __('Show details') ?></a>
      </div>

      <div class="modal hide fade" id="contact-modal" tabindex="-1" role="dialog" aria-labelledby="contact-modal-label" aria-hidden="true">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h3 id="contact-modal-label"><?php echo __('How to access to this content?') ?></h3>
        </div>
        <div class="modal-body">
         <?php foreach ($contactInformations as $contactItem): ?>
            <?php echo get_partial('contactinformation/contactInformation', array('contactInformation' => $contactItem)) ?>
          <?php endforeach; ?>
        </div>
        <div class="modal-footer">
          <button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo __('Close') ?></button>
        </div>
      </div>

    </section>
  <?php endif; ?>
  <?php endif; ?>

  <?php echo get_partial('informationobject/subjectAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

  <?php echo get_partial('informationobject/nameAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

  <?php echo get_partial('informationobject/placeAccessPoints', array('resource' => $resource, 'sidebar' => true)) ?>

  <?php if (check_field_visibility('app_element_visibility_physical_storage')): ?>
    <?php echo get_component('physicalobject', 'contextMenu', array('resource' => $resource)) ?>
  <?php endif; ?>

<?php end_slot() ?>

<?php slot('before-content') ?>

  <?php echo get_component('digitalobject', 'imageflow', array('resource' => $resource)) ?>

<?php end_slot() ?>

<?php if (0 < count($resource->digitalObjects)): ?>
  <?php echo get_component('digitalobject', 'show', array('link' => $digitalObjectLink, 'resource' => $resource->digitalObjects[0], 'usageType' => QubitTerm::REFERENCE_ID)) ?>
<?php endif; ?>

<section id="elementsArea">

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Elements area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'mainArea', 'title' => __('Edit elements area'))) ?>

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

</section> <!-- /section#elementsArea -->

<?php if ($sf_user->isAuthenticated()): ?>

  <section id="rightsArea">

    <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Rights area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'rightsArea', 'title' => __('Edit rights area'))) ?>

    <?php echo get_component('right', 'relatedRights', array('resource' => $resource)) ?>

  </section> <!-- /section#rightsArea -->

<?php endif; ?>

<?php if (0 < count($resource->digitalObjects)): ?>

  <?php echo get_partial('digitalobject/metadata', array('resource' => $resource->digitalObjects[0])) ?>

  <?php echo get_partial('digitalobject/rights', array('resource' => $resource->digitalObjects[0])) ?>

<?php endif; ?>

<section id="accessionArea">

  <h2><?php echo __('Accession area') ?></h2>

  <?php echo get_component('informationobject', 'accessions', array('resource' => $resource)) ?>

</section> <!-- /section#accessionArea -->

<?php slot('after-content') ?>
  <?php echo get_partial('informationobject/actions', array('resource' => $resource)) ?>
<?php end_slot() ?>
