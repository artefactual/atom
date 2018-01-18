<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_component('settings', 'menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Active Directory authentication') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array('module' => 'settings', 'action' => 'ad'))) ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Active Directory authentication settings') ?></legend>

        <?php echo $form->ldapHost
          ->label(__('DC or GC URI'))
          ->renderRow() ?>

        <?php echo $form->ldapBaseDn
          ->label(__('Base DN'))
          ->renderRow() ?>
		  
      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
