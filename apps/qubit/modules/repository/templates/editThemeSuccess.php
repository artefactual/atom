<?php echo $form->renderGlobalErrors() ?>

<?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'repository', 'action' => 'editTheme'))) ?>

<div class="row">

  <div class="span12" id="main-column">

    <h1><?php echo render_title($resource) ?></h1>

    <ul class="breadcrumb">
      <li><?php echo link_to(__('Institutions'), array('module' => 'repository', 'action' => 'browse')) ?></li>
      <li><?php echo link_to(render_title($resource), array($resource, 'module' => 'repository')) ?></li>
      <li><span><?php echo __('Edit theme') ?></span></li>
    </ul>

    <div class="row">

      <div class="span9" id="content">

        <fieldset class="collapsible" id="styleArea">

          <legend><?php echo __('Style') ?></legend>

          <div class="control-group">
            <label class="control-label" for="html"><?php echo __('Background color') ?></label>
            <div class="controls">
              <?php echo $form->backgroundColor ?>
            </div>
          </div>

          <div class="control-group">
            <label class="control-label" for="html"><?php echo __('Banner') ?></label>
            <div class="controls">
              <?php echo $form->banner ?>
            </div>
          </div>

          <div class="control-group">
            <label class="control-label" for="html"><?php echo __('Logo') ?></label>
            <div class="controls">
              <?php echo $form->logo ?>
            </div>
          </div>

        </fieldset>

        <fieldset class="collapsible" id="pageContentArea">

          <legend><?php echo __('Page content') ?></legend>

          <div class="control-group">
            <label class="control-label" for="html"><?php echo __('HTML snippet') ?></label>
            <div class="controls">
              <?php echo $form->htmlSnippet ?>
            </div>
          </div>

        </fieldset>

      </div>

      <div class="span3" id="right-column">

      </div>

    </div>

  </div>

  <div class="actions section">
    <div class="content">
      <br />
      <ul class="clearfix links">
        <li><input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'repository'), array('class' => 'btn', 'title' => __('Edit'))) ?></li>
      </ul>
    </div>
  </div>

</div>
