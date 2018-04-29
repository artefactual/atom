<table>
  <tbody>
    <?php foreach ($physicalObjects as $item): ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
        <td>
          <?php echo link_to($item, array($item, 'module' => 'physicalobject')) ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
