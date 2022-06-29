<h1><?php echo __('List pages'); ?></h1>

<div class="table-responsive mb-3">
  <table class="table table-bordered mb-0">
    <thead>
      <tr>
        <th>
          <?php echo __('Title'); ?>
        </th><th>
          <?php echo __('Slug'); ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($pager->getResults() as $item) { ?>
        <tr>
          <td>
            <?php echo link_to(render_title($item->title), [$item, 'module' => 'staticpage']); ?>
          </td><td>
            <?php echo $item->slug; ?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php echo get_partial('default/pager', ['pager' => $pager]); ?>

<section class="actions mb-3">
  <?php echo link_to(__('Add new'), ['module' => 'staticpage', 'action' => 'add'], ['class' => 'btn atom-btn-outline-light']); ?></li>
</section>
