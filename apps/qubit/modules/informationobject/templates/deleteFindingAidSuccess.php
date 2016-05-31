<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1><?php echo __('Are you sure you want to delete the finding aid of %1%?', array('%1%' => $resource->title)) ?></h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'deleteFindingAid')), array('method' => 'delete')) ?>

    <div id="content">

      <h2><?php echo __('The following file will be deleted from the file system:') ?></h2>

      <div class="delete-list">
        <ul>
          <li><a href="<?php echo public_path($path) ?>" target="_blank"><?php echo $filename ?></a></li>
          <li><?php echo __('If the finding aid is an uploaded PDF, the transcript will be deleted too.') ?></li>
        </ul>
      </div>

    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
        <li><input class="c-btn c-btn-delete" type="submit" value="<?php echo __('Delete') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
