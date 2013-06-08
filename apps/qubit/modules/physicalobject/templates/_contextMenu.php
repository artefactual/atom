<section id="physical-objects">

  <h4><?php echo sfConfig::get('app_ui_label_physicalobject') ?></h4>

  <div class="content">
    <ul>

      <?php foreach ($physicalObjects as $item): ?>
        <li>

          <?php if (isset($item->type)): ?>
            <?php echo $item->type ?>:
          <?php endif; ?>

          <?php echo link_to_if(QubitAcl::check($resource, 'update'), render_title($item), array($item, 'module' => 'physicalobject')) ?>

          <?php if (isset($item->location) && $sf_user->isAuthenticated()): ?>
            - <?php echo $item->getLocation(array('cultureFallback' => 'true')) ?>
          <?php endif; ?>

        </li>
      <?php endforeach; ?>

    </ul>
  </div>

</section>
