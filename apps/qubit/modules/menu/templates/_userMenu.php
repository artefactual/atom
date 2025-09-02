<?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin') || $sf_context->getConfiguration()->isPluginEnabled('arOidcPlugin')) { ?>
    <?php include '_userMenu.mod_ext_auth.php'; ?>
<?php } else { ?>
    <?php include '_userMenu.mod_standard.php'; ?>
<?php } ?>
