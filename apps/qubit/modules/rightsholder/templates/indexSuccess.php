<h1><?php echo __('View rights holder') ?></h1>

<?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h1 class="label">'.render_title($resource).'</h1>', array($resource, 'module' => 'rightsholder', 'action' => 'edit'), array('title' => __('Edit rights holder'))) ?>

<?php if (isset($errorSchema)): ?>
  <div class="messages error">
    <ul>
      <?php foreach ($errorSchema as $error): ?>
        <li><?php echo $error ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php echo render_show(__('Authorized form of name'), render_value($resource->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?>

<div class="section" id="contactArea">

  <h2><?php echo __('Contact area') ?></h2>

  <?php foreach ($resource->contactInformations as $contactItem): ?>
    <?php echo get_partial('contactinformation/contactInformation', array('contactInformation' => $contactItem)) ?>
  <?php endforeach; ?>

</div> <!-- /.section#contactArea -->

<div class="actions section">

  <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

  <div class="content">
    <ul class="clearfix links">

      <?php if (QubitAcl::check($resource, 'update')): ?>
        <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'rightsholder', 'action' => 'edit'), array('title' => __('Edit'))) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($resource, 'delete')): ?>
        <li><?php echo link_to(__('Delete'), array($resource, 'module' => 'rightsholder', 'action' => 'delete'), array('class' => 'delete', 'title' => __('Delete'))) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($resource, 'create')): ?>
        <li><?php echo link_to(__('Add new'), array('module' => 'rightsholder', 'action' => 'add'), array('title' => __('Add new'))) ?></li>
      <?php endif; ?>

    </ul>
  </div>

</div>
