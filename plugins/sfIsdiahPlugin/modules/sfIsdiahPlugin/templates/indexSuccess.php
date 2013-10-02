<?php decorate_with('layout_3col') ?>

<?php slot('sidebar') ?>
  <?php include_component('repository', 'contextMenu') ?>
<?php end_slot() ?>

<?php slot('google_analytics') ?>
  _gaq.push(['_setCustomVar', 1, 'repository', '<?php echo $resource->slug ?>']);
<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo render_title($resource) ?></h1>

  <?php if (isset($errorSchema)): ?>
    <div class="messages error">
      <ul>
        <?php foreach ($errorSchema as $error): ?>
          <li><?php echo $error ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <section class="breadcrumb">
    <ul>
      <li><?php echo link_to(sfConfig::get('app_ui_label_repository'), array('module' => 'repository', 'action' => 'browse')) ?></li>
      <li><span><?php echo render_title($resource) ?></span></li>
    </ul>
  </section>

  <?php if ($resource->existsBanner()): ?>
    <div class="row" id="repository-banner">
      <div class="span7">
        <?php echo image_tag($resource->getBannerPath()) ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if (null !== $resource->htmlSnippet && null !== $resource->htmlSnippet->getValue()): ?>
    <div class="row" id="repository-html-snippet">
      <div class="span7">
        <?php echo $resource->htmlSnippet ?>
      </div>
    </div>
  <?php endif; ?>

<?php end_slot() ?>

<?php slot('context-menu') ?>

  <?php if (isset($primaryContact)): ?>
    <section id="primary-contact">
      <h4><?php echo __('Primary contact') ?></h4>
      <?php echo $primaryContact->getContactInformationString(array('simple' => true)) ?>
      <div class="context-actions">
        <?php if (null !== $website = $primaryContact->getWebsite()): ?>
          <a class="btn btn-small" href="<?php echo esc_entities($website) ?>"><?php echo __('Website') ?></a>
        <?php endif; ?>
        <?php if (null !== $email = $primaryContact->email): ?>
          <a class="btn btn-small" href="mailto:<?php echo esc_entities($email) ?>"><?php echo __('Email') ?></a>
        <?php endif; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if (null !== $openingTimes = $resource->getOpeningTimes(array('cultureFallback' => true))): ?>
    <section id="opening-times">
      <h4><?php echo __('Opening times') ?></h4>
      <?php echo render_value($openingTimes) ?>
    </section>
  <?php endif; ?>

<?php end_slot() ?>

<?php if (isset($latitude) && isset($longitude) && null !== $key = sfConfig::get('app_google_maps_api_key')): ?>
  <div id="front-map" class="simple-map" data-key="<?php echo $key ?>" data-latitude="<?php echo $latitude ?>" data-longitude="<?php echo $longitude ?>"></div>
<?php endif; ?>

<section id="identifyArea">

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'repository'), '<h2>'.__('Identity area').'</h2>', array($resource, 'module' => 'repository', 'action' => 'edit'), array('anchor' => 'identityArea', 'title' => __('Edit identity area'))) ?>

  <?php echo render_show(__('Identifier'), render_value($resource->identifier)) ?>

  <?php echo render_show(__('Authorized form of name'), render_value($resource)) ?>

  <div class="field">
    <h3><?php echo __('Parallel form(s) of name') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->getOtherNames(array('typeId' => QubitTerm::PARALLEL_FORM_OF_NAME_ID)) as $item): ?>
          <li><?php echo render_value($item->__toString()) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="field">
    <h3><?php echo __('Other form(s) of name') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->getOtherNames(array('typeId' => QubitTerm::OTHER_FORM_OF_NAME_ID)) as $item): ?>
          <li><?php echo render_value($item->__toString()) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="field">
    <h3><?php echo __('Type') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->getTermRelations(QubitTaxonomy::REPOSITORY_TYPE_ID) as $item): ?>
          <li><?php echo render_value($item->term->__toString()) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

</section>

<section id="contactArea">

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'repository'), '<h2>'.__('Contact area').'</h2>', array($resource, 'module' => 'repository', 'action' => 'edit'), array('anchor' => 'contactArea', 'title' => __('Edit contact area'))) ?>

  <?php foreach ($resource->contactInformations as $contactItem): ?>
    <?php echo get_partial('contactinformation/contactInformation', array('contactInformation' => $contactItem)) ?>
  <?php endforeach; ?>

</section>

<section id="descriptionArea">

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'repository'), '<h2>'.__('Description area').'</h2>', array($resource, 'module' => 'repository', 'action' => 'edit'), array('anchor' => 'descriptionArea', 'title' => __('Edit description area'))) ?>

  <?php echo render_show(__('History'), render_value($resource->getHistory(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Geographical and cultural context'), render_value($resource->getGeoculturalContext(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Mandates/Sources of authority'), render_value($resource->getMandates(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Administrative structure'), render_value($resource->getInternalStructures(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Records management and collecting policies'), render_value($resource->getCollectingPolicies(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Buildings'), render_value($resource->getBuildings(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Holdings'), render_value($resource->getHoldings(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Finding aids, guides and publications'), render_value($resource->getFindingAids(array('cultureFallback' => true)))) ?>

</section>

<section id="accessArea">

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'repository'), '<h2>'.__('Access area').'</h2>', array($resource, 'module' => 'repository', 'action' => 'edit'), array('anchor' => 'accessArea', 'title' => __('Edit access area'))) ?>

  <?php echo render_show(__('Opening times'), render_value($resource->getOpeningTimes(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Access conditions and requirements'), render_value($resource->getAccessConditions(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Accessibility'), render_value($resource->getDisabledAccess(array('cultureFallback' => true)))) ?>

</section>

<section id="servicesArea">

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'repository'), '<h2>'.__('Services area').'</h2>', array($resource, 'module' => 'repository', 'action' => 'edit'), array('anchor' => 'servicesArea', 'title' => __('Edit services area'))) ?>

  <?php echo render_show(__('Research services'), render_value($resource->getResearchServices(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Reproduction services'), render_value($resource->getReproductionServices(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Public areas'), render_value($resource->getPublicFacilities(array('cultureFallback' => true)))) ?>

</section>

<section id="controlArea">

  <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'repository'), '<h2>'.__('Control area').'</h2>', array($resource, 'module' => 'repository', 'action' => 'edit'), array('anchor' => 'controlArea', 'title' => __('Edit control area'))) ?>

  <?php echo render_show(__('Description identifier'), render_value($resource->descIdentifier)) ?>

  <?php echo render_show(__('Institution identifier'), render_value($resource->getDescInstitutionIdentifier(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Rules and/or conventions used'), render_value($resource->getDescRules(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Status'), render_value($resource->descStatus)) ?>

  <?php echo render_show(__('Level of detail'), render_value($resource->descDetail)) ?>

  <?php echo render_show(__('Dates of creation, revision and deletion'), render_value($resource->getDescRevisionHistory(array('cultureFallback' => true)))) ?>

  <div class="field">
    <h3><?php echo __('Language(s)') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->language as $code): ?>
          <li><?php echo format_language($code) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="field">
    <h3><?php echo __('Script(s)') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->script as $code): ?>
          <li><?php echo format_script($code) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <?php echo render_show(__('Sources'), render_value($resource->getDescSources(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Maintenance notes'), render_value($isdiah->_maintenanceNote)) ?>

</section>


<?php if (QubitAcl::check($resource, array('update', 'delete', 'create'))): ?>

  <?php slot('after-content') ?>

    <section class="actions">
      <ul>
        <?php if (QubitAcl::check($resource, 'update')): ?>
          <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'repository', 'action' => 'edit'), array('class' => 'c-btn', 'title' => __('Edit'))) ?></li>
        <?php endif; ?>
        <?php if (QubitAcl::check($resource, 'delete')): ?>
          <li><?php echo link_to(__('Delete'), array($resource, 'module' => 'repository', 'action' => 'delete'), array('class' => 'c-btn c-btn-delete', 'title' => __('Delete'))) ?></li>
        <?php endif; ?>
        <?php if (QubitAcl::check($resource, 'create')): ?>
          <li><?php echo link_to(__('Add new'), array('module' => 'repository', 'action' => 'add'), array('class' => 'c-btn', 'title' => __('Add new'))) ?></li>
        <?php endif; ?>
        <li class="divider"></li>
        <?php if (QubitAcl::check($resource, 'update')): ?>
          <li><?php echo link_to(__('Edit theme'), array($resource, 'module' => 'repository', 'action' => 'editTheme'), array('class' => 'c-btn', 'title' => 'Edit theme')) ?>
        <?php endif; ?>
      </ul>
    </section>

  <?php end_slot() ?>

<?php endif; ?>
