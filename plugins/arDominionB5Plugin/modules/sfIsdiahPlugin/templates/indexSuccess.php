<?php decorate_with('layout_3col'); ?>

<?php slot('sidebar'); ?>
  <?php include_component('repository', 'contextMenu'); ?>
  <?php include_component('repository', 'maintainedActors', ['resource' => $resource]); ?>
<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo render_title($resource); ?></h1>

  <?php if (isset($errorSchema)) { ?>
    <div class="alert alert-danger" role="alert">
      <ul class="<?php echo render_b5_show_list_css_classes(); ?>">
        <?php foreach ($errorSchema as $error) { ?>
          <?php $error = sfOutputEscaper::unescape($error); ?>
          <li><?php echo $error->getMessage(); ?></li>
        <?php } ?>
      </ul>
    </div>
  <?php } ?>

  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><?php echo link_to(esc_specialchars(sfConfig::get('app_ui_label_repository')), ['module' => 'repository', 'action' => 'browse']); ?></li>
      <li class="breadcrumb-item active" aria-current="page"><?php echo render_title($resource); ?></li>
    </ol>
  </nav>

  <?php if ($resource->existsBanner()) { ?>
    <div class="row mb-3" id="repository-banner">
      <div class="col-md-9">
        <?php echo image_tag($resource->getBannerPath(), ['alt' => '', 'class' => 'img-fluid rounded']); ?>
      </div>
    </div>
  <?php } ?>

  <?php if ($sf_data->getRaw('htmlSnippet')) { ?>
    <div class="row" id="repository-html-snippet">
      <div class="col-md-9">
        <?php echo render_value_html($sf_data->getRaw('htmlSnippet')); ?>
      </div>
    </div>
  <?php } ?>

  <?php echo get_component('default', 'translationLinks', ['resource' => $resource]); ?>

<?php end_slot(); ?>

<?php slot('context-menu'); ?>

  <nav>

    <h4 class="h5 mb-2"><?php echo __('Clipboard'); ?></h4>
    <ul class="list-unstyled">
      <li>
        <?php echo get_component('clipboard', 'button', ['slug' => $resource->slug, 'wide' => true, 'type' => 'informationObject']); ?>
      </li>
    </ul>

    <?php if (isset($primaryContact)) { ?>
      <section id="primary-contact" class="mb-3">
        <h4 class="h5 mb-2"><?php echo __('Primary contact'); ?></h4>
        <?php echo render_value($sf_data->getRaw('primaryContact')->getContactInformationString(['simple' => true])); ?>
        <div class="d-flex gap-2 flex-wrap">
          <?php if (null !== $website = $primaryContact->getWebsite()) { ?>
            <?php if (null === parse_url($website, PHP_URL_SCHEME)) { ?>
              <?php $website = 'http://'.$website; ?>
            <?php } ?>
            <a class="btn atom-btn-white" href="<?php echo esc_entities($website); ?>"><?php echo __('Website'); ?></a>
          <?php } ?>
          <?php if (null !== $email = $primaryContact->email) { ?>
            <a class="btn atom-btn-white" href="mailto:<?php echo esc_entities($email); ?>"><?php echo __('Email'); ?></a>
          <?php } ?>
        </div>
      </section>
    <?php } ?>

  </nav>

<?php end_slot(); ?>

<?php if (isset($latitude, $longitude) && $mapApiKey = sfConfig::get('app_google_maps_api_key')) { ?>
  <style <?php echo __(sfConfig::get('csp_nonce', '')); ?>></style>
  <div class="p-1 border-bottom">
    <div id="front-map" class="simple-map" data-key="<?php echo $mapApiKey; ?>" data-latitude="<?php echo $latitude; ?>" data-longitude="<?php echo $longitude; ?>"></div>
  </div>
<?php } ?>

<?php
    // TODO: Move this to the controller when we only have B5 themes
    $headingsCondition = SecurityPrivileges::editCredentials($sf_user, 'repository');
    $headingsUrl = [$resource, 'module' => 'repository', 'action' => 'edit'];
?>

<section id="identifyArea" class="border-bottom">

  <?php echo render_b5_section_heading(
      __('Identity area'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'identity-collapse', 'class' => 'rounded-top']
  ); ?>

  <?php echo render_show(__('Identifier'), $resource->identifier); ?>

  <?php echo render_show(__('Authorized form of name'), render_value_inline($resource)); ?>

  <?php echo render_show(__('Parallel form(s) of name'), $resource->getOtherNames(['typeId' => QubitTerm::PARALLEL_FORM_OF_NAME_ID])); ?>

  <?php echo render_show(__('Other form(s) of name'), $resource->getOtherNames(['typeId' => QubitTerm::OTHER_FORM_OF_NAME_ID])); ?>

  <?php
      $terms = [];
      foreach ($resource->getTermRelations(QubitTaxonomy::REPOSITORY_TYPE_ID) as $item) {
          $terms[] = $item->term;
      }
      echo render_show(__('Type'), $terms);
  ?>

</section>

<section id="contactArea" class="border-bottom">

  <?php echo render_b5_section_heading(
      __('Contact area'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'contact-collapse']
  ); ?>

  <?php foreach ($resource->contactInformations as $contactItem) { ?>
    <?php echo get_partial('contactinformation/contactInformation', ['contactInformation' => $contactItem]); ?>
  <?php } ?>

</section>

<section id="descriptionArea" class="border-bottom">

  <?php echo render_b5_section_heading(
      __('Description area'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'description-collapse']
  ); ?>

  <?php echo render_show(__('History'), render_value($resource->getHistory(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Geographical and cultural context'), render_value($resource->getGeoculturalContext(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Mandates/Sources of authority'), render_value($resource->getMandates(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Administrative structure'), render_value($resource->getInternalStructures(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Records management and collecting policies'), render_value($resource->getCollectingPolicies(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Buildings'), render_value($resource->getBuildings(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Holdings'), render_value($resource->getHoldings(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Finding aids, guides and publications'), render_value($resource->getFindingAids(['cultureFallback' => true]))); ?>

</section>

<section id="accessArea" class="border-bottom">

  <?php echo render_b5_section_heading(
      __('Access area'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'access-collapse']
  ); ?>

  <?php echo render_show(__('Opening times'), render_value($resource->getOpeningTimes(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Access conditions and requirements'), render_value($resource->getAccessConditions(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Accessibility'), render_value($resource->getDisabledAccess(['cultureFallback' => true]))); ?>

</section>

<section id="servicesArea" class="border-bottom">

  <?php echo render_b5_section_heading(
      __('Services area'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'services-collapse']
  ); ?>

  <?php echo render_show(__('Research services'), render_value($resource->getResearchServices(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Reproduction services'), render_value($resource->getReproductionServices(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Public areas'), render_value($resource->getPublicFacilities(['cultureFallback' => true]))); ?>

</section>

<section id="controlArea" class="border-bottom">

  <?php echo render_b5_section_heading(
      __('Control area'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'control-collapse']
  ); ?>

  <?php echo render_show(__('Description identifier'), render_value_inline($resource->descIdentifier)); ?>

  <?php echo render_show(__('Institution identifier'), render_value_inline($resource->getDescInstitutionIdentifier(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Rules and/or conventions used'), render_value($resource->getDescRules(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Status'), render_value_inline($resource->descStatus)); ?>

  <?php echo render_show(__('Level of detail'), render_value_inline($resource->descDetail)); ?>

  <?php echo render_show(__('Dates of creation, revision and deletion'), render_value($resource->getDescRevisionHistory(['cultureFallback' => true]))); ?>

  <?php
      $languages = [];
      foreach ($resource->language as $code) {
          $languages[] = format_language($code);
      }
      echo render_show(__('Language(s)'), $languages);
  ?>

  <?php
      $scripts = [];
      foreach ($resource->script as $code) {
          $scripts[] = format_script($code);
      }
      echo render_show(__('Script(s)'), $scripts);
  ?>

  <?php echo render_show(__('Sources'), render_value($resource->getDescSources(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Maintenance notes'), render_value($isdiah->_maintenanceNote)); ?>

</section>

<section id="accessPointsArea" class="border-bottom">

  <?php echo render_b5_section_heading(
      __('Access points'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'points-collapse']
  ); ?>

  <div class="field <?php echo render_b5_show_field_css_classes(); ?>">
    <?php echo render_b5_show_label(__('Access Points')); ?>
    <div class="<?php echo render_b5_show_value_css_classes(); ?>">
      <ul class="<?php echo render_b5_show_list_css_classes(); ?>">
        <?php foreach ($resource->getTermRelations(QubitTaxonomy::THEMATIC_AREA_ID) as $item) { ?>
          <li><?php echo __(render_value_inline($item->term)); ?> (Thematic area)</li>
        <?php } ?>
        <?php foreach ($resource->getTermRelations(QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID) as $item) { ?>
          <li><?php echo __(render_value_inline($item->term)); ?> (Geographic subregion)</li>
        <?php } ?>
      </ul>
    </div>
  </div>

</section>

<?php if (QubitAcl::check($resource, ['update', 'delete', 'create'])) { ?>

  <?php slot('after-content'); ?>

    <ul class="actions mb-3 nav gap-2">
      <?php if (QubitAcl::check($resource, 'update') || QubitAcl::check($resource, 'translate')) { ?>
        <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'repository', 'action' => 'edit'], ['class' => 'btn atom-btn-outline-light']); ?></li>
      <?php } ?>
      <?php if (QubitAcl::check($resource, 'delete')) { ?>
        <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'repository', 'action' => 'delete'], ['class' => 'btn atom-btn-outline-danger']); ?></li>
      <?php } ?>
      <?php if (QubitAcl::check($resource, 'create')) { ?>
        <li><?php echo link_to(__('Add new'), ['module' => 'repository', 'action' => 'add'], ['class' => 'btn atom-btn-outline-light']); ?></li>
      <?php } ?>
      <?php if (QubitAcl::check(QubitInformationObject, 'create')) { ?>
        <li><?php echo link_to(__('Add description'), ['module' => 'informationobject', 'action' => 'add', 'repository' => $resource->id], ['class' => 'btn atom-btn-outline-light']); ?></li>
      <?php } ?>
      <?php if (QubitAcl::check($resource, 'update') || QubitAcl::check($resource, 'translate')) { ?>
        <li><?php echo link_to(__('Edit theme'), [$resource, 'module' => 'repository', 'action' => 'editTheme'], ['class' => 'btn atom-btn-outline-light']); ?></li>
      <?php } ?>
    </ul>

  <?php end_slot(); ?>

<?php } ?>

<?php echo get_component('object', 'gaInstitutionsDimension', ['resource' => $resource]); ?>
