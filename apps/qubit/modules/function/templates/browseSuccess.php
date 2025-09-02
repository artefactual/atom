<?php decorate_with('layout_1col'); ?>
<?php use_helper('Date'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex align-items-center mb-3">
    <i class="fas fa-3x fa-tools me-3" aria-hidden="true"></i>
    <div class="d-flex flex-column">
      <h1 class="mb-0" aria-describedby="heading-label">
        <?php echo __('Showing %1% results', ['%1%' => $pager->getNbResults()]); ?>
      </h1>
      <span class="small" id="heading-label">
        <?php echo sfConfig::get('app_ui_label_function'); ?>
      </span>
    </div>
  </div>
<?php end_slot(); ?>

<?php slot('before-content'); ?>
  <div class="d-flex flex-wrap gap-2 mb-3">
    <?php echo get_component('search', 'inlineSearch', [
        'label' => __('Search %1%', ['%1%' => strtolower(sfConfig::get('app_ui_label_function'))]),
        'landmarkLabel' => __(sfConfig::get('app_ui_label_function')),
    ]); ?>

    <div class="d-flex flex-wrap gap-2 ms-auto">
      <?php echo get_partial('default/sortPickers', ['options' => [
          'lastUpdated' => __('Date modified'),
          'alphabetic' => __('Name'),
          'identifier' => __('Identifier'),
      ]]); ?>
    </div>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>
  <div class="table-responsive mb-3">
    <table class="table table-bordered mb-0">
      <thead>
        <tr>
          <th>
            <?php echo __('Name'); ?>
          </th>
          <?php if ('alphabetic' == $sf_request->sort) { ?>
            <th>
              <?php echo __('Type'); ?>
            </th>
          <?php } else { ?>
            <th>
              <?php echo __('Updated'); ?>
            </th>
          <?php } ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pager->getResults() as $item) { ?>
          <tr>
            <td>
              <?php echo link_to(render_title($item), [$item, 'module' => 'function']); ?>
            </td>
            <?php if ('alphabetic' == $sf_request->sort) { ?>
              <td>
                <?php echo $item->type; ?>
              </td>
            <?php } else { ?>
              <td>
                <?php echo format_date($item->updatedAt, 'f'); ?>
              </td>
            <?php } ?>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
<?php end_slot(); ?>

<?php slot('after-content'); ?>

  <?php echo get_partial('default/pager', ['pager' => $pager]); ?>

  <?php if ($sf_user->hasCredential(['contributor', 'editor', 'administrator'], false)) { ?>
    <section class="actions mb-3">
      <?php echo link_to(__('Add new'), ['module' => 'function', 'action' => 'add'], ['class' => 'btn atom-btn-outline-light']); ?>
    </section>
  <?php } ?>

<?php end_slot(); ?>
