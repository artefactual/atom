<div class="row">

  <div class="span9 offset3">

    <div id="main-column">

      <h1><?php echo __('Are you sure you want to delete %1%?', array('%1%' => render_title($resource))) ?></h1>

      <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'repository', 'action' => 'delete')), array('method' => 'delete')) ?>

        <section class="actions">
          <ul>
            <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'repository'), array('class' => 'c-btn')) ?></li>
            <li><input class="c-btn c-btn-delete" type="submit" value="<?php echo __('Delete') ?>"/></li>
          </ul>
        </section>

      </form>

    </div>

  </div>

</div>
