<h1><?php echo __('View deaccession record') ?></h1>

<h1 class="label"><?php echo link_to_if(QubitAcl::check($resource, 'update'), render_title($resource), array($resource, 'module' => 'deaccession', 'action' => 'edit'), array('title' => __('Edit accession record'))) ?></h1>

<?php if (isset($errorSchema)): ?>
  <div class="messages error">
    <ul>
      <?php foreach ($errorSchema as $error): ?>
        <li><?php echo $error ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="field">
  <h3>Accession record</h3>
  <div class="value">
    <?php echo link_to($resource->accession->__toString(), array($resource->accession, 'module' => 'accession')) ?>
  </div>
</div>

<?php echo render_show(__('Deaccession number'), render_value($resource->identifier)) ?>

<?php echo render_show(__('Scope'), render_value($resource->scope)) ?>

<?php echo render_show(__('Date'), render_value(Qubit::renderDate($resource->date))) ?>

<?php echo render_show(__('Description'), render_value($resource->description)) ?>

<?php echo render_show(__('Extent'), render_value($resource->extent)) ?>

<?php echo render_show(__('Reason'), render_value($resource->reason)) ?>

<div class="actions section">

  <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

  <div class="content">
    <ul class="clearfix links">

      <?php if (QubitAcl::check($resource, 'update') || (QubitAcl::check($resource, 'translate'))): ?>
        <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'deaccession', 'action' => 'edit')) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($resource, 'delete')): ?>
        <li><?php echo link_to(__('Delete'), array($resource, 'module' => 'deaccession', 'action' => 'delete'), array('class' => 'delete')) ?></li>
      <?php endif; ?>

    </ul>
  </div>

</div>
