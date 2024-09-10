<?php $gaKey = sfConfig::get('app_google_analytics_api_key', ''); ?>
<?php if (!empty($gaKey)) { ?>
    <script <?php echo __(sfConfig::get('csp_nonce', '')); ?> async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $gaKey; ?>"></script>
    <script <?php echo __(sfConfig::get('csp_nonce', '')); ?>>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    <?php include_slot('google_analytics'); ?>
    gtag('config', '<?php echo $gaKey; ?>');
    </script>
<?php } ?>
