<div class="row">

  <div class="span9 offset3">

    <div id="main-column">

      <h1><?php echo __('Are you sure you want to delete %1%?', array('%1%' => render_title($resource))) ?></h1>

      <?php if (0 < count($resource->descendants)): ?>
        <h2><?php echo __('It has %1% descendants that will also be deleted:', array('%1%' => count($resource->descendants))) ?></h2>
        <ul>
          <?php foreach ($resource->descendants as $item): ?>
            <li><?php echo link_to(render_title($item), array($item, 'module' => 'informationobject')) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

      <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'delete')), array('method' => 'delete')) ?>

        <section class="actions">
          <ul>
            <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
            <li><input class="c-btn c-btn-delete" type="submit" value="<?php echo __('Delete') ?>"/></li>
          </ul>
        </section>

      </form>

    </div>

</div>
