<div class="field">
  <h3><?php echo __('Date(s)') ?></h3>

  <div xmlns:dc="http://purl.org/dc/elements/1.1/" about="<?php echo url_for(array($resource, 'module' => 'informationobject'), true) ?>">

    <ul>
      <?php foreach ($resource->getDates() as $item): ?>
        <li>
          <div class="date">
            <span property="dc:date" start="<?php echo $item->startDate ?>" end="<?php echo $item->endDate ?>"><?php echo Qubit::renderDateStartEnd($item->getDate(array('cultureFallback' => true)), $item->startDate, $item->endDate) ?></span>
            <?php if (sfConfig::get('app_default_template_informationobject') !== 'dc'): ?>
              <span class="date-type">(<?php echo $item->type->__toString() ?>)</span>
            <?php endif; ?>
            <dl>
              <?php if (isset($item->actor) && null !== $item->type->getRole()): ?>
                <dt><?php echo $item->type->getRole() ?></dt>
                <dd><?php echo render_title($item->actor) ?></dd>
              <?php endif; ?>
              <?php if (null !== $item->getPlace()): ?>
                <dt><?php echo __('Place') ?></dt>
                <dd><?php echo $item->getPlace() ?></dd>
              <?php endif; ?>
              <?php if (0 < strlen($item->description)): ?>
                <dt><?php echo __('Note') ?></dt>
                <dd><?php echo $item->description ?></dd>
              <?php endif; ?>
            </dl>

          </div>
        </li>
      <?php endforeach; ?>
    </ul>

  </div>
</div>
