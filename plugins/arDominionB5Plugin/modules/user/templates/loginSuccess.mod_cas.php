<?php decorate_with('layout_1col'); ?>
<?php use_helper('Javascript'); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'cas', 'action' => 'login'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <ul class="actions mb-3 nav gap-2">
      <button type="submit" class="btn atom-btn-outline-success"><?php echo __('Log in with CAS'); ?></button>
    </ul>

  </form>

<?php end_slot(); ?>
