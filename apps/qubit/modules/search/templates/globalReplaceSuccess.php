<?php use_helper('Text'); ?>

<h1><?php echo render_title($title); ?></h1>

<?php if (isset($error)) { ?>
  <div class="alert alert-danger" role="alert">
    <?php echo $error; ?>
  </div>
<?php } ?>

<?php $searchParams = ['module' => 'search', 'action' => 'globalReplace']; ?>

<form action="<?php echo url_for($searchParams); ?>" method="get" class="mb-4">
  <input type="hidden" name="topLod" value="0">

  <div class="row g-3 align-items-end">
    <div class="col-md-7">
      <label class="form-label" for="global-replace-query">
        <?php echo __('Search'); ?>
      </label>
      <input
        class="form-control"
        id="global-replace-query"
        name="sq0"
        required
        type="search"
        value="<?php echo $sf_request->getParameter('sq0'); ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label" for="global-replace-search-field">
        <?php echo __('Field'); ?>
      </label>
      <select
        class="form-select"
        id="global-replace-search-field"
        name="sf0">
        <?php foreach ($searchFields as $value => $label) { ?>
          <option
            value="<?php echo $value; ?>"
            <?php echo $value === $sf_request->getParameter('sf0', '') ? 'selected' : ''; ?>>
            <?php echo $label; ?>
          </option>
        <?php } ?>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn atom-btn-secondary w-100" type="submit">
        <?php echo __('Search'); ?>
      </button>
    </div>
  </div>
</form>

<?php if (isset($pager)) { ?>
  <p>
    <?php echo __('%1% descriptions match this search.', ['%1%' => $pager->getNbResults()]); ?>
  </p>

  <?php if (0 < $pager->getNbResults()) { ?>
    <?php $replaceParams = $searchParams + $sf_request->getGetParameters(); ?>

    <?php if (isset($form->confirm)) { ?>
      <div class="alert alert-danger" role="alert">
        <h2 class="h4"><?php echo __('This action cannot be undone!'); ?></h2>
        <p class="mb-0">
          <?php echo __('This will permanently modify %1% descriptions.', ['%1%' => $pager->getNbResults()]); ?>
        </p>
      </div>

      <dl>
        <dt><?php echo __('Replace'); ?></dt>
        <dd><?php echo $form->getValue('pattern'); ?></dd>
        <dt><?php echo __('With'); ?></dt>
        <dd><?php echo $form->getValue('replacement'); ?></dd>
        <dt><?php echo __('Field'); ?></dt>
        <dd><?php echo sfInflector::humanize(sfInflector::underscore($form->getValue('column'))); ?></dd>
      </dl>

      <form action="<?php echo url_for($replaceParams); ?>" method="post">
        <?php echo $form->renderHiddenFields(); ?>
        <input type="hidden" name="pattern" value="<?php echo $form->getValue('pattern'); ?>">
        <input type="hidden" name="replacement" value="<?php echo $form->getValue('replacement'); ?>">
        <input type="hidden" name="column" value="<?php echo $form->getValue('column'); ?>">
        <?php if ($form->getValue('caseSensitive')) { ?>
          <input type="hidden" name="caseSensitive" value="1">
        <?php } ?>
        <?php if ($form->getValue('allowRegex')) { ?>
          <input type="hidden" name="allowRegex" value="1">
        <?php } ?>

        <a class="btn atom-btn-white" href="<?php echo url_for($replaceParams); ?>">
          <?php echo __('Cancel'); ?>
        </a>
        <button class="btn btn-danger" type="submit">
          <?php echo __('Replace'); ?>
        </button>
      </form>
    <?php } else { ?>
      <form action="<?php echo url_for($replaceParams); ?>" method="post">
        <div class="row g-3">
          <div class="col-md-4">
            <?php echo render_field($form->pattern->label(__('Replace'))); ?>
          </div>
          <div class="col-md-4">
            <?php echo render_field($form->replacement->label(__('With'))); ?>
          </div>
          <div class="col-md-4">
            <?php echo render_field($form->column->label(__('Field'))); ?>
          </div>
          <div class="col-md-6">
            <?php echo render_field($form->caseSensitive->label(__('Case-sensitive'))); ?>
          </div>
          <div class="col-md-6">
            <?php echo render_field($form->allowRegex->label(__('Use regular expression syntax'))); ?>
          </div>
        </div>

        <button class="btn btn-danger" type="submit">
          <?php echo __('Review replacement'); ?>
        </button>
      </form>
    <?php } ?>
  <?php } ?>
<?php } ?>
