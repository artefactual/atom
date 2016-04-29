<div class="row">
  <div class="span3">
    <?php echo __('Thematic area:') ?>
  </div>

  <div class="span3">
    <?php echo __('Archive type:') ?>
  </div>

  <div class="span3">
    <?php echo __('Regions:') ?>
  </div>
</div>

<form method="get">
  <?php foreach ($params as $paramName => $paramValue): ?>
    <?php echo '<input type="hidden" name="'.$paramName.'" value="'.$paramValue.'">' ?>
  <?php endforeach; ?>

  <div class="row">
    <div class="span3">
      <select name="thematicAreas">
        <option selected="selected"></option>
        <?php foreach ($thematicAreas as $r): ?>
          <option value="<?php echo $r->getId() ?>">
            <?php echo get_search_i18n($r->getData(), 'name', array('cultureFallback' => true)) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="span3">
      <select name="types">
        <option selected="selected"></option>
        <?php foreach ($repositoryTypes as $r): ?>
          <option value="<?php echo $r->getId() ?>">
            <?php echo get_search_i18n($r->getData(), 'name', array('cultureFallback' => true)) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="span3">
      <select name="regions">
        <option selected="selected"></option>
        <?php $regions = array() ?>

        <?php foreach ($repositories as $r): ?>
          <?php $region = get_search_i18n($r->getData(), 'region', array('allowEmpty' => false,
                                          'culture' => $this->selectedCulture, 'cultureFallback' => true)) ?>

          <?php if ($region && !in_array($region, $regions)): ?>
            <?php $regions[] = $region ?>
            <option value="<?php echo $region ?>"><?php echo $region ?></option>
          <?php endif; ?>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="row">
    <div class="span3">
      <button type="submit" class="btn fa fa-filter">&nbsp;<?php echo __('Set filters') ?></button>
    </div>
  </div>
</form>
