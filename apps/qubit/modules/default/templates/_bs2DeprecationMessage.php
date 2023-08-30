<?php echo javascript_include_tag('bs2DeprecationMessage'); ?>

<div class="animateNicely" id="bs2-deprecation-message">
    <div class="alert alert-danger alert-banner">
        <div id="bs2-deprecation-message-content">
            <?php echo __('Bootstrap 2 themes have been deprecated and will be removed in a future release. Please consider switching to a Bootstrap 5 theme. %1%More info.%2%', ['%1%' => '<a href="https://www.accesstomemory.org/en/docs/latest/admin-manual/customization/theming/#bs2-update" target="_blank">', '%2%' => '</a>']); ?>
        </div>
        <button type="button" class="close">&times;</button>
    </div>
</div>
