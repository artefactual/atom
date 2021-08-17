<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1 class="multiline">
    <?php echo __('View rights holder'); ?>
    <span class="sub"><?php echo render_title($resource); ?></span>
  </h1>
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

<div class="section border-bottom" id="identityArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), render_b5_section_label(__('Identity area')), [$resource, 'module' => 'rightsholder', 'action' => 'edit'], ['anchor' => 'identity-collapse', 'title' => __('Edit identity area'), 'class' => 'text-primary']); ?>

  <?php echo render_show(__('Authorized form of name'), render_value_inline($resource->getAuthorizedFormOfName(['cultureFallback' => true]))); ?>

</div> <!-- /.section#identityArea -->

<div class="section border-bottom" id="contactArea">

  <?php echo link_to_if(QubitAcl::check($resource, 'update'), render_b5_section_label(__('Contact area')), [$resource, 'module' => 'rightsholder', 'action' => 'edit'], ['anchor' => 'contact-collapse', 'title' => __('Edit contact area'), 'class' => 'text-primary']); ?>

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
