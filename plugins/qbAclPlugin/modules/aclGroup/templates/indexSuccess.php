<?php echo get_component('aclGroup', 'tabs') ?>

<h1><?php echo __('View group') ?></h1>

<h1 class="label"><?php echo link_to_if(QubitAcl::check($group, 'edit'), render_title($group), array($group, 'module' => 'aclGroup', 'action' => 'edit'), array('title' => __('Edit group'))) ?></h1>

<div class="section">

  <?php echo render_show(__('Name'), $group->name) ?>

  <?php echo render_show(__('Description'), $group->description) ?>

  <?php echo render_show(__('Translate'), __($translate)) ?>

</div>

<?php if (QubitAcl::check($group, 'create') || QubitAcl::check($group, 'update') || QubitAcl::check($group, 'delete')): ?>

  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">

      <ul class="clearfix links">
        <?php if (QubitAcl::check($group, 'update')): ?>
          <li><?php echo link_to (__('Edit'), array($group, 'module' => 'aclGroup', 'action' => 'edit')) ?></li>
        <?php endif; ?>

        <?php if (QubitAcl::check($group, 'delete')): ?>
          <li><?php echo link_to (__('Delete'), array($group, 'module' => 'aclGroup', 'action' => 'delete'), array('class' => 'delete')) ?></li>
        <?php endif; ?>

        <?php if (QubitAcl::check($group, 'create')): ?>
          <li><?php echo link_to (__('Add new'), array('module' => 'aclGroup', 'action' => 'add')) ?></li>
        <?php endif; ?>
      </ul>

    </div>

  </div>
<?php endif; ?>
