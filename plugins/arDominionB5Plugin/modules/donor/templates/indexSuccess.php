<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo __('View donor'); ?>
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
    $headingsUrl = [$resource, 'module' => 'donor', 'action' => 'edit'];
?>

<div class="section border-bottom" id="basicInfo">

  <?php echo render_b5_section_heading(
      __('Basic info'),
      $headingsCondition,
      $headingsUrl,
      ['anchor' => 'identity-collapse', 'class' => 'rounded-top']
  ); ?>

  <?php echo render_show(__('Authorized form of name'), render_value_inline($resource->getAuthorizedFormOfName(['cultureFallback' => true]))); ?>

</div>

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

<div class="section" id="accessionArea">

  <?php echo render_b5_section_heading(__('Accession area')); ?>

  <?php
      $relatedAccessions = [];
      foreach (QubitRelation::getRelationsByObjectId($resource->id, ['typeId' => QubitTerm::DONOR_ID]) as $item) {
          $relatedAccessions[] = link_to(render_title($item->subject), [$item->subject, 'module' => 'accession']);
      }
      echo render_show(__('Related accession(s)'), $relatedAccessions);
  ?>

</div> <!-- /.section#accessionArea -->

<?php slot('after-content'); ?>
  <?php if (QubitAcl::check($resource, ['update', 'delete', 'create'])) { ?>
    <ul class="actions mb-3 nav gap-2">
      <?php if (QubitAcl::check($resource, 'update')) { ?>
        <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'donor', 'action' => 'edit'], ['class' => 'btn atom-btn-outline-light']); ?></li>
      <?php } ?>
      <?php if (QubitAcl::check($resource, 'delete')) { ?>
        <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'donor', 'action' => 'delete'], ['class' => 'btn atom-btn-outline-danger']); ?></li>
      <?php } ?>
      <?php if (QubitAcl::check($resource, 'create')) { ?>
        <li><?php echo link_to(__('Add new'), ['module' => 'donor', 'action' => 'add'], ['class' => 'btn atom-btn-outline-light']); ?></li>
      <?php } ?>
    </ul>
  <?php } ?>
<?php end_slot(); ?>
