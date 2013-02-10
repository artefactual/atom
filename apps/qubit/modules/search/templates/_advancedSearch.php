<?php echo $form->renderFormTag(url_for(array('module' => 'search', 'action' => $action)), array('name' => 'form', 'method' => 'get')) ?>

<?php if (isset($form->confirm)): ?>

  <?php echo $form->renderHiddenFields() ?>
  <div style="display: none;">

<?php endif; ?>

<table class="multiRow" style="white-space: nowrap; min-width: 660px;">
  <tbody>
    <?php echo get_partial('search/searchFields') ?>
  </tbody>
</table>

<fieldset class="collapsible <?php echo ($form->getValue('repository') . $form->getValue('media') . $form->getValue('hasDigitalObject') . $form->getValue('levelOfDescription') . $form->getValue('startDate') . $form->getValue('endDate') ? '' : 'collapsed') ?>" id="filterLimit">

  <legend><?php echo __('Filter/Limit') ?></legend>

  <div class="form-item">
    <label><?php echo __('Date range search') ?></label>
    <table>
      <tr>
        <td>
          <?php echo $form->startDate
            ->label(__('Start date'))
            ->renderRow() ?>
        </td>
        <td>
          <?php echo $form->endDate
            ->label(__('End date'))
            ->renderRow() ?>
        </td>
      </tr>
      <tr>
        <td colspan="2"><?php echo __('Date format required: YYYYMMDD') ?></td>
      </tr>
    </table>
  </div>

  <?php if (sfConfig::get('app_multi_repository')): ?>

    <?php echo $form->repository
      ->label(__('Repository'))
      ->renderRow() ?>

  <?php endif; ?>

  <?php echo $form->materialType
    ->label(__('General material designation'))
    ->renderRow() ?>

  <?php echo $form->mediaType
    ->label(__('Media type'))
    ->renderRow() ?>

  <?php echo $form->hasDigitalObject
    ->label(__('Digital object available'))
    ->renderRow() ?>

  <?php echo $form->levelOfDescription->renderRow() ?>

  <?php echo $form->copyrightStatus
    ->label(__('Copyright status'))
    ->renderRow() ?>
</fieldset>

<?php if (isset($form->pager) && 'globalReplace' == $action): ?>

  <div class="form-item form-item-identifier">
    <table>
      <tbody>
        <tr>
          <td>
            <?php echo __('Replace') ?>:
          </td><td style="padding: 0px;">
            <?php echo $form->pattern->render() ?>
          </td><td>
            <?php echo __('With') ?>:
          </td><td style="padding: 0px;">
            <?php echo $form->replacement->render() ?>
          </td><td style="white-space: nowrap;"><?php echo __('in') ?>:
            <?php echo $form->column->render() ?>
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <?php echo __('Case-sensitive') ?>
            <?php echo $form->caseSensitive->render() ?>
          </td>
          <td colspan="3">
            <?php echo __('Use regular expression syntax') ?>
            <?php echo $form->allowRegex->render() ?>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

<?php endif; ?>

<?php if (isset($form->confirm)): ?>
  </div>
<?php endif; ?>

<div class="actions">

  <h2 class="element-invisible"><?php echo __('Actions') ?></h2>
  <div class="content">
    <ul class="clearfix links">

      <?php if (isset($form->confirm)): ?>

        <li><a href="#" title="<?php echo __('Cancel') ?>" onclick="document.form.submit();"><?php echo __('Cancel') ?></a></li>
        <li><input class="danger form-submit" type="submit" value="<?php echo __('Replace') ?>" onclick="document.form.method = 'post';"/></li>

      <?php else: ?>
        <input type="submit" name="Submit" class="form-submit" value="<?php echo __('Search') ?>" />

        <?php if (isset($form->pager) && 'globalReplace' == $action): ?>
          <li><a class="delete" href="#" title="<?php echo __('Replace') ?>" onclick="document.form.method = 'post'; document.form.submit();"><?php echo __('Replace') ?></a></li>
        <?php endif; ?>

      <?php endif; ?>

    </ul>
  </div>

</div>

</form>
