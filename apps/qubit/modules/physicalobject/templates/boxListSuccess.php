<div id="preview-message">
  <?php echo __('Print preview'); ?>
  <?php echo link_to('Close', [$resource, 'module' => 'physicalobject']); ?>
</div>

<h1 class="do-print"><?php echo sfConfig::get('app_ui_label_physicalobject'); ?></h1>

<h1 class="label">
  <?php echo $resource->getLabel(); ?>
</h1>

<table class="sticky-enabled">
  <thead>
    <tr>
      <th>
        <?php echo __('Reference code'); ?>
      </th><th>
        <?php echo __('Title'); ?>
      </th><th>
        <?php echo __('Date(s)'); ?>
      </th><th>
        <?php echo __('Part of'); ?>
      </th><th>
        <?php echo __('Conditions governing access'); ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($informationObjects as $item) { ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd'; ?>">
        <td>
          <?php $isad = new sfIsadPlugin($item);
          echo render_value_inline($isad->referenceCode); ?>
        </td><td>
          <?php echo render_title($item); ?>
        </td><td>
          <ul>
            <?php foreach ($item->getDates() as $date) { ?>
              <li>
                <?php echo render_value_inline(Qubit::renderDateStartEnd($date->getDate(['cultureFallback' => true]), $date->startDate, $date->endDate)); ?> (<?php echo $date->getType(['cultureFallback' => true]); ?>)
                <?php if (isset($date->actor)) { ?>
                  <?php echo render_title($date->actor); ?>
                <?php } ?>
              </li>
            <?php } ?>
          </ul>
        </td><td>
          <?php if ($item->getCollectionRoot()->id != $item->id) { ?>
            <?php echo render_title($item->getCollectionRoot()); ?>
          <?php } ?>
        </td><td>
        <?php if (null != ($accessConditions = $item->getAccessConditions(['cultureFallback' => true]))) { ?>
          <?php echo render_value_inline($accessConditions); ?>
        <?php } else { ?>
          <?php echo __('None'); ?>
        <?php } ?>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>

<div id="result-count">
  <?php echo __('Showing %1% results', ['%1%' => $foundcount]); ?>
</div>
