<h1><?php echo __('Group %1%', ['%1%' => render_title($group)]); ?></h1>

<?php echo get_component('aclGroup', 'tabs'); ?>

<section id="content" class="p-0">

  <section id="groupDetails">

    <?php echo link_to_if(QubitAcl::check($group, 'update'), render_b5_section_label(__('Group details')), [$group, 'module' => 'aclGroup', 'action' => 'edit'], ['class' => 'text-primary']); ?>

    <?php echo render_show(__('Name'), render_value_inline($group->name)); ?>

    <?php echo render_show(__('Description'), render_value($group->description)); ?>

    <?php echo render_show(__('Translate'), render_value_inline(__($translate))); ?>

  </section>

</section>

<?php echo get_partial('showActions', ['group' => $group]); ?>
