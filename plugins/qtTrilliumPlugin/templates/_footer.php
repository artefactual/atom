<div id="footer">
  <div class="section">

    <?php if (QubitAcl::check('userInterface', 'translate')): ?>
      <?php echo get_component('sfTranslatePlugin', 'translate') ?>
    <?php endif; ?>

    <?php echo get_component_slot('footer') ?>

  </div> <!-- /.section -->
</div> <!-- /#footer -->
