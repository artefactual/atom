<div class="options-list"><?php echo link_to('list', array('module' => 'sfPluginAdminPlugin')) ?><a class="active">configure</a></div>

<h1><?php echo __('Configure themes') ?></h1>

<?php echo $form->renderGlobalErrors() ?>

<?php echo $form->renderFormTag(url_for(array('module' => 'sfThemePlugin')), array('style' => 'float: left;')) ?>

  <fieldset>

    <legend><?php echo __('Default page elements') ?></legend>

    <div class="description">
      <p>
        <?php echo __('Enable or disable the display of certain page elements.  Unless they have been overridden by a specific theme, these settings will be used site wide.') ?>
      </p>
    </div>

    <?php echo $form->toggleLogo
      ->label('Logo')
      ->renderRow() ?>

    <?php echo $form->toggleTitle
      ->label('Title')
      ->renderRow() ?>

    <?php echo $form->toggleDescription
      ->label('Description')
      ->renderRow() ?>

  </fieldset>

  <input class="form-submit" type="submit" value="<?php echo 'Save settings' ?>"/>

</form>
