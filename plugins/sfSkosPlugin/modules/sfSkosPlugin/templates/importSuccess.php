<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <?php if (isset($resource) && 'QubitTerm' == $resource->getClass()) { ?>
    <div class="multiline-header">
      <h1><?php echo __('SKOS import'); ?></h1>
      <span class="sub"><?php echo render_title($parent); ?></span>
    </div>
  <?php } else { ?>
    <h1><?php echo __('SKOS import'); ?></h1>
  <?php } ?>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php if ($form->hasGlobalErrors()) { ?>
    <div class="messages error">
      <ul>
        <?php foreach ($form->getGlobalErrors() as $error) { ?>
          <li><?php echo $error->getMessage(); ?></li>
        <?php } ?>
      </ul>
    </div>
  <?php } ?>

  <?php if (QubitTerm::ROOT_ID == $parent->id) { ?>
    <?php echo $form->renderFormTag(url_for([$taxonomy, 'module' => 'sfSkosPlugin', 'action' => 'import'])); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for([$parent, 'module' => 'sfSkosPlugin', 'action' => 'import'])); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Import options'); ?></legend>

        <?php if (isset($resource)) { ?>
          <div class="form-item">
            <?php echo $form->taxonomy->renderLabel(); ?>
            <?php echo $form->taxonomy->renderError(); ?>
            <?php echo $form->taxonomy->render(); ?>
            <?php echo render_title($taxonomy); ?>
          </div>
        <?php } else { ?>
          <?php echo $form->taxonomy->renderLabel(); ?>
          <?php echo $form->taxonomy->renderError(); ?>
          <?php echo $form->taxonomy->render(); ?> 
          <input class="list" type="hidden" value="<?php echo url_for(['module' => 'taxonomy', 'action' => 'autocomplete']); ?>"/>
        <?php } ?>

      </fieldset>

      <fieldset class="collapsible">

        <legend><?php echo __('Select source'); ?></legend>

        <?php echo $form->file
            ->label(__('Select a file to import'))
            ->renderRow(); ?>

        <br /> <!-- Not ideal! -->

        <?php echo $form->url
            ->label(__('Or a remote resource'))
            ->renderRow(); ?>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Import'); ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
