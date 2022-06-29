<h1><?php echo __('Group %1%', ['%1%' => render_title($group)]); ?></h1>

<?php echo get_component('aclGroup', 'tabs'); ?>

<section id="content">

  <section id="groupDetails">

    <?php echo render_b5_section_heading(
        __('Group details'),
        QubitAcl::check($group, 'update'),
        [$group, 'module' => 'aclGroup', 'action' => 'edit'],
        ['class' => 'rounded-top']
    ); ?>

    <?php echo render_show(__('Name'), render_value_inline($group->name)); ?>

    <?php echo render_show(__('Description'), render_value($group->description)); ?>

    <?php echo render_show(__('Translate'), render_value_inline(__($translate))); ?>

  </section>

</section>

<?php echo get_partial('showActions', ['group' => $group]); ?>
