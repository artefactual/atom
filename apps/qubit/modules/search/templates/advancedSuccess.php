<?php decorate_with('layout_3col') ?>

<?php slot('sidebar') ?>

  <section id="advanced-search-filters">

    <h4><?php echo __('Search filters') ?></h4>

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

  </section>

<?php end_slot() ?>

<?php slot('title') ?>
  <?php if ('print' == $sf_request->getParameter('media')): ?>
    <div id="preview-message">
      <?php echo __('Print preview') ?>
      <?php echo link_to('Close', array_diff($sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), array('media' => 'print'))) ?>
    </div>
  <?php endif; ?>
  <h1><?php echo __('Advanced search') ?></h1>
<?php end_slot() ?>

<?php slot('context-menu') ?>
  <section id="action-icons">
    <ul>
      <li>
        <a href="<?php echo url_for(array_merge($sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), array('media' => 'print'))) ?>">
          <i class="icon-print"></i>
          <?php echo __('Print') ?>
        </a>
      </li>
    </ul>
  </section>
<?php end_slot() ?>

<?php echo get_partial('search/searchFields', array('criterias' => $criterias, 'template' => $template)) ?>

<section class="actions">
  <input type="submit" class="c-btn c-btn-submit" value="<?php echo __('Search') ?>"/>
  <input type="reset" class="c-btn c-btn-delete" value="<?php echo __('Reset') ?>"/>
</section>

<?php if (isset($pager)): ?>

  <?php if ($pager->hasResults()): ?>
    <?php foreach ($pager->getResults() as $hit): ?>
      <?php $doc = $hit->getData() ?>
      <?php echo include_partial('search/searchResult', array('doc' => $doc, 'pager' => $pager)) ?>
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
