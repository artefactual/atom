<?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin') || $sf_context->getConfiguration()->isPluginEnabled('arOidcPlugin')) { ?>
    <?php include '_showActions.mod_ext_auth.php'; ?>
<?php } else { ?>
    <?php include '_showActions.mod_standard.php'; ?>
<?php } ?>
