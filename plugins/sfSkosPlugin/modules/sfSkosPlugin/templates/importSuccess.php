<h1><?php echo __('Import successful') ?></h1>

<?php echo $form->renderFormTag(url_for(array('module' => 'sfSkosPlugin', 'action' => 'import'))) ?>

  <div>
    <?php echo __('%1% terms imported in %2%s', array('%1%' => count($skos->terms), '%2%' => $timer->elapsed())) ?>
  </div>

  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">
        <li><?php echo link_to(__('View %1%', array('%1%' => $taxonomy->__toString())), array($taxonomy, 'module' => 'taxonomy')) ?></li>
        <li><?php echo link_to(__('Import more %1%', array('%1%' => $taxonomy->__toString())), array($taxonomy, 'module' => 'sfSkosPlugin', 'action' => 'import')) ?></li>
      </ul>
    </div>

  </div>

</form>
