<div id="footer">
  <div class="section">

    <?php echo link_to(image_tag('xhtml10', array('alt' => __('This page is valid XHTML 1.0'))), 'http://validator.w3.org/check?'.http_build_query(array('uri' => $sf_request->getUri().'?'.http_build_query(array(session_name() => session_id())), 'ss' => 1))) ?>

    <?php if (QubitAcl::check('userInterface', 'translate')): ?>
      <?php // echo get_component('sfTranslatePlugin', 'translate') ?>
    <?php endif; ?>

    <?php echo get_component_slot('footer') ?>

  </div> <!-- /.section -->
</div> <!-- /#footer -->
