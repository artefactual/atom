<h1><?php echo __('Import initiated'); ?></h1>

<section class="actions mb-3">
  <?php echo link_to(__('Back'), ['module' => 'object', 'action' => 'importSelect', 'type' => $sf_request->importType], ['class' => 'btn atom-btn-outline-light']); ?>
</section>
