<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1 class="multiline">
    <?php echo __('View rights holder'); ?>
    <span class="sub"><?php echo render_title($resource); ?></span>
  </h1>
<?php end_slot(); ?>

<?php slot('before-content'); ?>

  <?php if (isset($errorSchema)) { ?>
    <div class="messages error">
      <ul>
        <?php foreach ($errorSchema as $error) { ?>
          <li><?php echo $error; ?></li>
        <?php } ?>
      </ul>
    </div>
  <?php } ?>

  <?php echo get_component('default', 'translationLinks', ['resource' => $resource]); ?>

<?php end_slot(); ?>

<?php echo render_show(__('Authorized form of name'), render_value($resource->getAuthorizedFormOfName(['cultureFallback' => true]))); ?>

<div class="section" id="contactArea">

  <h2><?php echo __('Contact area'); ?></h2>

  <?php foreach ($resource->contactInformations as $contactItem) { ?>
    <?php echo get_partial('contactinformation/contactInformation', ['contactInformation' => $contactItem]); ?>
  <?php } ?>

</div> <!-- /.section#contactArea -->

<?php slot('after-content'); ?>
  <?php if (QubitAcl::check($resource, ['update', 'delete', 'create'])) { ?>
    <ul class="actions nav gap-2">
      <?php if (QubitAcl::check($resource, 'update')) { ?>
        <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'rightsholder', 'action' => 'edit'], ['class' => 'btn atom-btn-outline-light']); ?></li>
      <?php } ?>
      <?php if (QubitAcl::check($resource, 'delete')) { ?>
        <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'rightsholder', 'action' => 'delete'], ['class' => 'btn atom-btn-outline-danger']); ?></li>
      <?php } ?>
      <?php if (QubitAcl::check($resource, 'create')) { ?>
        <li><?php echo link_to(__('Add new'), ['module' => 'rightsholder', 'action' => 'add'], ['class' => 'btn atom-btn-outline-light']); ?></li>
      <?php } ?>
    </ul>
  <?php } ?>
<?php end_slot(); ?>
