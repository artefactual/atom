<?php if (isset($errorSchema)) { ?>
  <div class="alert alert-danger" role="alert">
    <ul class="<?php echo render_b5_show_list_css_classes(); ?>">
      <?php foreach ($errorSchema as $error) { ?>
        <?php $error = sfOutputEscaper::unescape($error); ?>
        <li><?php echo $error->getMessage(); ?></li>
      <?php } ?>
    </ul>
  </div>
<?php } ?>
