<h1><?php echo __('Import %1% (SKOS)', array('%1%' => $taxonomy->__toString())) ?></h1>

<h1 class="label"><?php echo __('Select a file to import') ?></h1>

<?php if (QubitTerm::ROOT_ID == $parent->id): ?>
  <?php echo $form->renderFormTag(url_for(array($taxonomy, 'module' => 'sfSkosPlugin', 'action' => 'import'))) ?>
<?php else: ?>
  <?php echo $form->renderFormTag(url_for(array($parent, 'module' => 'sfSkosPlugin', 'action' => 'import'))) ?>
<?php endif; ?>

  <?php echo $form->file->renderRow() ?>

  <div class="form-item">
    <?php echo $form->taxonomy->renderLabel() ?>
    <?php echo $form->taxonomy->renderError() ?>
    <?php echo $form->taxonomy->render(array('class' => 'form-autocomplete')) ?>
    <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'taxonomy', 'action' => 'autocomplete')) ?>"/>
  </div>

  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">
        <li><input class="form-submit" type="submit" value="<?php echo __('Import') ?>"/></li>
      </ul>
    </div>

  </div>

</form>
