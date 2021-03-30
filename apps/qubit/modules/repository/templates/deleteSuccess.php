<div class="row">

  <div class="span9 offset3">

    <div id="main-column">

      <h1><?php echo __('Are you sure you want to delete %1%?', ['%1%' => render_title($resource)]); ?></h1>

      <?php echo $form->renderGlobalErrors(); ?>

      <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'repository', 'action' => 'delete']), ['method' => 'delete']); ?>

        <?php echo $form->renderHiddenFields(); ?>

        <section class="actions">
          <ul>
            <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'repository'], ['class' => 'c-btn']); ?></li>
            <li><input class="c-btn c-btn-delete" type="submit" value="<?php echo __('Delete'); ?>"/></li>
          </ul>
        </section>

      </form>

    </div>

  </div>

</div>
