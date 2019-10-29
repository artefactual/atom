<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo __('View ISDF function') ?>
    <span class="sub"><?php echo render_title($resource) ?></span>
  </h1>
<?php end_slot() ?>

<?php slot("before-content") ?>

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

  <?php echo get_component('default', 'translationLinks', array('resource' => $resource)) ?>

<?php end_slot() ?>

<div class="section" id="identityArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Identity area').'</h2>', array($resource, 'module' => 'function', 'action' => 'edit'), array('anchor' => 'identityArea', 'title' => __('Edit identity area'))) ?>

  <?php echo render_show(__('Type'), render_value($resource->type)) ?>

  <?php echo render_show(__('Authorized form of name'), render_value($resource->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?>

  <div class="field">
    <h3><?php echo __('Parallel form(s) of name') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->getOtherNames(array('typeId' => QubitTerm::PARALLEL_FORM_OF_NAME_ID)) as $item): ?>
          <li><?php echo render_value_inline($item) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="field">
    <h3><?php echo __('Other form(s) of name') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->getOtherNames(array('typeId' => QubitTerm::OTHER_FORM_OF_NAME_ID)) as $item): ?>
          <li><?php echo render_value_inline($item) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <?php echo render_show(__('Classification'), $resource->getClassification(array('cultureFallback' => true))) ?>

</div> <!-- /.section#identityArea -->

<div class="section" id="contextArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Context area').'</h2>', array($resource, 'module' => 'function', 'action' => 'edit'), array('anchor' => 'contextArea', 'title' => __('Edit context area'))) ?>

  <?php echo render_show(__('Dates'), render_value($resource->getDates(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Description'), render_value($resource->getDescription(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('History'), render_value($resource->getHistory(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Legislation'), render_value($resource->getLegislation(array('cultureFallback' => true)))) ?>

</div> <!-- /.section#contextArea -->

<div class="section" id="relationshipsArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Relationships area').'</h2>', array($resource, 'module' => 'function', 'action' => 'edit'), array('anchor' => 'relationshipsArea', 'title' => __('Edit relationships area'))) ?>

  <?php foreach ($isdf->relatedFunction as $item): ?>
    <div class="field">
      <h3><?php echo __('Related function') ?></h3>
      <div>

        <?php echo render_show(__('Authorized form of name'), link_to(render_title($item->getOpposedObject($resource->id)), array($item->getOpposedObject($resource->id), 'module' => 'function'))) ?>

        <?php echo render_show(__('Identifier'), render_value($item->getOpposedObject($resource->id)->getDescriptionIdentifier(array('cultureFallback' => true)))) ?>

        <?php echo render_show(__('Type'), render_value($item->getOpposedObject($resource->id)->type)) ?>

        <?php echo render_show(__('Category of relationship'), render_value($item->type)) ?>

        <?php echo render_show(__('Description of relationship'), render_value($item->description)) ?>

        <?php echo render_show(__('Dates of relationship'), render_value_inline(Qubit::renderDateStartEnd($item->date, $item->startDate, $item->endDate))) ?>

      </div>
    </div>
  <?php endforeach; ?>

  <?php foreach ($isdf->relatedAuthorityRecord as $item): ?>
    <div class="field">
      <h3><?php echo __('Related authority record') ?></h3>
      <div>

        <?php echo render_show(__('Authorized form of name'), link_to(render_title($item->object->getAuthorizedFormOfName(array('cultureFallback' => true))), array($item->object, 'module' => 'actor'))) ?>

        <?php echo render_show(__('Identifier'), render_value($item->object->descriptionIdentifier)) ?>

        <?php if (null !== $item->description): ?>
          <?php echo render_show(__('Nature of relationship'), render_value($item->description)) ?>
        <?php endif; ?>

        <?php echo render_show(__('Dates of the relationship'), render_value_inline(Qubit::renderDateStartEnd($item->date, $item->startDate, $item->endDate))) ?>

      </div>
    </div>
  <?php endforeach; ?>

  <!-- Related archival material -->
  <?php foreach ($isdf->relatedResource as $item): ?>
    <div class="field">
      <h3><?php echo __('Related resource') ?></h3>
      <div>

        <?php echo render_show(__('Title'), link_to(render_title($item->object->getTitle(array('cultureFallback' => true))), array($item->object, 'module' => 'informationobject'))) ?>

        <?php $isad = new sfIsadPlugin($item->object); echo render_show(__('Identifier'), render_value($isad->referenceCode)) ?>

        <?php if (null !== $item->description): ?>
          <?php echo render_show(__('Nature of relationship'), render_value($item->description)) ?>
        <?php endif; ?>

        <?php echo render_show(__('Dates of the relationship'), render_value_inline(Qubit::renderDateStartEnd($item->date, $item->startDate, $item->endDate))) ?>

      </div>
    </div>
  <?php endforeach; ?>

</div> <!-- /.section#relationshipsArea -->

<div class="section" id="controlArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Control area').'</h2>', array($resource, 'module' => 'function', 'action' => 'edit'), array('anchor' => 'controlArea', 'title' => __('Edit control area'))) ?>

  <?php echo render_show(__('Description identifier'), render_value($resource->descriptionIdentifier)) ?>

  <?php echo render_show(__('Institution identifier'), render_value($resource->getInstitutionIdentifier(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Rules and/or conventions used'), render_value($resource->getRules(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Status'), render_value($resource->descriptionStatus)) ?>

  <?php echo render_show(__('Level of detail'), render_value($resource->descriptionDetail)) ?>

  <?php echo render_show(__('Dates of creation, revision or deletion'), render_value($resource->getRevisionHistory(array('cultureFallback' => true)))) ?>

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

  <?php echo render_show(__('Sources'), render_value($resource->getSources(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Maintenance notes'), render_value($isdf->_maintenanceNote)) ?>

</div> <!-- /.section#controlArea -->

<?php slot("after-content") ?>
  <?php if (QubitAcl::check($resource, 'update') || QubitAcl::check($resource, 'delete') || QubitAcl::check($resource, 'create')): ?>
    <section class="actions">
      <ul>
        <?php if (QubitAcl::check($resource, 'update')): ?>
          <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'function', 'action' => 'edit'), array('title' => __('Edit'), 'class' => 'c-btn')) ?></li>
        <?php endif; ?>
        <?php if (QubitAcl::check($resource, 'delete')): ?>
          <li><?php echo link_to(__('Delete'), array($resource, 'module' => 'function', 'action' => 'delete'), array('class' => 'c-btn c-btn-delete', 'title' => __('Delete'))) ?></li>
        <?php endif; ?>
        <?php if (QubitAcl::check($resource, 'create')): ?>
          <li><?php echo link_to(__('Add new'), array('module' => 'function', 'action' => 'add'), array('title' => __('Add new'), 'class' => 'c-btn')) ?></li>
        <?php endif; ?>
      </ul>
    </section>
  <?php endif; ?>
<?php end_slot() ?>
