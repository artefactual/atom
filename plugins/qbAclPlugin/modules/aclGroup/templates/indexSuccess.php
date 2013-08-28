<h1><?php echo __('Group %1%', array('%1%' => render_title($group))) ?></h1>

<?php echo get_component('aclGroup', 'tabs') ?>

<section id="content">

  <section id="groupDetails">

    <?php echo link_to_if(QubitAcl::check($group, 'update'), '<h2>'.__('Group details').'</h2>', array($group, 'module' => 'aclGroup', 'action' => 'edit')) ?>

    <?php echo render_show(__('Name'), $group->name) ?>

    <?php echo render_show(__('Description'), $group->description) ?>

    <?php echo render_show(__('Translate'), __($translate)) ?>

  </section>

</section>

<?php echo get_partial('showActions', array('group' => $group)) ?>
