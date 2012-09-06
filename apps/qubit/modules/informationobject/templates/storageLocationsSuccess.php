<div id="preview-message">
  <?php echo __('Print preview') ?>
  <?php echo link_to('Close', array($resource, 'module' => 'informationobject')) ?>
</div>

<h1 class="do-print"><?php echo __('Physical storage locations') ?></h1>

<h1 class="label">
  <?php $isad = new sfIsadPlugin($resource); echo $isad->__toString() ?>
</h1>

<table class="sticky-enabled">
  <thead>
    <tr>
      <th>
        <?php echo __('#') ?>
      </th><th>
        <?php echo __('Name') ?>
      </th><th>
        <?php echo __('Location') ?>
      </th><th>
        <?php echo __('Type') ?>
      </th>
    </tr>
  </thead><tbody>
    <?php $row = 1; foreach ($physicalObjects as $item): ?>
      <tr>
        <td>
          <?php echo $row++ ?>
        </td><td>
          <?php echo link_to($item->name, array($item, 'module' => 'physicalobject')) ?>
        </td><td>
          <?php echo $item->location ?>
        </td><td>
          <?php echo $item->type->__toString() ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<div id="result-count">
  <?php echo __('Showing %1% results', array('%1%' => $foundcount)) ?>
</div>
