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
            <?php foreach ($resource->descendants as $index => $item): ?>
              <li><?php echo link_to(render_title($item), array($item, 'module' => 'informationobject')) ?></li>
              <?php if ($index + 1 == $previewSize) break; ?>
            <?php endforeach; ?>
          </ul>

          <?php if ($previewIsLimited): ?>
            <hr />
            <p>
              <?php echo __('Only %1% descriptions were shown.', array('%1%' => $previewSize)) ?>
              <?php echo link_to(__('View the full list of descendants.'), array('module' => 'informationobject', 'action' => 'browse', 'collection' => $resource->id, 'topLod' => false)) ?>
            </p>
          <?php endif; ?>

        </div>
      <?php endif; ?>

    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
        <li><input class="c-btn c-btn-delete" type="submit" value="<?php echo __('Delete') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
