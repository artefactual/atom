<?php use_helper('Text') ?>

<h1><?php echo esc_entities($title) ?></h1>

<?php if (isset($pager)): ?>
    <?php $form->pager = true; ?>
<?php endif; ?>

<?php if (isset($form->confirm)): ?>

  <h3 style="font-weight: normal;"><?php echo __('This will permanently modify %1% records.', array('%1%' => count($pager->hits))) ?></h3>
  <div class="error">
    <h2><?php echo __('This action cannot be undone!') ?></li></h2>
  </div>

<?php endif; ?>

<?php echo get_partial('search/advancedSearch', array('form' => $form, 'action' => 'globalReplace')) ?>

<?php if (isset($error)): ?>

  <div class="error">
    <ul>
      <li><?php echo $error ?></li>
    </ul>
  </div>

<?php endif; ?>

<?php if (isset($pager) && !isset($form->confirm)): ?>

  <?php # echo get_partial('search/searchResults', array('pager' => $pager, 'timer' => $timer, 'culture' => $sf_user->getCulture())) ?>

<?php endif; ?>
