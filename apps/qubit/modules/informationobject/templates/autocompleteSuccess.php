<table>
  <tbody>
    <?php foreach ($pager->getResults() as $hit): ?>
      <?php $doc = $hit->getData() ?>
      <tr>
        <td>
          <?php echo link_to(render_title(get_search_autocomplete_string($doc)), array('module' => 'informationobject', 'slug' => $doc['slug'])) ?><?php if (isset($doc['publicationStatusId']) && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $doc['publicationStatusId']): ?> <span class="publicationStatus"><?php echo QubitTerm::getById($doc['publicationStatusId'])->__toString() ?></span><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
