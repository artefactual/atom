<table>
  <tbody>
    <?php foreach ($repositories as $item) { ?>
      <tr>
        <td>
          <?php echo link_to(render_title($item->getAuthorizedFormOfName(['cultureFallback' => true])), [$item, 'module' => 'repository']); ?>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>
