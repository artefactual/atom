<table>
  <tbody>
    <?php foreach($repositories as $item): ?>
      <tr>
        <td>
          <?php echo link_to($item->getAuthorizedFormOfName(array('cultureFallback' => true)), array($item, 'module' => 'repository')) ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
