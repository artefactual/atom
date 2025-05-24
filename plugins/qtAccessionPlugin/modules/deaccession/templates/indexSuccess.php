<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1 class="multiline">
    <?php echo __('View deaccession record'); ?>
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

<div class="field">
  <h3>Accession record</h3>
  <div class="value">
    <?php echo link_to(render_title($resource->accession, false), [$resource->accession, 'module' => 'accession']); ?>
  </div>
</div>

<?php echo render_show(__('Deaccession number'), $resource->identifier); ?>

<?php echo render_show(__('Scope'), render_value($resource->scope)); ?>

<?php echo render_show(__('Date'), render_value(Qubit::renderDate($resource->date))); ?>

<?php echo render_show(__('Description'), render_value($resource->description)); ?>

<?php echo render_show(__('Extent'), render_value($resource->extent)); ?>

<?php echo render_show(__('Reason'), render_value($resource->reason)); ?>

<?php slot('after-content'); ?>
  <section class="actions">
    <ul>
      <?php if (QubitAcl::check($resource, 'update') || QubitAcl::check($resource, 'translate')) { ?>
        <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'deaccession', 'action' => 'edit'], ['class' => 'c-btn']); ?></li>
      <?php } ?>
      <?php if (QubitAcl::check($resource, 'delete')) { ?>
        <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'deaccession', 'action' => 'delete'], ['class' => 'c-btn c-btn-delete']); ?></li>
      <?php } ?>
    </ul>
  </section>
<?php end_slot(); ?>
