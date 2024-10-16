<?php decorate_with('layout_1col'); ?>

<?php slot('title'); ?>

  <h1 id="resource-name">
    <?php echo render_title($resource); ?>
  </h1>

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
      <li class="breadcrumb-item"><?php echo link_to(esc_specialchars(sfConfig::get('app_ui_label_function')), ['module' => 'function', 'action' => 'browse']); ?></li>
      <li class="breadcrumb-item active" aria-current="page"><?php echo render_title($resource); ?></li>
    </ol>
  </nav>

<?php end_slot(); ?>

<?php slot('before-content'); ?>

  <?php echo get_component('default', 'translationLinks', ['resource' => $resource]); ?>

<?php end_slot(); ?>

<?php
    // TODO: Move this to the controller when we only have B5 themes
    $headingsCondition = QubitAcl::check($resource, 'update');
    $headingsUrl = [$resource, 'module' => 'function', 'action' => 'edit'];
?>

<div class="section border-bottom" id="identityArea">

  <?php echo render_b5_section_heading(
      __('Identity area'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'identity-collapse', 'class' => 'rounded-top']
  ); ?>

  <?php echo render_show(__('Type'), render_value_inline($resource->type)); ?>

  <?php echo render_show(__('Authorized form of name'), render_value_inline($resource->getAuthorizedFormOfName(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Parallel form(s) of name'), $resource->getOtherNames(['typeId' => QubitTerm::PARALLEL_FORM_OF_NAME_ID])); ?>

  <?php echo render_show(__('Other form(s) of name'), $resource->getOtherNames(['typeId' => QubitTerm::OTHER_FORM_OF_NAME_ID])); ?>

  <?php echo render_show(__('Classification'), $resource->getClassification(['cultureFallback' => true])); ?>

</div> <!-- /.section#identityArea -->

<div class="section border-bottom" id="contextArea">

  <?php echo render_b5_section_heading(
      __('Context area'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'context-collapse']
  ); ?>

  <?php echo render_show(__('Dates'), render_value_inline($resource->getDates(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Description'), render_value($resource->getDescription(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('History'), render_value($resource->getHistory(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Legislation'), render_value($resource->getLegislation(['cultureFallback' => true]))); ?>

</div> <!-- /.section#contextArea -->

<div class="section border-bottom" id="relationshipsArea">

  <?php echo render_b5_section_heading(
      __('Relationships area'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'relationships-collapse']
  ); ?>

  <?php foreach ($isdf->relatedFunction as $item) { ?>
    <div class="field <?php echo render_b5_show_field_css_classes(); ?>">
      <?php echo render_b5_show_label(__('Related function')); ?>
      <div class="<?php echo render_b5_show_value_css_classes(); ?>">

        <?php echo render_show(__('Authorized form of name'), link_to(render_title($item->getOpposedObject($resource->id)), [$item->getOpposedObject($resource->id), 'module' => 'function']), ['isSubField' => true]); ?>

        <?php echo render_show(__('Identifier'), render_value_inline($item->getOpposedObject($resource->id)->getDescriptionIdentifier(['cultureFallback' => true])), ['isSubField' => true]); ?>

        <?php echo render_show(__('Type'), render_value_inline($item->getOpposedObject($resource->id)->type), ['isSubField' => true]); ?>

        <?php echo render_show(__('Category of relationship'), render_value_inline($item->type), ['isSubField' => true]); ?>

        <?php echo render_show(__('Description of relationship'), render_value($item->description), ['isSubField' => true]); ?>

        <?php echo render_show(__('Dates of relationship'), render_value_inline(Qubit::renderDateStartEnd($item->date, $item->startDate, $item->endDate)), ['isSubField' => true]); ?>

      </div>
    </div>
  <?php } ?>

  <?php foreach ($isdf->relatedAuthorityRecord as $item) { ?>
    <div class="field <?php echo render_b5_show_field_css_classes(); ?>">
      <?php echo render_b5_show_label(__('Related authority record')); ?>
      <div class="<?php echo render_b5_show_value_css_classes(); ?>">

        <?php echo render_show(__('Authorized form of name'), link_to(render_title($item->object->getAuthorizedFormOfName(['cultureFallback' => true])), [$item->object, 'module' => 'actor']), ['isSubField' => true]); ?>

        <?php echo render_show(__('Identifier'), render_value_inline($item->object->descriptionIdentifier), ['isSubField' => true]); ?>

        <?php if (null !== $item->description) { ?>
          <?php echo render_show(__('Nature of relationship'), render_value($item->description), ['isSubField' => true]); ?>
        <?php } ?>

        <?php echo render_show(__('Dates of the relationship'), render_value_inline(Qubit::renderDateStartEnd($item->date, $item->startDate, $item->endDate)), ['isSubField' => true]); ?>

      </div>
    </div>
  <?php } ?>

  <!-- Related archival material -->
  <?php foreach ($isdf->relatedResource as $item) { ?>
    <div class="field <?php echo render_b5_show_field_css_classes(); ?>">
      <?php echo render_b5_show_label(__('Related resource')); ?>
      <div class="<?php echo render_b5_show_value_css_classes(); ?>">

        <?php echo render_show(__('Title'), link_to(render_title($item->object->getTitle(['cultureFallback' => true])), [$item->object, 'module' => 'informationobject']), ['isSubField' => true]); ?>

        <?php $isad = new sfIsadPlugin($item->object);
        echo render_show(__('Identifier'), render_value_inline($isad->referenceCode), ['isSubField' => true]); ?>

        <?php if (null !== $item->description) { ?>
          <?php echo render_show(__('Nature of relationship'), render_value($item->description), ['isSubField' => true]); ?>
        <?php } ?>

        <?php echo render_show(__('Dates of the relationship'), render_value_inline(Qubit::renderDateStartEnd($item->date, $item->startDate, $item->endDate)), ['isSubField' => true]); ?>

      </div>
    </div>
  <?php } ?>

</div> <!-- /.section#relationshipsArea -->

<div class="section border-bottom" id="controlArea">

  <?php echo render_b5_section_heading(
      __('Control area'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'control-collapse']
  ); ?>

  <?php echo render_show(__('Description identifier'), render_value_inline($resource->descriptionIdentifier)); ?>

  <?php echo render_show(__('Institution identifier'), render_value_inline($resource->getInstitutionIdentifier(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Rules and/or conventions used'), render_value($resource->getRules(['cultureFallback' => true]))); ?>

  <?php echo render_show(__('Status'), render_value_inline($resource->descriptionStatus)); ?>

  <?php echo render_show(__('Level of detail'), render_value_inline($resource->descriptionDetail)); ?>

  <?php echo render_show(__('Dates of creation, revision or deletion'), render_value($resource->getRevisionHistory(['cultureFallback' => true]))); ?>

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

  <?php echo render_show(__('Maintenance notes'), render_value($isdf->_maintenanceNote)); ?>

</div> <!-- /.section#controlArea -->

<?php slot('after-content'); ?>
  <?php if (QubitAcl::check($resource, 'update') || QubitAcl::check($resource, 'delete') || QubitAcl::check($resource, 'create')) { ?>
    <ul class="actions mb-3 nav gap-2">
      <?php if (QubitAcl::check($resource, 'update')) { ?>
        <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'function', 'action' => 'edit'], ['class' => 'btn atom-btn-outline-light']); ?></li>
      <?php } ?>
      <?php if (QubitAcl::check($resource, 'delete')) { ?>
        <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'function', 'action' => 'delete'], ['class' => 'btn atom-btn-outline-danger']); ?></li>
      <?php } ?>
      <?php if (QubitAcl::check($resource, 'create')) { ?>
        <li><?php echo link_to(__('Add new'), ['module' => 'function', 'action' => 'add'], ['class' => 'btn atom-btn-outline-light']); ?></li>
      <?php } ?>
    </ul>
  <?php } ?>
<?php end_slot(); ?>
