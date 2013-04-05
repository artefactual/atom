<?php use_helper('Text') ?>

<div id="advanced-search">

  <?php if ('print' == $sf_request->getParameter('media')): ?>
    <div id="preview-message">
      <?php echo __('Print preview') ?>
      <?php echo link_to('Close', array_diff($sf_request->getParameterHolder()->getAll(), array('media' => 'print'))) ?>
    </div>
  <?php endif; ?>

  <h1>
    <?php echo __('Advanced search') ?>
    <div id="action-icons">
      <?php echo link_to(
        image_tag('printer-icon.png', array('alt' => __('Print'))),
          array_merge($sf_request->getParameterHolder()->getAll(), array('media' => 'print')),
          array('title' => __('Print'))) ?>
    </div>
  </h1>

  <div class="row">

    <div class="span3">

      <div class="section aside-form">

        <h4><?php echo __('Search filters') ?></h4>

        <div class="filter">
          <?php if (sfConfig::get('app_multi_repository')): ?>
            <?php echo $form->repository
              ->label(__('Repository'))
              ->renderRow() ?>
          <?php endif; ?>
        </div>

        <div class="filter">
          <?php echo $form->materialType
            ->label(__('General material designation'))
            ->renderRow() ?>
        </div>

        <div class="filter">
          <?php echo $form->mediaType
            ->label(__('Media type'))
            ->renderRow() ?>
        </div>

        <div class="filter">
          <?php echo $form->hasDigitalObject
            ->label(__('Digital object available'))
            ->renderRow() ?>
        </div>

        <div class="filter">
          <?php echo $form->levelOfDescription->renderRow() ?>
        </div>

        <div class="filter">
          <?php echo $form->copyrightStatus
            ->label(__('Copyright status'))
            ->renderRow() ?>
        </div>

      </div>

    </div>

    <div class="span9">
      <div id="content">
        <?php if ('print' != $sf_request->getParameter('media')): ?>
          <?php echo get_partial('search/advancedSearch', array('form' => $form, 'action' => 'advanced')) ?>
        <?php else: ?>
          <?php echo get_partial('search/printAdvancedSearchTerms', array('queryTerms' => $queryTerms)) ?>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <?php if (isset($error)): ?>
    <div class="error">
      <ul>
        <li><?php echo $error ?></li>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (isset($pager) && $pager->hasResults()): ?>
    <?php echo get_partial('search/searchResults', array('pager' => $pager, 'filters' => $filters)) ?>
  <?php endif; ?>

</div>
