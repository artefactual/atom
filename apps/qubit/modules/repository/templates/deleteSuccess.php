<div class="row">

  <div class="span9 offset3">

    <div id="main-column">

      <h1><?php echo __('Are you sure you want to delete %1%?', ['%1%' => render_title($resource)]); ?></h1>

      <?php echo $form->renderGlobalErrors(); ?>

      <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'repository', 'action' => 'delete']), ['method' => 'delete']); ?>

        <?php echo $form->renderHiddenFields(); ?>

        <ul class="actions mb-3 nav gap-2">
          <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'repository'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
          <li><input class="btn atom-btn-outline-danger" type="submit" value="<?php echo __('Delete'); ?>"></li>
        </ul>

      </form>

    </div>

  </div>

</div>
