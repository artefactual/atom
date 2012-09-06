<h1><?php echo __('Browse %1%', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))) ?></h1>

<table class="sticky-enabled">
  <thead>
    <tr>
      <th>
        <?php echo __('Media type') ?>
      </th><th>
        <?php echo __('Results') ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($terms as $item): ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
        <td>
          <div style="padding-left: 17px;">
            <?php echo link_to($item->getName(array('cultureFallback'=>true)), array('module' => 'digitalobject', 'action' => 'browse', 'mediatype' => $item->id)) ?>
          </div>
        </td><td>
          <?php echo QubitDigitalObject::getCount($item->id); ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
