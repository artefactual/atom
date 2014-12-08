<section id="popular-this-week">

  <h3><?php echo __('Popular this week') ?></h3>
  <ul>
    <?php foreach ($popularThisWeek as $item): ?>
      <?php $object = QubitObject::getById($item[0]); ?>
      <li>
        <a href="<?php echo url_for(array($object)) ?>">
          <?php echo esc_entities(render_title($object)) ?>
          <strong><?php echo __('%1% visits', array('%1%' => $item[1])) ?></strong>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

</section>
