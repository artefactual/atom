<?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin')) { ?>
    <?php include '_showActions.mod_cas.php'; ?>
<?php } else { ?>
    <?php include '_showActions.mod_standard.php'; ?>
<?php } ?>
