<?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin')) { ?>
    <?php include 'listSuccess.mod_cas.php'; ?>
<?php } else { ?>
    <?php include 'listSuccess.mod_standard.php'; ?>
<?php } ?>
