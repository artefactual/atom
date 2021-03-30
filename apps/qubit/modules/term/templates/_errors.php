<?php if (isset($errorSchema)) { ?>
  <div class="messages error">
    <ul>
      <?php foreach ($errorSchema as $error) { ?>
        <li><?php echo $error; ?></li>
      <?php } ?>
    </ul>
  </div>
<?php } ?>
