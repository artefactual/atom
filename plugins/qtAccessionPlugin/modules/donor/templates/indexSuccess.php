<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo __('View donor') ?>
    <span class="sub"><?php echo render_title($resource) ?></span>
  </h1>
<?php end_slot() ?>

<?php slot('before-content') ?>

  <?php if (isset($errorSchema)): ?>
    <div class="messages error">
      <ul>
        <?php foreach ($errorSchema as $error): ?>
          <li><?php echo $error ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php echo get_component('default', 'translationLinks', array('resource' => $resource)) ?>

<?php end_slot() ?>

<?php echo render_show(__('Authorized form of name'), render_value($resource->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?>

<div class="section" id="contactArea">

  <h2><?php echo __('Contact area') ?></h2>

  <?php foreach ($resource->contactInformations as $contactItem): ?>
    <?php echo get_partial('contactinformation/contactInformation', array('contactInformation' => $contactItem)) ?>
  <?php endforeach; ?>

</div> <!-- /.section#contactArea -->

<div class="section" id="accessionArea">

  <h2><?php echo __('Accession area') ?></h2>

  <div class="field">

    <h3><?php echo __('Related accession(s)') ?></h3>

    <div>
      <ul>
        <?php foreach (QubitRelation::getRelationsByObjectId($resource->id, array('typeId' => QubitTerm::DONOR_ID)) as $item): ?>
          <li><?php echo link_to(render_title($item->subject), array($item->subject, 'module' => 'accession')) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

  </div>

</div> <!-- /.section#accessionArea -->

<?php slot('after-content') ?>
  <section class="actions">
    <ul>
      <?php if (QubitAcl::check($resource, 'update')): ?>
        <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'donor', 'action' => 'edit'), array('title' => __('Edit'), 'class' => 'c-btn')) ?></li>
      <?php endif; ?>
      <?php if (QubitAcl::check($resource, 'delete')): ?>
        <li><?php echo link_to(__('Delete'), array($resource, 'module' => 'donor', 'action' => 'delete'), array('title' => __('Delete'), 'class' => 'c-btn c-btn-delete')) ?></li>
      <?php endif; ?>
      <?php if (QubitAcl::check($resource, 'create')): ?>
        <li><?php echo link_to(__('Add new'), array('module' => 'donor', 'action' => 'add'), array('title' => __('Add new'), 'class' => 'c-btn')) ?></li>
      <?php endif; ?>
    </ul>
  </section>
<?php end_slot() ?>
