<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1><?php echo __('Are you sure you want to delete %1%?', array('%1%' => render_title($resource))) ?></h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'delete')), array('method' => 'delete')) ?>

    <?php echo $form->renderHiddenFields() ?>

    <div id="content">

      <?php if (0 < $count): ?>
        <h2><?php echo __('It has %1% descendants that will also be deleted:', array('%1%' => $count)) ?></h2>
        <div class="delete-list">

          <ul>
            <?php foreach ($descendants as $item): ?>
              <li><?php echo link_to(render_title($item), array($item, 'module' => 'informationobject')) ?></li>
            <?php endforeach; ?>
          </ul>

        </div>
      <?php endif; ?>

    </div>

  </form>

<?php end_slot() ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

  <section class="actions">
    <ul>
      <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
      <li><input class="c-btn c-btn-delete" type="submit" value="<?php echo __('Delete') ?>"/></li>
    </ul>
  </section>
<?php end_slot() ?>