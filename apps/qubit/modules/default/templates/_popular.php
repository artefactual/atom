<section id="popular-this-week">

  <h2><?php echo __('Popular this week'); ?></h2>
  <ul>
    <?php foreach ($popularThisWeek as $item) { ?>
      <?php $object = QubitObject::getById($item[0]); ?>
      <li><a href="<?php echo url_for([$object]); ?>"><?php echo render_title($object); ?><strong>&nbsp;&nbsp;<?php echo __('%1% visits', ['%1%' => $item[1]]); ?></strong></a></li>
    <?php } ?>
  </ul>

</section>
