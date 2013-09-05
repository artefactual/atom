<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1><?php echo __('Are you sure you want to delete %1%?', array('%1%' => render_title($resource))) ?></h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderGlobalErrors() ?>

  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'physicalobject', 'action' => 'delete')), array('method' => 'delete')) ?>

    <?php echo $form->renderHiddenFields() ?>

    <div id="content">

      <?php if (0 < count($informationObjects)): ?>
        <h2><?php echo __('Click Confirm to delete this physical storage from the system. This will also remove the physical storage location from the following records:') ?></h2>
        <div class="delete-list">
          <ul>
            <?php foreach ($informationObjects as $item): ?>
              <li><?php echo link_to(render_title($item), array($item, 'module' => 'informationobject')) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'physicalobject'), array('class' => 'c-btn')) ?></li>
        <li><input class="c-btn c-btn-delete" type="submit" value="<?php echo __('Delete') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
