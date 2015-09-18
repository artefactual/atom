<?php decorate_with('layout_3col') ?>

<?php slot('sidebar') ?>

  <section id="advanced-search-filters">

    <h2><?php echo __('Search filters') ?></h2>

    <div class="filter">
      <?php if (sfConfig::get('app_multi_repository')): ?>
        <?php echo $form->r
          ->label(__('Repository'))
          ->renderRow() ?>
      <?php endif; ?>
    </div>
    <div class="filter">
      <?php echo $form->f
        ->label(__('Top-level descriptions'))
        ->renderLabel() ?>
      <?php echo $form->f->render(array('class' => 'form-autocomplete')) ?>
      <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'informationobject', 'action' => 'autocomplete', 'parent' => QubitInformationObject::ROOT_ID, 'filterDrafts' => true)) ?>"/>
    </div>
    <div class="filter">
      <?php echo $form->m
        ->label(__('General material designation'))
        ->renderRow() ?>
    </div>
    <div class="filter">
      <?php echo $form->t
        ->label(__('Media type'))
        ->renderRow() ?>
    </div>
    <div class="filter">
      <?php echo $form->h
        ->label(__('Digital object available'))
        ->renderRow() ?>
    </div>
    <div class="filter">
      <?php echo $form->l
        ->label(__('Level of description'))
        ->renderRow() ?>
    </div>
    <div class="filter">
      <?php echo $form->c
        ->label(__('Copyright status'))
        ->renderRow() ?>
    </div>

    <h2><?php echo __('Date range') ?></h2>

    <div class="filter">
      <?php echo $form->sd
        ->label(__('Start'))
        ->renderRow() ?>
    </div>
    <div class="filter">
      <?php echo $form->ed
        ->label(__('End'))
        ->renderRow() ?>
    </div>

  </section>

<?php end_slot() ?>

<?php slot('title') ?>
  <?php echo get_partial('default/printPreviewBar') ?>

  <h1><?php echo __('Advanced search') ?></h1>
<?php end_slot() ?>

<?php slot('context-menu') ?>
  <section id="action-icons">
    <ul>
      <li>
        <?php echo get_partial('default/printPreviewButton') ?>
      </li>
      <?php if (isset($pager) && $pager->hasResults() && $sf_user->isAuthenticated()): ?>
      <li>
        <a href="<?php echo url_for(array_merge($sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), array('module' => 'search', 'action' => 'exportCsv'))) ?>">
           <i class="icon-upload-alt"></i>
           <?php echo __('Export CSV') ?>
        </a>
      </li>
      <?php endif; ?>
    </ul>
  </section>
<?php end_slot() ?>

<?php if ($sf_user->hasFlash('notice')): ?>
  <div class="messages">
    <div><?php echo $sf_user->getFlash('notice', ESC_RAW) ?></div>
  </div>
<?php endif; ?>

<?php echo get_partial('search/searchFields', array('criteria' => $criteria, 'template' => $template)) ?>

<section class="actions">
  <input type="submit" class="c-btn c-btn-submit" value="<?php echo __('Search') ?>"/>
  <input type="button" class="reset c-btn c-btn-delete" value="<?php echo __('Reset') ?>"/>
</section>

<?php if (isset($pager)): ?>

  <?php if ($pager->hasResults()): ?>
    <?php foreach ($pager->getResults() as $hit): ?>
      <?php echo get_partial('search/searchResult', array('hit' => $hit, 'culture' => $sf_context->user->getCulture())) ?>
    <?php endforeach; ?>
  <?php else: ?>
    <section id="no-search-results">
      <i class="icon-search"></i>
      <p class="no-results-found"><?php echo __('No results found.') ?></p>
    </section>
  <?php endif; ?>

  <?php slot('after-content') ?>
    <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
  <?php end_slot() ?>

<?php endif; ?>

<?php slot('pre') ?>
  <?php echo $form->renderFormTag(url_for(array('module' => 'search', 'action' => 'advanced')), array('name' => 'form', 'method' => 'get')) ?>
    <?php echo $form->renderHiddenFields() ?>
<?php end_slot() ?>

<?php slot('post') ?>
  </form>
<?php end_slot() ?>
