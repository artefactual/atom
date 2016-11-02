<html>
<head>
  <style>
    table {
        border-collapse: collapse;
    }

    table, th, td {
        border: 1px solid black;
    }
  </style>
</head>

<body>
  <h1 class="label"><?php echo $this->i18n->__('Item list') ?></h1>

  <?php $row = $startrow = 1; foreach ($results as $parent => $items): ?>
    <h2 class="element-invisible"><?php echo $this->i18n->__('%1% hierarchy', array('%1%' => sfConfig::get('app_ui_label_informationobject'))) ?></h2>
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
          <th><?php echo $this->i18n->__('#') ?></th>
          <?php if ($includeThumbnails): ?>
            <th><?php echo $this->i18n->__('Thumbnail') ?></th>
          <?php endif; ?>
          <th><?php echo $this->i18n->__('Reference code') ?></th>
          <th><?php echo $this->i18n->__('Title') ?></th>
          <th><?php echo $this->i18n->__('Dates') ?></th>
          <th><?php echo $this->i18n->__('Access restrictions') ?></th>
        <?php if (sfContext::getInstance()->user->isAuthenticated()): ?>
          <th><?php echo $this->i18n->__('Retrieval information') ?></th>
        <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td class="row-number"><?php echo $row++ ?></td>
            <?php if ($includeThumbnails): ?>
            <td>
            <?php if (null != ($do = $item['resource']->getDigitalObject()) && (null != $do->thumbnail)):  ?>
              <?php echo image_tag($do->thumbnail->getFullPath(), array('alt' => '')) ?>
            <?php else: ?>
              <?php echo $this->i18n->__('N/A') ?>
            <?php endif; ?>
            </td>
            <?php endif; ?>
            <td><?php echo $item['referenceCode'] ?></td>
            <td><?php echo $item['title'] ?></td>
            <td><?php echo $item['dates'] ?></td>
            <td><?php echo isset($item['accessConditions']) ? $item['accessConditions'] : $this->i18n->__('None') ?></td>
          <?php if (sfContext::getInstance()->user->isAuthenticated()): ?>
            <td><?php echo $item['locations'] ?></td>
          <?php endif; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="result-count">
      <?php echo $this->i18n->__('Showing %1% to %2% of %3% results', array(
        '%1%' => $startrow,
        '%2%' => ($startrow += count($items)) - 1,
        '%3%' => $resultCount)) ?>
    </div>
  <?php endforeach; ?>
</body>
</html>
