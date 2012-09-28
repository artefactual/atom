<section>
  <h4><?php echo __('Subject of') ?></h4>
  <div class="content">
    <ul>
      <?php foreach ($subjectInfoObjects as $item): ?>
        <li><?php echo link_to(render_title($item), array($item, 'module' => 'informationobject')) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php foreach ($relatedInfoObjects as $role => $informationObjects): ?>
  <section>
    <h4><?php echo __('%1% of', array('%1%' => $role)) ?></h4>
    <div class="content">
      <ul>
        <?php foreach ($informationObjects as $informationObject): ?>
          <li><?php echo link_to(render_title($informationObject), array($informationObject, 'module' => 'informationobject')) ?><?php if (QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $informationObject->getPublicationStatus()->status->id): ?> <span class="publicationStatus"><?php echo $informationObject->getPublicationStatus()->status ?></span><?php endif; ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
<?php endforeach; ?>

<?php echo get_partial('actor/format', array('resource' => $resource)) ?>
