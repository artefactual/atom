<form method="get" action="[?php echo url_for('<?php echo $this->getModuleName() ?>/search') ?]">
  
  [?php include_partial('simpleForm', array('form' => $form)) ?]

  <?php if ($this->get('mode.simple.form.submit') !== false): ?>
    <input type="submit" value="[?php echo __('<?php echo $this->get('mode.simple.form.submit', 'Search') ?>') ?]" />
  <?php endif ?>

  <input type="hidden" name="do" value="search" />
</form>
