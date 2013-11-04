<h1><?php echo $title ?></h1>

<?php if (isset($resource)): ?>
  <h1 class="label"><?php echo render_title($resource) ?></h1>
  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'object', 'action' => 'import')), array('enctype' => 'multipart/form-data')) ?>
<?php else: ?>
  <?php echo $form->renderFormTag(url_for(array('module' => 'object', 'action' => 'import')), array('enctype' => 'multipart/form-data')) ?>
<?php endif; ?>

<?php if ($sf_user->hasFlash('error')): ?>
  <div class="messages error">
    <h3><?php echo __('Error encountered') ?></h3>
    <div><?php echo $sf_user->getFlash('error') ?></div>
  </div>
<?php endif; ?>

  <?php echo $form->renderHiddenFields() ?>

  <fieldset>

    <legend><?php echo $title ?></legend>

    <div class="form-item">
      <label><?php echo __('Select a file to import') ?></label>
      <input name="file" type="file"/>
    </div>

    <input type="hidden" name="importType" value="<?php echo esc_entities($type) ?>"/>

    <?php if ('csv' == $type): ?>
      <div class="form-item">
        <label><?php echo __('Type') ?></label>
        <select name="csvObjectType">
          <option value="informationObject"><?php echo sfConfig::get('app_ui_label_informationobject') ?></option>
          <option value="accession"><?php echo sfConfig::get('app_ui_label_accession', __('Accession')) ?></option>
          <option value="authorityRecord"><?php echo sfConfig::get('app_ui_label_actor') ?></option>
          <option value="event"><?php echo sfConfig::get('app_ui_label_event', __('Event')) ?></option>
        </select>
      </div>
    <?php endif; ?>

    <div class="form-item">
      <label>
        <input name="noindex" type="checkbox"/>
        <?php echo __('Do not index imported items') ?>
      </label>
    </div>

  </fieldset>

  <?php if ('csv' != $type): ?>
    <div>
      <p><?php echo __('If you are importing a SKOS file to a taxonomy other than subjects, please go to the %1%', array('%1%' => link_to(__('SKOS import page'), array('module' => 'sfSkosPlugin', 'action' => 'import')))) ?></p>
    </div>
  <?php endif; ?>

  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">
        <li><input class="form-submit" type="submit" value="<?php echo __('Import') ?>"/></li>
      </ul>
    </div>

  </div>

</form>
