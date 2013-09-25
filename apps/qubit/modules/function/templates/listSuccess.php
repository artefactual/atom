<?php use_helper('Date') ?>
<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>
  <h1><?php echo __('List %1%', array('%1%' => sfConfig::get('app_ui_label_function'))) ?></h1>
<?php end_slot() ?>

<?php slot('before-content') ?>
  <div class="nav">
    <div class="search">
      <form action="<?php echo url_for(array('module' => 'function', 'action' => 'list')) ?>">
        <input name="subquery" value="<?php echo esc_entities($sf_request->subquery) ?>"/>
        <input class="form-submit" type="submit" value="<?php echo __('Search %1%', array('%1%' => sfConfig::get('app_ui_label_function'))) ?>"/>
      </form>
    </div>
  </div>
<?php end_slot() ?>

<?php slot('content') ?>

  <table class="table table-bordered sticky-enabled">
    <thead>
      <tr>
        <th>
          <?php echo __('Name') ?>
        </th><th>
          <?php echo __('Updated') ?>
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($pager->getResults() as $item): ?>
        <tr>
          <td>
            <?php echo link_to(render_title($item->getAuthorizedFormOfName(array('cultureFallback' => true))), $item) ?>
          </td><td>
            <?php echo format_date($item->updatedAt, 'f') ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<?php end_slot() ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
