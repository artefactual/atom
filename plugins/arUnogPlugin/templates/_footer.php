<footer>

  <div class="footer-main bg-secondary text-white">
    <div class="container-xl">
      <div class="row py-3">
        <div class="col-md-3 col-lg-4">
          <h2><?php echo __('Follow and connect with us'); ?></h2>
          <ul class="list-unstyled d-flex flex-row justify-content-start">
            <li class="d-flex flex-column me-3"><a class="d-flex align-items-center p-0 text-white border rounded-circle" href="https://www.facebook.com/UNOGLibrary/" target="_blank"><i class="fab fa-facebook-f mx-auto" aria-hidden="true"></i></a></li>
            <li class="d-flex flex-column me-3"><a class="d-flex align-items-center p-0 text-white border rounded-circle" href="https://twitter.com/UNOGLibrary" target="_blank"><i class="fab fa-twitter mx-auto" aria-hidden="true"></i></a></li>
            <li class="d-flex flex-column me-3"><a class="d-flex align-items-center p-0 text-white border rounded-circle" href="https://www.youtube.com/channel/UCtbm9kgq0_M7r_VHDxqVD4g" target="_blank"><i class="fab fa-youtube mx-auto" aria-hidden="true"></i></a></li>
          </ul>
        </div>
        <div class="col-md-4 col-lg-4">
          <h2><?php echo __('Contact'); ?></h2>
          <p>
            <a class="d-inline-block text-white border p-3" href="https://ask.unog.ch/archives"><?php echo __('Ask an Archivist'); ?></a>
          </p>
          <p>
            Palais des Nations<br>
            1211 Geneva 1211, Switzerland<br>
            Phone: +41 (0)22 917 1234<br>
            <a class="text-white" href="https://www.google.com/maps/place/Palais+des+Nations/@46.2269806,6.1334386,602a,35y,90h,39.23t/data=!3m1!1e3!4m5!3m4!1s0x478c64fcaacb2e3f:0xbaabef97619cd473!8m2!3d46.2266053!4d6.1404813" target="_blank"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> See location</a>
          </p>
        </div>
        <div class="col-md-5 col-lg-4">
          <h2><?php echo __('Practical Information'); ?></h2>
          <div class="d-flex">
            <ul class="list-unstyled">
              <li><a class="text-white" href="https://www.ungeneva.org/en/practical-information/students-researchers">Students and researchers</a></li>
              <li><a class="text-white" href="https://www.ungeneva.org/practical-information/services">Services operating hours</a></li>
              <li><a class="text-white" href="https://www.ungeneva.org/about/accessibility"><i class="fab fa-accessible-icon" aria-hidden="true"></i> Accessibility</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="footer-ownership pt-3 pb-2">
    <div class="d-flex justify-content-between">
      <div>
        <a href="https://www.un.org/en/sections/about-website/terms-use/" target="_blank">Terms of Use</a> |
        <a href="https://www.un.org/en/about-us/privacy-notice/" target="_blank">Privacy Notice</a> |
        <a href="https://www.ungeneva.org/multilingualism-disclaimer">Multilingualism Disclaimer</a>
      </div>
      <div>© United Nations Office at Geneva</div>
    </div>
  </div>


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
  <script>
    window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
    ga('create', '<?php echo $gaKey; ?>', 'auto');
    <?php include_slot('google_analytics'); ?>
    ga('send', 'pageview');
  </script>
  <script async src='https://www.google-analytics.com/analytics.js'></script>
<?php } ?>
