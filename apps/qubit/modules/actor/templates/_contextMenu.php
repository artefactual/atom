<div class="sidebar-lowering">

  <?php if (isset($subjectInfoObjects)): ?>

    <section class="list-menu">

      <h4><?php echo __('Subject of') ?></h4>

      <ul>
        <?php foreach ($subjectInfoObjects as $item): ?>
          <li><?php echo link_to(render_title($item), array($item, 'module' => 'informationobject')) ?></li>
        <?php endforeach; ?>
      </ul>

    </section>

  <?php endif; ?>

  <?php foreach ($relatedInfoObjects as $role => $informationObjects): ?>

    <section class="list-menu">

      <h4><?php echo __('%1% of', array('%1%' => $role)) ?></h4>

      <ul>
        <?php foreach ($informationObjects as $informationObject): ?>
          <li><?php echo link_to(render_title($informationObject), array($informationObject, 'module' => 'informationobject')) ?><?php if (QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $informationObject->getPublicationStatus()->status->id): ?> <span class="publicationStatus"><?php echo $informationObject->getPublicationStatus()->status ?></span><?php endif; ?></li>
        <?php endforeach; ?>
      </ul>

    </section>

  <?php endforeach; ?>

</div>
