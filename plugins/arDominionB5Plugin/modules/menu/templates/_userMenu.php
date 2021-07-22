<?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin')) { ?>
    <?php include '_userMenu.mod_cas.php'; ?>
<?php } else { ?>
    <?php include '_userMenu.mod_standard.php'; ?>
<?php } ?>
