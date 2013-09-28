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

        <div class="form-item form-item-banner">
          <?php echo $form->banner->renderLabel() ?>
          <div>
            <?php echo $form->banner->render() ?>
            <?php echo $form->banner->renderError() ?>
            <?php echo $form->getWidget('banner')->getHelp() ?>
          </div>
        </div>

        <div class="form-item form-item-logo">
          <?php echo $form->logo->renderLabel() ?>
          <div>
            <?php echo $form->logo->render() ?>
            <?php echo $form->logo->renderError() ?>
            <?php echo $form->getWidget('logo')->getHelp() ?>
          </div>
        </div>

      </fieldset>

      <fieldset class="collapsible" id="pageContentArea">

        <legend><?php echo __('Page content') ?></legend>

        <?php echo render_field($form->htmlSnippet
          ->label(__('Description'))
          ->help(__('Content in this area will appear below an uploaded banner and above the institution\'s description areas. It can be used to offer a summary of the institution\'s mandate, include a tag line or important information, etc. HTML and inline CSS can be used to style the contents.')), $resource, array('class' => 'resizable')) ?>

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
