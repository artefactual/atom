<div id="preview-message">
  <?php echo __('Print preview') ?>
  <?php echo link_to('Close', array($resource, 'module' => 'informationobject', 'action' => 'fileList')) ?>
</div>

<h1 class="label"><?php echo __('File list') ?></h1>

<?php $row = $startrow = 1; foreach ($results as $parent => $items): ?>

  <h2 class="element-invisible"><?php echo __('%1% hierarchy', array('%1%' => sfConfig::get('app_ui_label_informationobject'))) ?></h2>
  <div class="resource-hierarchy">
    <ul>
    <?php foreach ($items[0]['resource']->getAncestors()->orderBy('lft') as $ancestor): ?>
      <?php if (QubitInformationObject::ROOT_ID != intval($ancestor->id)): ?>
      <li><h3><?php echo QubitInformationObject::getStandardsBasedInstance($ancestor)->__toString() ?></h3></li>
      <?php endif; ?>
    <?php endforeach; ?>
    </ul>
  </div>

  <table>
    <thead>
      <tr>
        <th><?php echo __('#') ?></th>
        <th><?php echo __('Reference code') ?></th>
        <th><?php echo __('Title') ?></th>
        <th><?php echo __('Dates') ?></th>
        <th><?php echo __('Access restrictions') ?></th>
      <?php if ($sf_user->isAuthenticated()): ?>
        <th><?php echo __('Retrieval information') ?></th>
      <?php endif; ?>
      </tr>
    </thead><tbody>
    <?php foreach ($items as $item): ?>
      <tr>
        <td class="row-number"><?php echo $row++ ?></td>
        <td><?php echo $item['referenceCode'] ?></td>
        <td><?php echo $item['title'] ?></td>
        <td><?php echo $item['dates'] ?></td>
        <td><?php echo isset($item['accessConditions']) ? $item['accessConditions'] : __('None') ?></td>
      <?php if ($sf_user->isAuthenticated()): ?>
        <td><?php echo $item['locations'] ?></td>
      <?php endif; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <div class="result-count">
    <?php echo __('Showing %1% to %2% of %3% results', array(
      '%1%' => $startrow,
      '%2%' => ($startrow += count($items)) - 1,
      '%3%' => $resultCount)) ?>
  </div>
<?php endforeach; ?>
