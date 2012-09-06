<table>
  <tbody>
    <?php foreach ($informationObjects as $item): ?>
      <tr>
        <td>
          <?php echo link_to(render_title(new sfIsadPlugin($item)), array($item, 'module' => 'informationobject')) ?><?php if (QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $item->getPublicationStatus()->status->id): ?> <span class="publicationStatus"><?php echo $item->getPublicationStatus()->status ?></span><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
