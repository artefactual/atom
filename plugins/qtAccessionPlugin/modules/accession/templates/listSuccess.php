<h1><?php echo __('List accession record') ?></h1>

<?php if (isset($error)): ?>

  <div class="search-results">
    <ul>
      <li><?php echo $error ?></li>
    </ul>
  </div>

<?php else: ?>

  <table class="sticky-enabled">
    <thead>
      <tr>
        <th>
          <?php echo __('Identifier') ?>
        </th><th>
          <?php echo __('Date') ?>
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($accessions as $item): ?>
        <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
          <td>
            <?php echo link_to(render_title($item), array($item, 'module' => 'accession')) ?>
          </td><td>
            <?php echo $item->date ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

<?php endif; ?>

<div class="search">
  <form action="<?php echo url_for(array('module' => 'accession', 'action' => 'list')) ?>">
    <input name="subquery" value="<?php echo esc_entities($sf_request->subquery) ?>"/>
    <input class="form-submit" type="submit" value="<?php echo __('Search accession') ?>"/>
  </form>
</div>
