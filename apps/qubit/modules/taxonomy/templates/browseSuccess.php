<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php if (isset($icon)): ?>
      <?php echo image_tag('/images/icons-large/icon-'.$icon.'.png') ?>
    <?php endif; ?>
    <?php echo __('Showing %1% results', array('%1%' => $pager->getNbResults())) ?>
    <span class="sub"><?php echo render_title($resource) ?></span>
  </h1>
<?php end_slot() ?>

<?php slot('before-content') ?>

  <?php if ($sf_user->isAuthenticated() ): ?>
    <div class="manage-button">
      <?php echo link_to(__('Manage %1%', array('%1%' => 'taxonomy')), array($resource, 'module' => 'taxonomy'), array('class' => 'btn btn-small')) ?>
    </div>
  <?php endif; ?>

<?php end_slot() ?>

<?php slot('content') ?>

  <table class="table table-bordered sticky-enabled">
    <thead>
      <tr>
        <th>
          <?php echo render_title($resource) ?>
        </th><th>
          <?php echo __('Results') ?>
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($terms as $item): ?>
        <tr>
          <td>
            <?php echo link_to(render_title($item), array($item, 'module' => 'term')) ?>
          </td><td>
            <?php echo QubitTerm::countRelatedInformationObjects($item->id) ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<?php end_slot() ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
