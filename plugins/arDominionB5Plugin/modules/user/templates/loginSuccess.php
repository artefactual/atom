<?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin')) { ?>
    <?php include 'loginSuccess.mod_cas.php'; ?>
<?php } else { ?>
    <?php include 'loginSuccess.mod_standard.php'; ?>
<?php } ?>
