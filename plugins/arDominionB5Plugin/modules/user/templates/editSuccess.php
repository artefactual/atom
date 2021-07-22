<?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin')) { ?>
    <?php include 'editSuccess.mod_cas.php'; ?>
<?php } else { ?>
    <?php include 'editSuccess.mod_standard.php'; ?>
<?php } ?>
