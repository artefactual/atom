<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo __('View rights holder'); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php echo render_title($resource); ?>
    </span>
  </div>
<?php end_slot(); ?>

<?php slot('before-content'); ?>

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

  <?php echo get_component('default', 'translationLinks', ['resource' => $resource]); ?>

<?php end_slot(); ?>

<?php
    // TODO: Move this to the controller when we only have B5 themes
    $headingsCondition = QubitAcl::check($resource, 'update');
    $headingsUrl = [$resource, 'module' => 'rightsholder', 'action' => 'edit'];
?>

<div class="section border-bottom" id="identityArea">

  <?php echo render_b5_section_heading(
      __('Identity area'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'identity-collapse', 'class' => 'rounded-top']
  ); ?>

  <?php echo render_show(__('Authorized form of name'), render_value_inline($resource->getAuthorizedFormOfName(['cultureFallback' => true]))); ?>

</div> <!-- /.section#identityArea -->

<div class="section border-bottom" id="contactArea">

  <?php echo render_b5_section_heading(
      __('Contact area'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'contact-collapse']
  ); ?>

  <?php foreach ($resource->contactInformations as $contactItem) { ?>
    <?php echo get_partial('contactinformation/contactInformation', ['contactInformation' => $contactItem]); ?>
  <?php } ?>

</div> <!-- /.section#contactArea -->

<?php slot('after-content'); ?>
  <?php if (QubitAcl::check($resource, ['update', 'delete', 'create'])) { ?>
    <ul class="actions mb-3 nav gap-2">
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
