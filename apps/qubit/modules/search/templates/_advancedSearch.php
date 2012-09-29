<?php echo $form->renderFormTag(url_for(array('module' => 'search', 'action' => $action)), array('name' => 'form', 'method' => 'get')) ?>

  <?php echo $form->renderHiddenFields() ?>

  <?php echo get_partial('search/searchFields') ?>

  <div class="actions">
    <button type="submit" class="gray btn-large"><?php echo __('Search') ?></button>
  </div>

</form>
