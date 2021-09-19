<?php decorate_with('layout_3col'); ?>

<?php slot('sidebar'); ?>
  <?php include_component('actor', 'contextMenu', ['resource' => $resource]); ?>
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
      <li class="breadcrumb-item"><?php echo link_to(esc_specialchars(sfConfig::get('app_ui_label_actor')), ['module' => 'actor', 'action' => 'browse']); ?></li>
      <li class="breadcrumb-item active" aria-current="page"><?php echo render_title($resource); ?></li>
    </ol>
  </nav>

  <?php echo get_component('default', 'translationLinks', ['resource' => $resource]); ?>

<?php end_slot(); ?>

<?php slot('context-menu'); ?>

  <?php if ($sf_context->getConfiguration()->isPluginEnabled('sfEacPlugin')) { ?>

    <nav>

      <h4 class="h5 mb-2"><?php echo __('Clipboard'); ?></h4>
      <ul class="list-unstyled">
        <li>
          <?php echo get_component('clipboard', 'button', ['slug' => $resource->slug, 'wide' => true, 'type' => 'actor']); ?>
        </li>
      </ul>

      <h4 class="h5 mb-2"><?php echo __('Export'); ?></h4>
      <ul class="list-unstyled">
        <li>
          <a class="atom-icon-link" href="<?php echo url_for([$resource, 'module' => 'sfEacPlugin', 'sf_format' => 'xml']); ?>">
            <i class="fas fa-fw fa-upload me-1" aria-hidden="true">
            </i><?php echo __('EAC'); ?>
          </a>
        </li>
      </ul>

      <?php echo get_partial('object/subjectAccessPoints', ['resource' => $resource, 'sidebar' => true]); ?>

      <?php echo get_partial('object/placeAccessPoints', ['resource' => $resource, 'sidebar' => true]); ?>

    </nav>

  <?php } ?>

<?php end_slot(); ?>

<?php if (0 < count($resource->digitalObjectsRelatedByobjectId)) { ?>
  <?php echo get_component('digitalobject', 'show', ['link' => $digitalObjectLink, 'resource' => $resource->digitalObjectsRelatedByobjectId[0], 'usageType' => QubitTerm::REFERENCE_ID]); ?>
<?php } ?>

<?php
    // TODO: Move this to the controller when we only have B5 themes
    $headingsCondition = QubitAcl::check($resource, 'update');
    $headingsUrl = [$resource, 'module' => 'actor', 'action' => 'edit'];
?>

<section id="identityArea" class="border-bottom">

  <?php echo render_b5_section_heading(
      __('Identity area'),
      $headingsCondition,
      $headingsUrl,
      [
          'anchor' => 'identity-collapse',
          'class' => 0 < count($resource->digitalObjectsRelatedByobjectId) ? '' : 'rounded-top',
      ]
  ); ?>

  <?php echo render_show(__('Type of entity'), render_value_inline($resource->entityType)); ?>

  <?php echo render_show(__('Authorized form of name'), render_value_inline($resource->getAuthorizedFormOfName(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Parallel form(s) of name'), $resource->getOtherNames(['typeId' => QubitTerm::PARALLEL_FORM_OF_NAME_ID])); ?>

  <?php echo render_show(__('Standardized form(s) of name according to other rules'), $resource->getOtherNames(['typeId' => QubitTerm::STANDARDIZED_FORM_OF_NAME_ID])); ?>

  <?php echo render_show(__('Other form(s) of name'), $resource->getOtherNames(['typeId' => QubitTerm::OTHER_FORM_OF_NAME_ID])); ?>

  <?php echo render_show(__('Identifiers for corporate bodies'), $resource->corporateBodyIdentifiers); ?>

</section> <!-- /section#identityArea -->

<section id="descriptionArea" class="border-bottom">

  <?php echo render_b5_section_heading(
      __('Description area'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'description-collapse']
  ); ?>

  <?php echo render_show(__('Dates of existence'), render_value_inline($resource->getDatesOfExistence(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('History'), render_value($resource->getHistory(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Places'), render_value($resource->getPlaces(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Legal status'), render_value($resource->getLegalStatus(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Functions, occupations and activities'), render_value($resource->getFunctions(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Mandates/sources of authority'), render_value($resource->getMandates(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Internal structures/genealogy'), render_value($resource->getInternalStructures(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('General context'), render_value($resource->getGeneralContext(['cultureFallback' => true]))); ?>

</section> <!-- /section#descriptionArea -->

<section id="relationshipsArea" class="border-bottom">

  <?php echo render_b5_section_heading(
      __('Relationships area'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'relationships-collapse']
  ); ?>

  <?php foreach ($resource->getActorRelations() as $item) { ?>
    <?php $relatedEntity = $item->getOpposedObject($resource->id); ?>
    <div class="field <?php echo render_b5_show_field_css_classes(); ?>">
      <?php echo render_b5_show_label(__('Related entity')); ?>
      <div class="<?php echo render_b5_show_value_css_classes(); ?>">

        <?php echo link_to(render_title($relatedEntity), [$relatedEntity, 'module' => ('QubitRepository' == $relatedEntity->className) ? 'repository' : 'actor']); ?><?php if (isset($relatedEntity->datesOfExistence)) { ?> <span class="note2">(<?php echo render_value_inline($relatedEntity->getDatesOfExistence(['cultureFallback' => true])); ?>)</span><?php } ?>

        <?php echo render_show(__('Identifier of related entity'), render_value_inline($relatedEntity->descriptionIdentifier), ['isSubField' => true]); ?>

        <?php if (QubitTerm::ROOT_ID == $item->type->parentId) { ?>
          <?php echo render_show(__('Category of relationship'), render_value_inline($item->type), ['isSubField' => true]); ?>
        <?php } else { ?>
          <?php echo render_show(__('Category of relationship'), render_value_inline($item->type->parent), ['isSubField' => true]); ?>

          <?php if ($resource->id != $item->objectId) { ?>
            <?php echo render_show(__('Type of relationship'), link_to(render_title($relatedEntity), [$relatedEntity, 'module' => ('QubitRepository' == $relatedEntity->className) ? 'repository' : 'actor']).' '.render_value_inline($item->type).' '.render_value_inline($resource->getAuthorizedFormOfName(['cultureFallback' => true])), ['isSubField' => true]); ?>
          <?php } elseif (0 < count($converseTerms = QubitRelation::getBySubjectOrObjectId($item->type->id, ['typeId' => QubitTerm::CONVERSE_TERM_ID]))) { ?>
            <?php echo render_show(__('Type of relationship'), link_to(render_title($relatedEntity), [$relatedEntity, 'module' => ('QubitRepository' == $relatedEntity->className) ? 'repository' : 'actor']).' '.render_value_inline($converseTerms[0]->getOpposedObject($item->type)).' '.render_value_inline($resource->getAuthorizedFormOfName(['cultureFallback' => true])), ['isSubField' => true]); ?>
          <?php } ?>
        <?php } ?>

        <?php echo render_show(__('Dates of relationship'), render_value_inline(Qubit::renderDateStartEnd($item->date, $item->startDate, $item->endDate)), ['isSubField' => true]); ?>

        <?php echo render_show(__('Description of relationship'), render_value_inline($item->description), ['isSubField' => true]); ?>

      </div>
    </div>
  <?php } ?>

  <?php foreach ($functions as $item) { ?>
    <?php echo render_show(__('Related function'), link_to(render_title($item), [$item, 'module' => 'function'])); ?>
  <?php } ?>

</section> <!-- /section#relationshipsArea -->

<section id="accessPointsArea" class="border-bottom">

  <?php echo render_b5_section_heading(
      __('Access points area'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'access-collapse']
  ); ?>

  <div class="subjectAccessPoints">
    <?php echo get_partial('object/subjectAccessPoints', ['resource' => $resource]); ?>
  </div>

  <div class="placeAccessPoints">
    <?php echo get_partial('object/placeAccessPoints', ['resource' => $resource]); ?>
  </div>

  <div class="field <?php echo render_b5_show_field_css_classes(); ?>">
    <?php echo render_b5_show_label(__('Occupations')); ?>
    <div class="<?php echo render_b5_show_value_css_classes(); ?>">
      <?php foreach ($resource->getOccupations() as $item) { ?>
        <div>
          <?php echo link_to(render_title($item->term), [$item->term, 'module' => 'term']); ?>
          <?php $note = $item->getNotesByType(['noteTypeId' => QubitTerm::ACTOR_OCCUPATION_NOTE_ID])->offsetGet(0); ?>
          <?php if (isset($note)) { ?>
            <?php echo render_show(__('Note'), render_value($note->getContent(['cultureFallback' => true])), ['isSubField' => true]); ?>
          <?php } ?>
        </div>
      <?php } ?>
    </div>
  </div>

</section> <!-- /section#accessPointsArea -->

<section id="controlArea" class="border-bottom">

  <?php echo render_b5_section_heading(
      __('Control area'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'control-collapse']
  ); ?>

  <?php echo render_show(__('Authority record identifier'), $resource->descriptionIdentifier); ?>

  <?php if (null !== $repository = $resource->getMaintainingRepository()) { ?>
    <?php echo render_show(__('Maintained by'), link_to(render_title($repository), [$repository, 'module' => 'repository'])); ?>
  <?php } ?>

  <?php echo render_show(__('Institution identifier'), render_value_inline($resource->getInstitutionResponsibleIdentifier(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Rules and/or conventions used'), render_value($resource->getRules(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Status'), render_value_inline($resource->descriptionStatus)); ?>

  <?php echo render_show(__('Level of detail'), render_value_inline($resource->descriptionDetail)); ?>

  <?php echo render_show(__('Dates of creation, revision and deletion'), render_value($resource->getRevisionHistory(['cultureFallback' => true]))); ?>

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

  <?php echo render_show(__('Sources'), render_value($resource->getSources(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Maintenance notes'), render_value($isaar->_maintenanceNote)); ?>

</section> <!-- /section#controlArea -->

<?php if (0 < count($resource->digitalObjectsRelatedByobjectId)) { ?>

  <div class="digitalObjectMetadata">
    <?php echo get_component('digitalobject', 'metadata', ['resource' => $resource->digitalObjectsRelatedByobjectId[0], 'object' => $resource]); ?>
  </div>

<?php } ?>

<?php slot('after-content'); ?>

  <?php if (QubitAcl::check($resource, ['update', 'translate', 'delete', 'create'])) { ?>

    <ul class="actions mb-3 nav gap-2">

      <?php if (QubitAcl::check($resource, 'update') || (QubitAcl::check($resource, 'translate'))) { ?>
        <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'actor', 'action' => 'edit'], ['class' => 'btn atom-btn-outline-light']); ?></li>
      <?php } ?>

      <?php if (QubitAcl::check($resource, 'delete')) { ?>
        <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'actor', 'action' => 'delete'], ['class' => 'btn atom-btn-outline-danger']); ?></li>
      <?php } ?>

      <?php if (QubitAcl::check($resource, 'create')) { ?>
        <li><?php echo link_to(__('Add new'), ['module' => 'actor', 'action' => 'add'], ['class' => 'btn atom-btn-outline-light']); ?></li>
      <?php } ?>

      <?php if (QubitAcl::check($resource, 'update') || sfContext::getInstance()->getUser()->hasGroup(QubitAclGroup::EDITOR_ID)) { ?>
        <li>
          <div class="dropup">
            <button type="button" class="btn atom-btn-outline-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <?php echo __('More'); ?>
            </button>

            <ul class="dropdown-menu mb-2">
              <?php if (0 < count($resource->digitalObjectsRelatedByobjectId) && QubitDigitalObject::isUploadAllowed()) { ?>
                <li><?php echo link_to(__('Edit %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]), [$resource->digitalObjectsRelatedByobjectId[0], 'module' => 'digitalobject', 'action' => 'edit'], ['class' => 'dropdown-item']); ?></li>
              <?php } elseif (QubitDigitalObject::isUploadAllowed()) { ?>
                <li><?php echo link_to(__('Link %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]), [$resource, 'module' => 'object', 'action' => 'addDigitalObject'], ['class' => 'dropdown-item']); ?></li>
              <?php } ?>
            </ul>
          </div>
        </li>
      <?php } ?>

    </ul>

  <?php } ?>

<?php end_slot(); ?>

<?php echo get_component('object', 'gaInstitutionsDimension', ['resource' => $resource]); ?>
