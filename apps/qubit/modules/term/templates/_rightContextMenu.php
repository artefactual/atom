<section id="action-icons">
  <ul>

    <li class="separator"><h4><?php echo __('Results') ?></h4></li>
    <li><?php echo $results ?></li>

    <?php if (QubitTerm::ROOT_ID != $resource->parent->id): ?>
      <li class="separator"><h4><?php echo __('Broader term') ?></h4></li>
      <li><?php echo link_to(render_title($resource->parent), array($resource->parent, 'module' => 'term')) ?></li>
    <?php endif; ?>

    <li class="separator"><h4><?php echo __('No. narrower terms') ?></h4></li>
    <li><?php echo count($resource->getChildren()) ?></li>

    <?php if (count(QubitRelation::getBySubjectOrObjectId($resource->id, array('typeId' => QubitTerm::TERM_RELATION_ASSOCIATIVE_ID))) > 0): ?>
      <li class="separator"><h4><?php echo __('Related terms') ?></h4></li>

      <?php foreach (QubitRelation::getBySubjectOrObjectId($resource->id, array('typeId' => QubitTerm::TERM_RELATION_ASSOCIATIVE_ID)) as $item): ?>
        <li><?php echo link_to(render_title($item->getOpposedObject($resource->id)), array($item->getOpposedObject($resource->id), 'module' => 'term')) ?></li>
      <?php endforeach; ?>
    <?php endif; ?>

  </ul>
</section>
