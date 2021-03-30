<?php decorate_with('layout_3col'); ?>

<?php slot('sidebar'); ?>
  <?php include_component('actor', 'contextMenu', ['resource' => $resource]); ?>
<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo render_title($resource); ?></h1>

  <?php if (isset($errorSchema)) { ?>
    <div class="messages error">
      <ul>
        <?php foreach ($errorSchema as $error) { ?>
          <?php $error = sfOutputEscaper::unescape($error); ?>
          <li><?php echo $error->getMessage(); ?></li>
        <?php } ?>
      </ul>
    </div>
  <?php } ?>

  <section class="breadcrumb">
    <ul>
      <li><?php echo link_to(esc_specialchars(sfConfig::get('app_ui_label_actor')), ['module' => 'actor', 'action' => 'browse']); ?></li>
      <li><span><?php echo render_title($resource); ?></span></li>
    </ul>
  </section>

  <?php echo get_component('default', 'translationLinks', ['resource' => $resource]); ?>

<?php end_slot(); ?>

<?php slot('context-menu'); ?>

  <?php if ($sf_context->getConfiguration()->isPluginEnabled('sfEacPlugin')) { ?>

    <section id="action-icons">
      <ul>
        <li class="separator"><h4><?php echo __('Clipboard'); ?></h4></li>

        <li class="clipboard">
          <?php echo get_component('clipboard', 'button', ['slug' => $resource->slug, 'wide' => true, 'type' => 'actor']); ?>
        </li>

        <li class="separator"><h4><?php echo __('Export'); ?></h4></li>

        <li>
          <a href="<?php echo url_for([$resource, 'module' => 'sfEacPlugin', 'sf_format' => 'xml']); ?>">
            <i class="fa fa-upload"></i>
            <?php echo __('EAC'); ?>
          </a>
        </li>
      </ul>
    </section>

    <?php echo get_partial('object/subjectAccessPoints', ['resource' => $resource, 'sidebar' => true]); ?>
    <?php echo get_partial('object/placeAccessPoints', ['resource' => $resource, 'sidebar' => true]); ?>

  <?php } ?>

<?php end_slot(); ?>

<?php if (0 < count($resource->digitalObjectsRelatedByobjectId)) { ?>
  <?php echo get_component('digitalobject', 'show', ['link' => $digitalObjectLink, 'resource' => $resource->digitalObjectsRelatedByobjectId[0], 'usageType' => QubitTerm::REFERENCE_ID]); ?>
<?php } ?>

<section id="identityArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Identity area').'</h2>', [$resource, 'module' => 'actor', 'action' => 'edit'], ['anchor' => 'identityArea', 'title' => __('Edit identity area')]); ?>

  <?php echo render_show(__('Type of entity'), render_value($resource->entityType)); ?>

  <?php echo render_show(__('Authorized form of name'), render_value($resource->getAuthorizedFormOfName(['cultureFallback' => true]))); ?>

  <div class="field">
    <h3><?php echo __('Parallel form(s) of name'); ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->getOtherNames(['typeId' => QubitTerm::PARALLEL_FORM_OF_NAME_ID]) as $item) { ?>
          <li><?php echo render_value_inline($item->__toString()); ?></li>
        <?php } ?>
      </ul>
    </div>
  </div>

  <div class="field">
    <h3><?php echo __('Standardized form(s) of name according to other rules'); ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->getOtherNames(['typeId' => QubitTerm::STANDARDIZED_FORM_OF_NAME_ID]) as $item) { ?>
          <li><?php echo render_value_inline($item->__toString()); ?></li>
        <?php } ?>
      </ul>
    </div>
  </div>

  <div class="field">
    <h3><?php echo __('Other form(s) of name'); ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->getOtherNames(['typeId' => QubitTerm::OTHER_FORM_OF_NAME_ID]) as $item) { ?>
          <li><?php echo render_value_inline($item->__toString()); ?></li>
        <?php } ?>
      </ul>
    </div>
  </div>

  <?php echo render_show(__('Identifiers for corporate bodies'), $resource->corporateBodyIdentifiers); ?>

</section> <!-- /section#identityArea -->

<section id="descriptionArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Description area').'</h2>', [$resource, 'module' => 'actor', 'action' => 'edit'], ['anchor' => 'descriptionArea', 'title' => __('Edit description area')]); ?>

  <?php echo render_show(__('Dates of existence'), render_value($resource->getDatesOfExistence(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('History'), render_value($resource->getHistory(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Places'), render_value($resource->getPlaces(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Legal status'), render_value($resource->getLegalStatus(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Functions, occupations and activities'), render_value($resource->getFunctions(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Mandates/sources of authority'), render_value($resource->getMandates(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Internal structures/genealogy'), render_value($resource->getInternalStructures(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('General context'), render_value($resource->getGeneralContext(['cultureFallback' => true]))); ?>

</section> <!-- /section#descriptionArea -->

<section id="relationshipsArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Relationships area').'</h2>', [$resource, 'module' => 'actor', 'action' => 'edit'], ['anchor' => 'relationshipsArea', 'title' => __('Edit relationships area')]); ?>

  <?php foreach ($resource->getActorRelations() as $item) { ?>
    <?php $relatedEntity = $item->getOpposedObject($resource->id); ?>
    <div class="field">
      <h3><?php echo __('Related entity'); ?></h3>
      <div>

        <?php echo link_to(render_title($relatedEntity), [$relatedEntity, 'module' => ('QubitRepository' == $relatedEntity->className) ? 'repository' : 'actor']); ?><?php if (isset($relatedEntity->datesOfExistence)) { ?> <span class="note2">(<?php echo render_value_inline($relatedEntity->getDatesOfExistence(['cultureFallback' => true])); ?>)</span><?php } ?>

        <?php echo render_show(__('Identifier of related entity'), render_value_inline($relatedEntity->descriptionIdentifier)); ?>

        <?php if (QubitTerm::ROOT_ID == $item->type->parentId) { ?>
          <?php echo render_show(__('Category of relationship'), render_value_inline($item->type)); ?>
        <?php } else { ?>
          <?php echo render_show(__('Category of relationship'), render_value_inline($item->type->parent)); ?>

          <?php if ($resource->id != $item->objectId) { ?>
            <?php echo render_show(__('Type of relationship'), link_to(render_title($relatedEntity), [$relatedEntity, 'module' => ('QubitRepository' == $relatedEntity->className) ? 'repository' : 'actor']).' '.render_value($item->type).' '.render_value($resource->getAuthorizedFormOfName(['cultureFallback' => true]))); ?>
          <?php } elseif (0 < count($converseTerms = QubitRelation::getBySubjectOrObjectId($item->type->id, ['typeId' => QubitTerm::CONVERSE_TERM_ID]))) { ?>
            <?php echo render_show(__('Type of relationship'), link_to(render_title($relatedEntity), [$relatedEntity, 'module' => ('QubitRepository' == $relatedEntity->className) ? 'repository' : 'actor']).' '.render_value($converseTerms[0]->getOpposedObject($item->type)).' '.render_value($resource->getAuthorizedFormOfName(['cultureFallback' => true]))); ?>
          <?php } ?>
        <?php } ?>

        <?php echo render_show(__('Dates of relationship'), render_value_inline(Qubit::renderDateStartEnd($item->date, $item->startDate, $item->endDate))); ?>

        <?php echo render_show(__('Description of relationship'), render_value_inline($item->description)); ?>

      </div>
    </div>
  <?php } ?>

  <?php foreach ($functions as $item) { ?>
    <?php echo render_show(__('Related function'), link_to(render_title($item), [$item, 'module' => 'function'])); ?>
  <?php } ?>

</section> <!-- /section#relationshipsArea -->

<section id="accessPointsArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Access points area').'</h2>', [$resource, 'module' => 'actor', 'action' => 'edit'], ['anchor' => 'accessPointsArea', 'title' => __('Edit access points area')]); ?>

  <div class="subjectAccessPoints">
    <?php echo get_partial('object/subjectAccessPoints', ['resource' => $resource]); ?>
  </div>

  <div class="placeAccessPoints">
    <?php echo get_partial('object/placeAccessPoints', ['resource' => $resource]); ?>
  </div>

  <div class="field">
    <h3><?php echo __('Occupations'); ?></h3>
    <div>
      <?php foreach ($resource->getOccupations() as $item) { ?>
        <div>
          <?php echo link_to(render_title($item->term), [$item->term, 'module' => 'term']); ?>
          <?php $note = $item->getNotesByType(['noteTypeId' => QubitTerm::ACTOR_OCCUPATION_NOTE_ID])->offsetGet(0); ?>
          <?php if (isset($note)) { ?>
            <?php echo render_show(__('Note'), render_value($note->getContent(['cultureFallback' => true]))); ?>
          <?php } ?>
        </div>
      <?php } ?>
    </div>
  </div>

</section> <!-- /section#accessPointsArea -->

<section id="controlArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Control area').'</h2>', [$resource, 'module' => 'actor', 'action' => 'edit'], ['anchor' => 'controlArea', 'title' => __('Edit control area')]); ?>

  <?php echo render_show(__('Authority record identifier'), $resource->descriptionIdentifier); ?>

  <?php if (null !== $repository = $resource->getMaintainingRepository()) { ?>
    <?php echo render_show(__('Maintained by'), link_to(render_title($repository), [$repository, 'module' => 'repository'])); ?>
  <?php } ?>

  <?php echo render_show(__('Institution identifier'), render_value($resource->getInstitutionResponsibleIdentifier(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Rules and/or conventions used'), render_value($resource->getRules(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Status'), render_value($resource->descriptionStatus)); ?>

  <?php echo render_show(__('Level of detail'), render_value($resource->descriptionDetail)); ?>

  <?php echo render_show(__('Dates of creation, revision and deletion'), render_value($resource->getRevisionHistory(['cultureFallback' => true]))); ?>

  <div class="field">
    <h3><?php echo __('Language(s)'); ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->language as $code) { ?>
          <li><?php echo format_language($code); ?></li>
        <?php } ?>
      </ul>
    </div>
  </div>

  <div class="field">
    <h3><?php echo __('Script(s)'); ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->script as $code) { ?>
          <li><?php echo format_script($code); ?></li>
        <?php } ?>
      </ul>
    </div>
  </div>

  <?php echo render_show(__('Sources'), render_value($resource->getSources(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Maintenance notes'), render_value($isaar->_maintenanceNote)); ?>

</section> <!-- /section#controlArea -->

<?php if (0 < count($resource->digitalObjectsRelatedByobjectId)) { ?>

  <div class="digitalObjectMetadata">
    <?php echo get_component('digitalobject', 'metadata', ['resource' => $resource->digitalObjectsRelatedByobjectId[0], 'object' => $resource]); ?>
  </div>

<?php } ?>

<?php slot('after-content'); ?>

  <section class="actions">

    <ul>

        <?php if (QubitAcl::check($resource, 'update') || (QubitAcl::check($resource, 'translate'))) { ?>
          <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'actor', 'action' => 'edit'], ['class' => 'c-btn c-btn-submit', 'title' => __('Edit')]); ?></li>
        <?php } ?>

        <?php if (QubitAcl::check($resource, 'delete')) { ?>
          <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'actor', 'action' => 'delete'], ['class' => 'c-btn c-btn-delete', 'title' => __('Delete')]); ?></li>
        <?php } ?>

        <?php if (QubitAcl::check($resource, 'create')) { ?>
          <li><?php echo link_to(__('Add new'), ['module' => 'actor', 'action' => 'add'], ['class' => 'c-btn', 'title' => __('Add new')]); ?></li>
        <?php } ?>

        <?php if (QubitAcl::check($resource, 'update') || sfContext::getInstance()->getUser()->hasGroup(QubitAclGroup::EDITOR_ID)) { ?>
        <li class="divider"></li>

        <li>
          <div class="btn-group dropup">
            <a class="c-btn dropdown-toggle" data-toggle="dropdown" href="#">
              <?php echo __('More'); ?>
              <span class="caret"></span>
            </a>

            <ul class="dropdown-menu">
              <?php if (0 < count($resource->digitalObjectsRelatedByobjectId) && QubitDigitalObject::isUploadAllowed()) { ?>
                <li><?php echo link_to(__('Edit %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]), [$resource->digitalObjectsRelatedByobjectId[0], 'module' => 'digitalobject', 'action' => 'edit']); ?></li>
              <?php } elseif (QubitDigitalObject::isUploadAllowed()) { ?>
                <li><?php echo link_to(__('Link %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]), [$resource, 'module' => 'object', 'action' => 'addDigitalObject']); ?></li>
              <?php } ?>
            </ul>
          </div>
        </li>
        <?php } ?>
    </ul>

  </section>

<?php end_slot(); ?>

<?php echo get_component('object', 'gaInstitutionsDimension', ['resource' => $resource]); ?>
