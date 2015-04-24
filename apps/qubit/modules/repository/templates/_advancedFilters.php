<div class="row">
  <div class="span3">
    <div>
    <?php echo __('Thematic area:') ?></div>
  </div>

  <div class="span3">
    <?php echo __('Archive type:') ?>
  </div>

  <div class="span3">
    <?php echo __('Regions:') ?>
  </div>
</div>

<div class="row">
  <form>
    <div class="span3">
      <select name="thematicAreas">
        <?php foreach ($thematicAreas as $thematicArea): ?>
          <option><?php echo $thematicArea ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="span3">
      <select name="types">
        <?php foreach ($repositoryTypes as $type): ?>
          <option><?php echo $type ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="span3">
      <select name="regions">
        <?php foreach ($regions as $region): ?>
          <option><?php echo $region ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>
</div>

<div class="row">
  <div class="span3">
    <a class="btn icon-filter">&nbsp;<?php echo __('Set filter') ?></a>
  </div>
</div>
