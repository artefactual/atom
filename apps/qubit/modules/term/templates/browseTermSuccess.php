<?php decorate_with('layout_2col') ?>
<?php use_helper('Text') ?>

<?php slot('title') ?>

  <div class="hidden-phone">
    <h1>
      <?php if (isset($icon)): ?>
        <?php echo image_tag('/images/icons-large/icon-'.$icon.'.png', array('width' => '42', 'height' => '42', 'alt' => '')) ?>
      <?php endif; ?>
      <?php echo __('Browse %1% %2%', array(
        '%1%' => render_title($resource->taxonomy),
        '%2%' => render_title($resource))) ?>
    </h1>
  </div>

  <?php if ($sf_user->isAuthenticated() ): ?>
    <div class="manage-button hidden-phone">
      <?php echo link_to(__('Manage %1%', array('%1%' => strtolower(sfConfig::get('app_ui_label_term')))), array($resource, 'module' => 'term'), array('class' => 'btn btn-small')) ?>
    </div>
  <?php endif; ?>

<?php end_slot() ?>

<?php slot('sidebar') ?>

  <div class="sidebar-lowering-sort">

    <?php echo get_component('term', 'treeView', array('browser' => false)) ?>

    <section id="facets">

      <div class="visible-phone facets-header">
        <a class="x-btn btn-wide">
          <i class="fa fa-filter"></i>
          <?php echo __('Filters') ?>
        </a>
      </div>

      <div class="content">

        <?php echo get_partial('search/facetLanguage', array(
          'target' => '#facet-languages',
          'label' => __('Language'),
          'facet' => 'languages',
          'pager' => $pager,
          'filters' => $search->filters)) ?>

        <?php if (QubitTaxonomy::PLACE_ID != $resource->taxonomyId): ?>
          <?php echo get_partial('search/facet', array(
            'target' => '#facet-places',
            'label' => sfConfig::get('app_ui_label_place'),
            'facet' => 'places',
            'pager' => $pager,
            'filters' => $search->filters)) ?>
          <?php endif; ?>

        <?php if (QubitTaxonomy::SUBJECT_ID != $resource->taxonomyId): ?>
          <?php echo get_partial('search/facet', array(
            'target' => '#facet-subjects',
            'label' => sfConfig::get('app_ui_label_subject'),
            'facet' => 'subjects',
            'pager' => $pager,
            'filters' => $search->filters)) ?>
        <?php endif; ?>

        <?php if (QubitTaxonomy::GENRE_ID != $resource->taxonomyId): ?>
          <?php echo get_partial('search/facet', array(
            'target' => '#facet-genres',
            'label' => sfConfig::get('app_ui_label_genre'),
            'facet' => 'genres',
            'pager' => $pager,
            'filters' => $search->filters)) ?>
        <?php endif; ?>

      </div>

    </section>

  </div>

<?php end_slot() ?>

<?php echo get_partial('search/searchResults', array('pager' => $pager, 'culture' => $selectedCulture)) ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
