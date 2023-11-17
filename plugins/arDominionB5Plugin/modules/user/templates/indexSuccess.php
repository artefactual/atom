<?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin') || $sf_context->getConfiguration()->isPluginEnabled('arOidcPlugin')) { ?>
    <?php include 'indexSuccess.mod_ext_auth.php'; ?>
<?php } else { ?>
    <?php include 'indexSuccess.mod_standard.php'; ?>
<?php } ?>
