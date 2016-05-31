<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php include_component('repository', 'contextMenu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo $resource->title ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderGlobalErrors() ?>

  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'uploadFindingAid'))) ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Upload finding aid') ?></legend>

        <?php if (isset($errorMessage)): ?>
          <div class="messages error">
            <ul>
              <li><?php echo $errorMessage ?></li>
            </ul>
          </div>
        <?php endif; ?>

        <?php echo $form->file->label(__('%1% file', array('%1%' => strtoupper($format))))->renderRow() ?>
        
      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Upload') ?>"/></li>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
