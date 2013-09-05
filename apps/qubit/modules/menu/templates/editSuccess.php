<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php if (isset($sf_request->id)): ?>
      <?php echo __('Edit menu') ?>
    <?php else: ?>
      <?php echo __('Add new menu') ?>
    <?php endif; ?>
    <?php if (isset($sf_request->id)): ?>
      <span class="sub"><?php echo $menu->getName(array('sourceCulture' => true)) ?></h1>
    <?php endif; ?>
  </h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderGlobalErrors() ?>

  <?php if (isset($sf_request->id)): ?>
    <?php echo $form->renderFormTag(url_for(array($menu, 'module' => 'menu', 'action' => 'edit'))) ?>
  <?php else: ?>
    <?php echo $form->renderFormTag(url_for(array('module' => 'menu', 'action' => 'add'))) ?>
  <?php endif; ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend>
          <?php echo __('Main area') ?>
        </legend>

        <?php if ($menu->isProtected()): ?>
          <p><?php echo __('Read only') ?></p>
        <?php endif; ?>

        <div class="form-item">
          <?php echo $form->name
            ->help(__('Provide an internal menu name.  This is not visible to users.'))
            ->label(__('Name'))
            ->renderRow() ?>
        </div>

        <?php echo render_field($form['label']
          ->help(__('Provide a menu label for users.  For menu items that are not visible (i.e. are organizational only) this should be left blank.'))
          ->label(__('Label')), $menu) ?>

        <?php echo $form->parentId
          ->label('Parent')
          ->renderRow() ?>

        <?php echo $form['path']
          ->help(__('Provide a link to an external website or an internal, symfony path (module/action).'))
          ->label(__('Path'))
          ->renderRow() ?>

        <?php echo render_field($form['description']
          ->help(__('Provide a brief description of the menu and it\'s purpose.'))
          ->label(__('Description')), $menu) ?>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array('module' => 'menu', 'action' => 'list'), array('class' => 'c-btn')) ?></li>
        <?php if (!$menu->isProtected() && isset($menu->id)): ?>
          <li><?php echo link_to(__('Delete'), array($menu, 'module' => 'menu', 'action' => 'delete'), array('class' => 'c-btn c-btn-delete')) ?></li>
        <?php endif; ?>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
