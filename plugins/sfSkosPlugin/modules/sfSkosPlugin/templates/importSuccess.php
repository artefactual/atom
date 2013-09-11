<h1><?php echo __('Import completed') ?></h1>

<div class="note">
  <?php echo __('%1% terms imported in %2%s', array('%1%' => count($skos->terms), '%2%' => $timer->elapsed())) ?>
</div>

<section class="actions">
  <ul>
    <li><?php echo link_to(__('View %1%', array('%1%' => $taxonomy->__toString())), array($taxonomy, 'module' => 'taxonomy'), array('class' => 'c-btn')) ?></li>
    <li><?php echo link_to(__('Import more %1%', array('%1%' => $taxonomy->__toString())), array($taxonomy, 'module' => 'sfSkosPlugin', 'action' => 'import'), array('class' => 'c-btn')) ?></li>
  </ul>
</section>
