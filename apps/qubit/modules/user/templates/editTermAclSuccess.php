<h1><?php echo __('Edit %1% permissions', array('%1%' => sfConfig::get('apps_ui_label_term'))) ?></h1>

<h1 class="label"><?php echo render_title($resource) ?></h1>

<?php echo get_component('aclGroup', 'termAclForm', array('resource' => $resource, 'permissions' => $permissions, 'form' => $form)) ?>