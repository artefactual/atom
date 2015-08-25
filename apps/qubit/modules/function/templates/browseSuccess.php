<?php decorate_with('layout_1col') ?>
<?php use_helper('Date') ?>

<?php slot('title') ?>
  <div class="multiline-header">
    <?php echo image_tag('/images/icons-large/icon-functions.png', array('alt' => '')) ?>
    <h1 aria-describedby="results-label"><?php echo __('Showing %1% results', array('%1%' => $pager->getNbResults())) ?></h1>
    <span class="sub" id="results-label"><?php echo sfConfig::get('app_ui_label_function') ?></span>
  </div>
<?php end_slot() ?>

<?php slot('before-content') ?>

  <section class="header-options">
    <div class="row">
      <div class="span6">
        <?php echo get_component('search', 'inlineSearch', array(
          'label' => __('Search %1%', array('%1%' => strtolower(sfConfig::get('app_ui_label_function')))))) ?>
      </div>
      <div class="span6">
        <?php echo get_partial('default/sortPicker',
          array(
            'options' => array(
              'lastUpdated' => __('Most recent'),
              'alphabetic' => __('Alphabetic'),
              'identifier' => __('Identifier')))) ?>
      </div>
    </div>
  </section>

<?php end_slot() ?>

<?php slot('content') ?>
  <table class="table table-bordered sticky-enabled">
    <thead>
      <tr>
        <th>
          <?php echo __('Name') ?>
        </th>
        <?php if ('alphabetic' == $sf_request->sort): ?>
          <th>
            <?php echo __('Type') ?>
          </th>
        <?php else: ?>
          <th>
            <?php echo __('Updated') ?>
          </th>
        <?php endif; ?>
      </tr>
    </thead><tbody>

      <?php foreach ($pager->getResults() as $item): ?>
        <tr>
          <td>
            <?php echo link_to(render_title($item), array($item, 'module' => 'function')) ?>
          </td>
          <?php if ('alphabetic' == $sf_request->sort): ?>
            <td>
              <?php echo $item->type ?>
            </td>
          <?php else: ?>
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

  <?php if ($sf_user->hasCredential(array('contributor', 'editor', 'administrator'), false)): ?>
    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Add new'), array('module' => 'function', 'action' => 'add'), array('class' => 'c-btn')) ?></li>
      </ul>
    </section>
  <?php endif; ?>

<?php end_slot() ?>
