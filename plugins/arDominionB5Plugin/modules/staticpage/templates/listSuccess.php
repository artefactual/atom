<h1><?php echo __('List pages'); ?></h1>

<table class="sticky-enabled table table-bordered">
  <thead>
    <tr>
      <th>
        <?php echo __('Title'); ?>
      </th><th>
        <?php echo __('Slug'); ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($pager->getResults() as $item) { ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd'; ?>">
        <td>
          <?php echo link_to(render_title($item->title), [$item, 'module' => 'staticpage']); ?>
        </td><td>
          <?php echo $item->slug; ?>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>

<?php echo get_partial('default/pager', ['pager' => $pager]); ?>

<section class="actions">
  <?php echo link_to(__('Add new'), ['module' => 'staticpage', 'action' => 'add'], ['class' => 'btn atom-btn-outline-light']); ?></li>
</section>
