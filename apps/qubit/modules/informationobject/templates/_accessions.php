<?php foreach ($accessions as $item): ?>
  <div class="field">
    <h3>&nbsp;</h3>
    <div>
      <?php echo link_to(render_title($item->object), array($item->object, 'module' => 'accession')) ?>
    </div>
  </div>
<?php endforeach; ?>
