<footer>

  <div class="container">

    <div class="row">

      <div class="span3">
        <h5>Chinese Canadian Artifacts Project</h5>
        <ul>
          <li><a href="https://ccap.uvic.ca/index.php/about">About CCAP</a></li>
          <li><a href="https://ccap.uvic.ca/index.php/repository/browse">CCAP Partners</a></li>
          <li><a href="https://ccap.uvic.ca/index.php/contrib">Information for Contributors</a></li>
          <li><a href="mailto:ccap@uvic.ca">Contact</a></li>
        </ul>
      </div>

      <div class="span3 center">
        <h5>Funded by</h5>
        <?php echo image_tag('/plugins/arUvicPlugin/images/bc.png', array('id' => 'bc')) ?>
      </div>

      <div class="span3 center">
        <h5>Supported by</h5>
        <?php echo image_tag('/plugins/arUvicPlugin/images/uvic.svg') ?>
        <?php echo image_tag('/plugins/arUvicPlugin/images/bcma.png') ?>
      </div>

      <div class="span3 right">
        <h5>Powered by</h5>
        <a href="http://www.accesstomemory.org"><?php echo image_tag('/plugins/arUvicPlugin/images/atom-logo.png', array('id' => 'atom-logo')) ?></a>
      </div>

    </div>

    <?php if (QubitAcl::check('userInterface', 'translate')): ?>
      <?php echo get_component('sfTranslatePlugin', 'translate') ?>
    <?php endif; ?>

    <?php echo get_component_slot('footer') ?>

  </div>

</footer>
