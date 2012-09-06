<h2><?php echo __('Search for') ?></h2>

<div class="section search-terms">
  <ul>
  <?php $i = 0; foreach ($queryTerms as $item): ?>
    <li>
      <?php echo (0 < $i++) ? '<strong>'.strtoupper($item['operator'])."</strong>\n" : '' ?>
      <?php echo $item['term'] ?>
    </li>
  <?php endforeach; ?>
  </ul>
</div>
