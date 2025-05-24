<?php decorate_with('layout_1col'); ?>

<?php slot('title'); ?>

  <h1 id="resource-name">
    <?php echo render_title($resource); ?>
  </h1>

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
      <li><?php echo link_to(esc_specialchars(sfConfig::get('app_ui_label_function')), ['module' => 'function', 'action' => 'browse']); ?></li>
      <li><span><?php echo render_title($resource); ?></span></li>
    </ul>
  </section>

<?php end_slot(); ?>

<?php slot('before-content'); ?>

  <?php echo get_component('default', 'translationLinks', ['resource' => $resource]); ?>

<?php end_slot(); ?>

<div class="section" id="identityArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Identity area').'</h2>', [$resource, 'module' => 'function', 'action' => 'edit'], ['anchor' => 'identityArea', 'title' => __('Edit identity area')]); ?>

  <?php echo render_show(__('Type'), render_value($resource->type)); ?>

  <?php echo render_show(__('Authorized form of name'), render_value($resource->getAuthorizedFormOfName(['cultureFallback' => true]))); ?>

  <div class="field">
    <h3><?php echo __('Parallel form(s) of name'); ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->getOtherNames(['typeId' => QubitTerm::PARALLEL_FORM_OF_NAME_ID]) as $item) { ?>
          <li><?php echo render_value_inline($item); ?></li>
        <?php } ?>
      </ul>
    </div>
  </div>

  <div class="field">
    <h3><?php echo __('Other form(s) of name'); ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->getOtherNames(['typeId' => QubitTerm::OTHER_FORM_OF_NAME_ID]) as $item) { ?>
          <li><?php echo render_value_inline($item); ?></li>
        <?php } ?>
      </ul>
    </div>
  </div>

  <?php echo render_show(__('Classification'), $resource->getClassification(['cultureFallback' => true])); ?>

</div> <!-- /.section#identityArea -->

<div class="section" id="contextArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Context area').'</h2>', [$resource, 'module' => 'function', 'action' => 'edit'], ['anchor' => 'contextArea', 'title' => __('Edit context area')]); ?>

  <?php echo render_show(__('Dates'), render_value($resource->getDates(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Description'), render_value($resource->getDescription(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('History'), render_value($resource->getHistory(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Legislation'), render_value($resource->getLegislation(['cultureFallback' => true]))); ?>

</div> <!-- /.section#contextArea -->

<div class="section" id="relationshipsArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Relationships area').'</h2>', [$resource, 'module' => 'function', 'action' => 'edit'], ['anchor' => 'relationshipsArea', 'title' => __('Edit relationships area')]); ?>

  <?php foreach ($isdf->relatedFunction as $item) { ?>
    <div class="field">
      <h3><?php echo __('Related function'); ?></h3>
      <div>

        <?php echo render_show(__('Authorized form of name'), link_to(render_title($item->getOpposedObject($resource->id)), [$item->getOpposedObject($resource->id), 'module' => 'function'])); ?>

        <?php echo render_show(__('Identifier'), render_value($item->getOpposedObject($resource->id)->getDescriptionIdentifier(['cultureFallback' => true]))); ?>

        <?php echo render_show(__('Type'), render_value($item->getOpposedObject($resource->id)->type)); ?>

        <?php echo render_show(__('Category of relationship'), render_value($item->type)); ?>

        <?php echo render_show(__('Description of relationship'), render_value($item->description)); ?>

        <?php echo render_show(__('Dates of relationship'), render_value_inline(Qubit::renderDateStartEnd($item->date, $item->startDate, $item->endDate))); ?>

      </div>
    </div>
  <?php } ?>

  <?php foreach ($isdf->relatedAuthorityRecord as $item) { ?>
    <div class="field">
      <h3><?php echo __('Related authority record'); ?></h3>
      <div>

        <?php echo render_show(__('Authorized form of name'), link_to(render_title($item->object->getAuthorizedFormOfName(['cultureFallback' => true])), [$item->object, 'module' => 'actor'])); ?>

        <?php echo render_show(__('Identifier'), render_value($item->object->descriptionIdentifier)); ?>

        <?php if (null !== $item->description) { ?>
          <?php echo render_show(__('Nature of relationship'), render_value($item->description)); ?>
        <?php } ?>

        <?php echo render_show(__('Dates of the relationship'), render_value_inline(Qubit::renderDateStartEnd($item->date, $item->startDate, $item->endDate))); ?>

      </div>
    </div>
  <?php } ?>

  <!-- Related archival material -->
  <?php foreach ($isdf->relatedResource as $item) { ?>
    <div class="field">
      <h3><?php echo __('Related resource'); ?></h3>
      <div>

        <?php echo render_show(__('Title'), link_to(render_title($item->object->getTitle(['cultureFallback' => true])), [$item->object, 'module' => 'informationobject'])); ?>

        <?php $isad = new sfIsadPlugin($item->object);
        echo render_show(__('Identifier'), render_value($isad->referenceCode)); ?>

        <?php if (null !== $item->description) { ?>
          <?php echo render_show(__('Nature of relationship'), render_value($item->description)); ?>
        <?php } ?>

        <?php echo render_show(__('Dates of the relationship'), render_value_inline(Qubit::renderDateStartEnd($item->date, $item->startDate, $item->endDate))); ?>

      </div>
    </div>
  <?php } ?>

</div> <!-- /.section#relationshipsArea -->

<div class="section" id="controlArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Control area').'</h2>', [$resource, 'module' => 'function', 'action' => 'edit'], ['anchor' => 'controlArea', 'title' => __('Edit control area')]); ?>

  <?php echo render_show(__('Description identifier'), render_value($resource->descriptionIdentifier)); ?>

  <?php echo render_show(__('Institution identifier'), render_value($resource->getInstitutionIdentifier(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Rules and/or conventions used'), render_value($resource->getRules(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Status'), render_value($resource->descriptionStatus)); ?>

  <?php echo render_show(__('Level of detail'), render_value($resource->descriptionDetail)); ?>

  <?php echo render_show(__('Dates of creation, revision or deletion'), render_value($resource->getRevisionHistory(['cultureFallback' => true]))); ?>

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

  <?php echo render_show(__('Maintenance notes'), render_value($isdf->_maintenanceNote)); ?>

</div> <!-- /.section#controlArea -->

<?php slot('after-content'); ?>
  <?php if (QubitAcl::check($resource, 'update') || QubitAcl::check($resource, 'delete') || QubitAcl::check($resource, 'create')) { ?>
    <section class="actions">
      <ul>
        <?php if (QubitAcl::check($resource, 'update')) { ?>
          <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'function', 'action' => 'edit'], ['title' => __('Edit'), 'class' => 'c-btn']); ?></li>
        <?php } ?>
        <?php if (QubitAcl::check($resource, 'delete')) { ?>
          <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'function', 'action' => 'delete'], ['class' => 'c-btn c-btn-delete', 'title' => __('Delete')]); ?></li>
        <?php } ?>
        <?php if (QubitAcl::check($resource, 'create')) { ?>
          <li><?php echo link_to(__('Add new'), ['module' => 'function', 'action' => 'add'], ['title' => __('Add new'), 'class' => 'c-btn']); ?></li>
        <?php } ?>
      </ul>
    </section>
  <?php } ?>
<?php end_slot(); ?>
