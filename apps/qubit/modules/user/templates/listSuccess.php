<?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin') || $sf_context->getConfiguration()->isPluginEnabled('arOidcPlugin')) { ?>
    <?php include 'listSuccess.mod_ext_auth.php'; ?>
<?php } else { ?>
    <?php include 'listSuccess.mod_standard.php'; ?>
<?php } ?>
