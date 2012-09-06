<table>
  <tbody>
    <?php foreach ($taxonomies as $item): ?>
      <tr>
        <td>
          <?php echo link_to(render_title($item), array($item, 'module' => 'taxonomy')) ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
