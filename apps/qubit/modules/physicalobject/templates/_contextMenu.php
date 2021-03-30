<section id="physical-objects">

  <h4><?php echo sfConfig::get('app_ui_label_physicalobject'); ?></h4>

  <div class="content">
    <ul>

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
  </div>

</section>
