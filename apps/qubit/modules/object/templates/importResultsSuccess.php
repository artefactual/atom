<h1><?php echo __('Import initiated') ?></h1>

<section class="actions">
  <ul>
    <li><?php echo link_to(__('Back'), array('module' => 'object', 'action' => 'importSelect', 'type' => $sf_request->importType), array('class' => 'c-btn')) ?></li>
  </ul>
</section>
