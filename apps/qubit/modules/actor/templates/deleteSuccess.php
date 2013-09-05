<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1><?php echo __('Are you sure you want to delete %1%?', array('%1%' => render_title($resource))) ?></h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'actor', 'action' => 'delete')), array('method' => 'delete')) ?>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'actor'), array('class' => 'c-btn')) ?></li>
        <li><input class="c-btn c-btn-delete" type="submit" value="<?php echo __('Delete') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
