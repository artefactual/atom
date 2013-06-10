<?php decorate_with('layout_3col') ?>

<?php slot('sidebar') ?>
  <?php include_component('actor', 'contextMenu') ?>
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
      <li><?php echo link_to(sfConfig::get('app_ui_label_actor'), array('module' => 'actor', 'action' => 'browse')) ?></li>
      <li><span><?php echo render_title($resource) ?></span></li>
    </ul>
  </section>

<?php end_slot() ?>

<?php slot('context-menu') ?>

  <section id="action-icons">
    <ul>

      <li class="separator"><h4><?php echo __('Export') ?></h4></li>

      <li>
        <a href="<?php echo url_for(array($resource, 'module' => 'sfEacPlugin', 'sf_format' => 'xml')) ?>">
          <i class="icon-upload-alt"></i>
          <?php echo __('EAC') ?>
        </a>
      </li>

    </ul>
  </section>

<?php end_slot() ?>

<section id="identityArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Identity area').'</h2>', array($resource, 'module' => 'actor', 'action' => 'edit'), array('anchor' => 'identityArea', 'title' => __('Edit identity area'))) ?>

  <?php echo render_show(__('Type of entity'), render_value($resource->entityType)) ?>

  <?php echo render_show(__('Authorized form of name'), render_value($resource->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?>

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
    <h3><?php echo __('Standardized form(s) of name according to other rules') ?></h3>
    <div>
      <ul>
        <?php foreach ($resource->getOtherNames(array('typeId' => QubitTerm::STANDARDIZED_FORM_OF_NAME_ID)) as $item): ?>
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

  <?php echo render_show(__('Identifiers for corporate bodies'), render_value($resource->corporateBodyIdentifiers)) ?>

</section> <!-- /section#identityArea -->

<section id="descriptionArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Description area').'</h2>', array($resource, 'module' => 'actor', 'action' => 'edit'), array('anchor' => 'descriptionArea', 'title' => __('Edit description area'))) ?>

  <?php echo render_show(__('Dates of existence'), render_value($resource->getDatesOfExistence(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('History'), render_value($resource->getHistory(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Places'), render_value($resource->getPlaces(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Legal status'), render_value($resource->getLegalStatus(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Functions, occupations and activities'), render_value($resource->getFunctions(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Mandates/sources of authority'), render_value($resource->getMandates(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Internal structures/genealogy'), render_value($resource->getInternalStructures(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('General context'), render_value($resource->getGeneralContext(array('cultureFallback' => true)))) ?>

</section> <!-- /section#descriptionArea -->

<section id="relationshipsArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Relationships area').'</h2>', array($resource, 'module' => 'actor', 'action' => 'edit'), array('anchor' => 'relationshipsArea', 'title' => __('Edit relationships area'))) ?>

  <?php foreach ($resource->getActorRelations() as $item): ?>
    <?php $relatedEntity = $item->getOpposedObject($resource->id) ?>
    <div class="field">
      <h3><?php echo __('Related entity') ?></h3>
      <div>

        <?php echo link_to(render_title($relatedEntity), array($relatedEntity, 'module' => ('QubitRepository' == $relatedEntity->className) ? 'repository' : 'actor')) ?><?php if (isset($relatedEntity->datesOfExistence)): ?> <span class="note2">(<?php echo render_value($relatedEntity->getDatesOfExistence(array('cultureFallback' => true))) ?>)</span><?php endif; ?>

        <?php echo render_show(__('Identifier of the related entity'), render_value($relatedEntity->descriptionIdentifier)) ?>

        <?php echo render_show(__('Category of the relationship'), render_value($item->type)) ?>

        <?php echo render_show(__('Dates of the relationship'), Qubit::renderDateStartEnd($item->date, $item->startDate, $item->endDate)) ?>

        <?php echo render_show(__('Description of relationship'), render_value($item->description)) ?>

      </div>
    </div>
  <?php endforeach; ?>

  <?php foreach ($functions as $item): ?>
    <?php echo render_show(__('Related function'), link_to(render_title($item), array($item, 'module' => 'function'))) ?>
  <?php endforeach; ?>

</section> <!-- /section#relationshipsArea -->

<section id="controlArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Control area').'</h2>', array($resource, 'module' => 'actor', 'action' => 'edit'), array('anchor' => 'controlArea', 'title' => __('Edit control area'))) ?>

  <?php echo render_show(__('Description identifier'), render_value($resource->descriptionIdentifier)) ?>

  <?php echo render_show(__('Institution identifier'), render_value($resource->getInstitutionResponsibleIdentifier(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Rules and/or conventions used'), render_value($resource->getRules(array('cultureFallback' => true)))) ?>

  <?php echo render_show(__('Status'), render_value($resource->descriptionStatus)) ?>

  <?php echo render_show(__('Level of detail'), render_value($resource->descriptionDetail)) ?>

  <?php echo render_show(__('Dates of creation, revision and deletion'), render_value($resource->getRevisionHistory(array('cultureFallback' => true)))) ?>

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

  <?php echo render_show(__('Maintenance notes'), render_value($isaar->maintenanceNotes)) ?>

</section> <!-- /section#controlArea -->

<?php slot('after-content') ?>

  <section class="actions">

    <ul>

        <?php if (QubitAcl::check($resource, 'update') || (QubitAcl::check($resource, 'translate'))): ?>
          <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'actor', 'action' => 'edit'), array('class' => 'c-btn c-btn-submit', 'title' => __('Edit'))) ?></li>
        <?php endif; ?>

        <?php if (QubitAcl::check($resource, 'delete')): ?>
          <li><?php echo link_to(__('Delete'), array($resource, 'module' => 'actor', 'action' => 'delete'), array('class' => 'c-btn c-btn-delete', 'title' => __('Delete'))) ?></li>
        <?php endif; ?>

        <?php if (QubitAcl::check($resource, 'create')): ?>
          <li><?php echo link_to(__('Add new'), array('module' => 'actor', 'action' => 'add'), array('class' => 'c-btn', 'title' => __('Add new'))) ?></li>
        <?php endif; ?>

    </ul>

  </section>

<?php end_slot() ?>
