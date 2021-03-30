<?php use_helper('Text'); ?>

<h1><?php echo render_title($title); ?></h1>

<?php if (isset($pager)) { ?>
    <?php $form->pager = true; ?>
<?php } ?>

<?php if (isset($form->confirm)) { ?>

  <h3 style="font-weight: normal;"><?php echo __('This will permanently modify %1% records.', ['%1%' => count($pager->hits)]); ?></h3>
  <div class="error">
    <h2><?php echo __('This action cannot be undone!'); ?></li></h2>
  </div>

<?php } ?>

<?php echo get_partial('search/advancedSearch', ['form' => $form, 'action' => 'globalReplace']); ?>

<?php if (isset($error)) { ?>

  <div class="error">
    <ul>
      <li><?php echo $error; ?></li>
    </ul>
  </div>

<?php } ?>
