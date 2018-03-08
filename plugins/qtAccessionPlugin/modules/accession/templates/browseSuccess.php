<?php decorate_with('layout_1col') ?>
<?php use_helper('Date') ?>

<?php slot('title') ?>
  <h1><?php echo __('Browse accession') ?></h1>
<?php end_slot() ?>

<?php slot('before-content') ?>

  <section class="header-options">
    <div class="row">
      <div class="span6">
        <?php echo get_component('search', 'inlineSearch', array(
          'label' => __('Search %1%', array('%1%' => strtolower(sfConfig::get('app_ui_label_accession')))))) ?>
      </div>

      <div class="pickers">
        <?php echo get_partial('default/sortPickers', array('options' => $sortOptions)) ?>
      </div>
    </div>
  </section>

<?php end_slot() ?>

<?php slot('content') ?>

  <table class="table table-bordered sticky-enabled">
    <thead>
      <tr>
        <th>
          <?php echo __('Accession number') ?>
        </th>
        <th>
          <?php echo __('Title') ?>
        </th>
        <th>
          <?php echo __('Acquisition date') ?>
        </th>
        <?php if ('lastUpdated' == $sf_request->sort): ?>
          <th>
            <?php echo __('Updated') ?>
          </th>
        <?php endif; ?>
      </tr>
    </thead><tbody>
      <?php foreach ($pager->getResults() as $hit): ?>
        <?php $doc = $hit->getData() ?>
        <tr>
          <td width="20%">
            <?php echo link_to($doc['identifier'], array('module' => 'accession', 'slug' => $doc['slug'])) ?>
          </td>
          <td>
            <?php echo link_to(render_title(get_search_i18n($doc, 'title')), array('module' => 'accession', 'slug' => $doc['slug'])) ?>
          </td>
          <td width="20%">
            <?php echo format_date($doc['date'], 'i') ?>
          </td>
          <?php if ('lastUpdated' == $sf_request->sort): ?>
            <td width="20%">
              <?php echo format_date($doc['updatedAt'], 'f') ?>
            </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<?php end_slot() ?>

<?php slot('after-content') ?>

  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

  <section class="actions">
    <ul>
      <li><?php echo link_to(__('Add new'), array('module' => 'accession', 'action' => 'add'), array('class' => 'c-btn')) ?></li>
    </ul>
  </section>

<?php end_slot() ?>
