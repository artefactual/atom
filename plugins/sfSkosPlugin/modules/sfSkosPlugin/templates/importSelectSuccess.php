<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo __('SKOS import') ?>
    <span class="sub"><?php echo render_title($taxonomy) ?></span>
  </h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php if (QubitTerm::ROOT_ID == $parent->id): ?>
    <?php echo $form->renderFormTag(url_for(array($taxonomy, 'module' => 'sfSkosPlugin', 'action' => 'import'))) ?>
  <?php else: ?>
    <?php echo $form->renderFormTag(url_for(array($parent, 'module' => 'sfSkosPlugin', 'action' => 'import'))) ?>
  <?php endif; ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Select a file to import') ?></legend>

        <?php echo $form->file->renderRow() ?>

        <div class="form-item">
          <?php echo $form->taxonomy->renderLabel() ?>
          <?php echo $form->taxonomy->renderError() ?>
          <?php echo $form->taxonomy->render(array('class' => 'form-autocomplete')) ?>
          <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'taxonomy', 'action' => 'autocomplete')) ?>"/>
        </div>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Import') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
