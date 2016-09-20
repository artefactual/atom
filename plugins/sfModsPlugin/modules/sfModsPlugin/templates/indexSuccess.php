<?php decorate_with('layout_3col') ?>

<?php slot('sidebar') ?>
  <?php include_component('informationobject', 'contextMenu') ?>
<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo render_title($mods) ?></h1>

  <?php if (isset($errorSchema)): ?>
    <div class="messages error">
      <ul>
        <?php foreach ($errorSchema as $error): ?>
          <li><?php echo $error->getMessage(ESC_RAW) ?></li>
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

<section id="elementsArea">

  <?php echo link_to_if(SecurityPrivileges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Elements area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'mainArea', 'title' => __('Edit elements area'))) ?>

  <?php if (0 < count($resource->digitalObjects)): ?>
    <?php echo get_component('digitalobject', 'show', array('link' => $digitalObjectLink, 'resource' => $resource->digitalObjects[0], 'usageType' => QubitTerm::REFERENCE_ID)) ?>
  <?php endif; ?>

  <?php echo render_show(__('Identifier'), render_value($resource->identifier)) ?>

  <?php echo render_show(__('Title'), render_value($resource->getTitle(array('cultureFallback' => true)))) ?>

  <?php echo get_partial('informationobject/dates', array('resource' => $resource)) ?>

  <?php foreach ($mods->typeOfResource as $item): ?>
    <?php echo render_show(__('Type of resource'), $item->term) ?>
  <?php endforeach; ?>

  <?php foreach ($resource->language as $code): ?>
    <?php echo render_show(__('Language'), format_language($code)) ?>
  <?php endforeach; ?>

  <?php if (0 < count($resource->digitalObjects)): ?>
    <?php echo render_show(__('Internet media type'), $resource->digitalObjects[0]->mimeType) ?>
  <?php endif; ?>

  <?php echo get_partial('informationobject/subjectAccessPoints', array('resource' => $resource, 'mods' => true)) ?>

  <?php echo get_partial('informationobject/placeAccessPoints', array('resource' => $resource, 'mods' => true)) ?>

  <?php echo get_partial('informationobject/nameAccessPoints', array('resource' => $resource, 'mods' => true)) ?>

  <?php echo render_show(__('Access condition'), render_value($resource->getAccessConditions(array('cultureFallback' => true)))) ?>

  <?php if (0 < count($resource->digitalObjects)): ?>
    <?php echo render_show(__('URL'), link_to(null, $resource->getDigitalObjectPublicUrl())) ?>
  <?php endif; ?>

  <div class="field">
    <h3><?php echo __('Physical location') ?></h3>
    <div>
      <?php if (isset($resource->repository)): ?>

        <?php if (isset($resource->repository->identifier)): ?>
          <?php echo $resource->repository->identifier ?> -
        <?php endif; ?>

        <?php echo link_to(render_title($resource->repository), array($resource->repository, 'module' => 'repository')) ?>

        <?php if (null !== $contact = $resource->repository->getPrimaryContact()): ?>

          <?php if (isset($contact->city)): ?>
            <?php echo $contact->city ?>
          <?php endif; ?>

          <?php if (isset($contact->region)): ?>
            <?php echo $contact->region ?>
          <?php endif; ?>

          <?php if (isset($contact->countryCode)): ?>
            <?php echo format_country($contact->countryCode) ?>
          <?php endif; ?>

        <?php endif; ?>

      <?php endif; ?>
    </div>
  </div>

  <?php echo render_show(__('Description'), render_value($resource->getScopeAndContent(array('cultureFallback' => true)))) ?>

</section> <!-- /section#elementsArea -->

<?php if ($sf_user->isAuthenticated()): ?>

  <section id="rightsArea">

    <?php if (QubitAcl::check($resource, 'update')): ?>
      <h2><?php echo __('Rights area') ?> </h2>
    <?php endif; ?>

    <?php echo get_component('right', 'relatedRights', array('resource' => $resource)) ?>

  </section> <!-- /section#rightsArea -->

<?php endif; ?>

<?php if (0 < count($resource->digitalObjects)): ?>

  <?php echo get_component('digitalobject', 'metadata', array('resource' => $resource->digitalObjects[0], 'infoObj' => $resource)) ?>

  <?php echo get_partial('digitalobject/rights', array('resource' => $resource->digitalObjects[0])) ?>

<?php endif; ?>

<section id="accessionArea">

  <h2><?php echo __('Accession area') ?></h2>

  <?php echo get_component('informationobject', 'accessions', array('resource' => $resource)) ?>

</section> <!-- /section#accessionArea -->

<?php slot('after-content') ?>
  <?php echo get_partial('informationobject/actions', array('resource' => $resource, 'renameForm' => $renameForm)) ?>
<?php end_slot() ?>
