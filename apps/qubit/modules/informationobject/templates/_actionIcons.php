<section id="action-icons">
  <ul>
    <li>
      <a href="<?php echo url_for(array($sf_request->getAttribute('sf_route')->resource, 'module' => 'informationobject', 'action' => 'reports')) ?>">
        <?php echo image_tag('report.png') ?>
        <?php echo __('Reports') ?>
      </a>
  </ul>
</section>
