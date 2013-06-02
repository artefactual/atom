<?php decorate_with('layout_2col') ?>

<?php slot('sidebar') ?>
  <?php echo get_component('repository', 'contextMenu') ?>
<?php end_slot() ?>

<?php slot('title') ?>
  <h1><?php echo render_title($resource) ?></h1>
<?php end_slot() ?>

<?php slot('content') ?>
  <?php echo $form->renderGlobalErrors() ?>

  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'repository', 'action' => 'editTheme'))) ?>

    <div id="content">

      <fieldset class="collapsible" id="styleArea">

        <legend><?php echo __('Style') ?></legend>

        <?php echo $form->backgroundColor
          ->label(__('Background color'))
          ->renderRow() ?>

        <?php echo $form->banner
          ->help(__('Requirements: PNG format, 256K max. size. Recommended dimensions of %1%x%2%px, it will be cropped if ImageMagick is installed.',
              array(
                '%1%' => arRepositoryThemeCropValidatedFile::BANNER_MAX_WIDTH,
                '%2%' => arRepositoryThemeCropValidatedFile::BANNER_MAX_HEIGHT)))
          ->label(__('Banner'))
          ->renderRow() ?>

        <?php echo $form->logo
          ->help(__('Requirements: PNG format, 256K max. size. Recommended dimensions of %1%x%2%px, it will be cropped if ImageMagick is installed.',
            array(
              '%1%' => arRepositoryThemeCropValidatedFile::LOGO_MAX_WIDTH,
              '%2%' => arRepositoryThemeCropValidatedFile::LOGO_MAX_HEIGHT)))
          ->label(__('Logo'))
          ->renderRow() ?>

      </fieldset>

      <fieldset class="collapsible" id="pageContentArea">

        <legend><?php echo __('Page content') ?></legend>

        <?php echo render_field($form->htmlSnippet
          ->help(__('An abstract, table of contents or description of the resource\'s scope and contents.'))
          ->label(__('Description')), $resource, array('class' => 'resizable')) ?>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'repository'), array('class' => 'c-btn', 'title' => __('Edit'))) ?></li>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>

  </form>
<?php end_slot() ?>
