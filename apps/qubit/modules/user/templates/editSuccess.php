<?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin') || $sf_context->getConfiguration()->isPluginEnabled('arOidcPlugin')) { ?>
    <?php include 'editSuccess.mod_ext_auth.php'; ?>
<?php } else { ?>
    <?php include 'editSuccess.mod_standard.php'; ?>
<?php } ?>
