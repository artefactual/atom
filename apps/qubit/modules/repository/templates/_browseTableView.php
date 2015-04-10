<table class="table table-bordered sticky-enabled">
  <thead>
    <tr>
      <td style="width: 40%"><?php echo __('Name') ?></td>
      <td style="width: 20%"><?php echo __('Region') ?></td>
      <td style="width: 20%"><?php echo __('Locality') ?></td>
      <td style="width: 20%"><?php echo __('Thematic Area') ?></td>
    </tr>
  </thead>

  <?php foreach ($pager->getResults() as $hit): ?>
    <?php $resource = QubitRepository::getById($hit->getId()); ?>
    <tr>
      <td>
        <?php if ($resource->existsLogo()): ?>
          <?php echo image_tag($resource->getLogoPath(), array('height' => '10%', 'width' => '10%')) ?>
        <?php endif; ?>
        <?php echo link_to(render_title($resource), array('module' => 'repository', 'slug' => $resource->slug)) ?>
      </td>
      <td>
        <?php foreach ($resource->getTermRelations(QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID) as $item): ?>
          <li><?php echo __(render_value($item->term)) ?></li>
        <?php endforeach; ?>
      </td>
      <td></td>
      <td>
        <?php foreach ($resource->getTermRelations(QubitTaxonomy::THEMATIC_AREA_ID) as $item): ?>
          <li><?php echo __(render_value($item->term)) ?></li>
        <?php endforeach; ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
