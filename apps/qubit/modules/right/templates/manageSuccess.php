<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo render_title($resource) ?>
    <span class="sub">Manage Rights Inheritance</span>
  </h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderGlobalErrors() ?>

  <form method="post">
    <div id="content">
      <fieldset class="collapsible">
        <legend><?php echo __('Inheritance options') ?></legend>

        <div class="well">
        <?php echo $form->all_or_digital_only
            ->label('All descendants or just digital objects')
            ->help(__('Set if you want the rights applied to digital descendants only'))
            ->renderRow() ?>
        </div>

        <div class="well">
        <?php echo $form->overwrite_or_combine
            ->help(__('Set if you want to combine the current set of rights with any existing rights, or remove the existing rights and apply these new rights'))
            ->label('Overwrite or combine rights')
            ->renderRow() ?>
        </div>

      </fieldset>
    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Apply') ?>"/></li>
      </ul>
    </section>
  </form>

<?php end_slot() ?>