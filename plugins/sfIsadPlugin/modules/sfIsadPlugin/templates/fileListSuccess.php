<h1><?php echo __('File list'); ?></h1>

<?php echo get_partial('default/breadcrumb', ['objects' => $resource->ancestors->andSelf()]); ?>

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
          <?php echo link_to(render_title($item), [$item, 'module' => 'informationobject']); ?>
        </td><td>
          <ul>
            <?php foreach ($item->getDates() as $date) { ?>
              <li>
                <?php echo render_value_inline(Qubit::renderDateStartEnd($date->getDate(['cultureFallback' => true]), $date->startDate, $date->endDate)); ?> (<?php echo $date->getType(['cultureFallback' => true]); ?>)
                <?php if (isset($date->actor)) { ?>
                  <?php echo link_to(render_title($date->actor), [$date->actor, 'module' => 'actor']); ?>
                <?php } ?>
              </li>
            <?php } ?>
          </ul>
        </td><td>
          <?php echo render_value_inline($item->getAccessConditions(['cultureFallback' => true])); ?>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>

<?php echo get_partial('default/pager', ['pager' => $pager]); ?>
