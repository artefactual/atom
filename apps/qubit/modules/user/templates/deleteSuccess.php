<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1><?php echo __('Are you sure you want to delete %1%?', array('%1%' => render_title($resource->username))) ?></h1>
<?php end_slot() ?>

<?php slot('content') ?>
  <?php if ($noteCount = $resource->getNotes()->count()): ?>
    <div id="content"><h2>
      <?php echo __('This user has %1% note(s) in the system. These notes will not be deleted, but their association with this user will be removed.',
                    array('%1%' => $noteCount)) ?>
    </h2></div>
  <?php endif; ?>

  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'user', 'action' => 'delete')), array('method' => 'delete')) ?>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'user'), array('class' => 'c-btn')) ?></li>
        <li><input class="c-btn c-btn-delete" type="submit" value="<?php echo __('Delete') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
