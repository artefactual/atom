<footer>

  <?php if (QubitAcl::check('userInterface', 'translate')) { ?>
    <?php echo get_component('sfTranslatePlugin', 'translate'); ?>
  <?php } ?>

  <?php echo get_component_slot('footer'); ?>

  <div id="print-date">
    <?php echo __('Printed: %d%', ['%d%' => date('Y-m-d')]); ?>
  </div>

  <div id="js-i18n">
    <div id="read-more-less-links"
      data-read-more-text="<?php echo __('Read more'); ?>" 
      data-read-less-text="<?php echo __('Read less'); ?>">
    </div>
  </div>

</footer>

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
