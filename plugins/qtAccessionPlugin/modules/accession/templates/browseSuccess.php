<?php decorate_with('layout_1col') ?>
<?php use_helper('Date') ?>

<?php slot('title') ?>
  <h1><?php echo __('Browse accession') ?></h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <div class="search">
    <form action="<?php echo url_for(array('module' => 'accession', 'action' => 'list')) ?>">
      <input name="subquery" value="<?php echo esc_entities($sf_request->subquery) ?>"/>
      <input class="form-submit" type="submit" value="<?php echo __('Search accession') ?>"/>
    </form>
  </div>

  <table class="table table-bordered sticky-enabled">
    <thead>
      <tr>
        <th>
          <?php echo __('Name') ?>
        </th>
        <?php if ('alphabetic' != $sf_request->sort): ?>
          <th>
            <?php echo __('Updated') ?>
          </th>
        <?php endif; ?>
      </tr>
    </thead><tbody>
      <?php foreach ($pager->getResults() as $item): ?>
        <tr></tr>>
          <td>
            <?php echo link_to(render_title($item), array($item, 'module' => 'accession')) ?>
          </td>
          <?php if ('alphabetic' != $sf_request->sort): ?>
            <td>
              <?php echo format_date($item->updatedAt, 'f') ?>
            </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<?php end_slot() ?>

<?php slot('after-content') ?>

  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

<?php end_slot() ?>
