<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo __('View deaccession record'); ?>
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

<?php echo render_b5_section_heading(
    __('Deaccession area'),
    QubitAcl::check($resource, 'update'),
    [$resource, 'module' => 'deaccession', 'action' => 'edit'],
    ['anchor' => 'deaccession-collapse', 'class' => 'rounded-top']
); ?>

<?php echo render_show(__('Accession record'), link_to(render_title($resource->accession, false), [$resource->accession, 'module' => 'accession'])); ?>

<?php echo render_show(__('Deaccession number'), $resource->identifier); ?>

<?php echo render_show(__('Scope'), render_value_inline($resource->scope)); ?>

<?php echo render_show(__('Date'), render_value_inline(Qubit::renderDate($resource->date))); ?>

<?php echo render_show(__('Description'), render_value($resource->description)); ?>

<?php echo render_show(__('Extent'), render_value($resource->extent)); ?>

<?php echo render_show(__('Reason'), render_value($resource->reason)); ?>

<?php slot('after-content'); ?>
  <?php if (QubitAcl::check($resource, ['delete', 'update', 'translate'])) { ?>
    <ul class="actions mb-3 nav gap-2">
      <?php if (QubitAcl::check($resource, 'update') || QubitAcl::check($resource, 'translate')) { ?>
        <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'deaccession', 'action' => 'edit'], ['class' => 'btn atom-btn-outline-light']); ?></li>
      <?php } ?>
      <?php if (QubitAcl::check($resource, 'delete')) { ?>
        <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'deaccession', 'action' => 'delete'], ['class' => 'btn atom-btn-outline-danger']); ?></li>
      <?php } ?>
    </ul>
  <?php } ?>
<?php end_slot(); ?>
