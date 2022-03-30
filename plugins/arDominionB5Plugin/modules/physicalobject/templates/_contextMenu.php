<section id="physical-objects">

  <h4 class="h5 mb-2"><?php echo sfConfig::get('app_ui_label_physicalobject'); ?></h4>
  <ul class="list-unstyled">

    <?php foreach ($physicalObjects as $item) { ?>
      <li>

        <?php if (isset($item->type)) { ?>
          <?php echo render_value_inline($item->type); ?>:
        <?php } ?>

        <?php echo link_to_if(QubitAcl::check($resource, 'update'), render_title($item), [$item, 'module' => 'physicalobject']); ?>

        <?php if (isset($item->location) && $sf_user->isAuthenticated()) { ?>
          - <?php echo render_value_inline($item->getLocation(['cultureFallback' => 'true'])); ?>
        <?php } ?>

      </li>
    <?php } ?>

  </ul>

</section>
