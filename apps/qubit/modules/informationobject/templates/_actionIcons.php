<div id="action-icons">
  <?php echo link_to(image_tag('report.png', array('alt' => __('Reports'))), array($sf_request->getAttribute('sf_route')->resource, 'module' => 'informationobject', 'action' => 'reports'), array('id' => 'Reports', 'title' => __('Reports'))) ?>
</div>
