<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php include_component('repository', 'contextMenu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo $resource->title ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'updatePublicationStatus'))) ?>

    <?php echo $form->renderHiddenFields() ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Update publication status') ?></legend>

        <?php echo $form->publicationStatus->label(__('Publication status'))->renderRow() ?>
        
        <?php if ($resource->rgt - $resource->lft > 1): ?>
          <?php echo $form->updateDescendants->label(__('Update descendants'))->renderRow() ?>
        <?php endif; ?>
        
      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Update') ?>"/></li>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
