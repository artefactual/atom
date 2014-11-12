<?php decorate_with('layout_2col') ?>

<?php slot('sidebar') ?>
  <?php echo get_component('repository', 'logo') ?>
<?php end_slot() ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <div><?php echo __('Page views') ?> - <?php echo $resource->authorizedFormOfName ?></div>
    <span><?php echo __('Showing %1% results', array('%1%' => $pager->getNbResults())) ?></span>
  </h1>
<?php end_slot() ?>

<?php slot('before-content') ?>

  <section class="header-options">
    <div class="row">
      <div class="span3">
        Start date
      </div>
      <div class="span3">
        End date
      </div>
    </div>
    <div class="row">
      <form>
        <div class="span3">
          <input name="start_date" type="text" placeholder="YYYY-MM-DD" value="<?php echo esc_specialchars($startDate) ?>" />
        </div>
        <div class="span3">
          <input name="end_date" type="text" placeholder="YYYY-MM-DD" value="<?php echo esc_specialchars($endDate) ?>" />
        </div>
        <div class="span1">
          <input type="submit" value="Search" />
        </div>
      </form>
    </div>
  </section>

<?php end_slot() ?>

<table class="table table-bordered sticky-enabled">
  <thead>
    <tr>
      <th>
        <?php echo __('Rank') ?>
      <th>
        <?php echo __('Identifier') ?>
      </th><th>
        <?php echo __('Page') ?>
      </th><th>
        <?php echo __('Part Of') ?>
      </th><th>
        <?php echo __('Views') ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($objects as $hit): ?>
      <?php echo include_partial('repository/pageViewsSearchResult', array('hit' => $hit, 'rank' => $rank, 'resources' => $resources, 'parents' => $parents, 'pager' => $pager)) ?>
      <?php $rank++ ?>
    <?php endforeach; ?>
  </tbody>
</table>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
