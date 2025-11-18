<?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin') || $sf_context->getConfiguration()->isPluginEnabled('arOidcPlugin')) { ?>
    <?php include 'loginSuccess.mod_ext_auth.php'; ?>
<?php } else { ?>
    <?php include 'loginSuccess.mod_standard.php'; ?>
<?php } ?>
