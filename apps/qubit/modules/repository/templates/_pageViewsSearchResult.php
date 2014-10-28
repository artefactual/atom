<tr>
  <td>
    <?php echo $rank ?>
  </td>
  <td>
    <?php echo $referenceCodes[($hit['ID'])] ?>
  </td>
  <td>
    <p class="title"><?php echo link_to($hit['TITLE'], array('slug' => $hit['SLUG'])) ?></p>
  </td>
  <td>
    <?php if ($hit['PARENT_ID'] > 1): ?>
      <?php $parent = $parents[($hit['ID'])] ?>
      <?php $linkText = sprintf('%s (%s)', $parent->title, $parent->identifier) ?>
      <p class="title"><?php echo link_to($linkText, array('slug' => $parent->slug)) ?></p>
    <?php endif; ?>
  </td>
  <td>
    <?php echo $hit['hits'] ?>
  </td>
</tr>
