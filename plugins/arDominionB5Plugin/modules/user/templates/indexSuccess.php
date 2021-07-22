<?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin')) { ?>
    <?php include 'indexSuccess.mod_cas.php'; ?>
<?php } else { ?>
    <?php include 'indexSuccess.mod_standard.php'; ?>
<?php } ?>
