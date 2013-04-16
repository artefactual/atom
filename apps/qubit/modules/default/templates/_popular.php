<section id="popular-this-week">

  <h3><?php echo __('Popular this week') ?></h3>
  <ul>
    <?php foreach ($popularThisWeek as $item): ?>
      <?php $object = QubitObject::getById($item[0]); ?>
      <li>
        <?php echo link_to(render_title($object), url_for(array($object))) ?>
        <strong><?php echo __('%1% visits', array('%1%' => $item[1])) ?></strong>
      </li>
    <?php endforeach; ?>
  </ul>

</section>
