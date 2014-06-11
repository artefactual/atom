<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <?php if (isset($resource)): ?>
    <h1 class="multiline">
      <?php echo $title ?>
      <span class="sub"><?php echo render_title($resource) ?></span>
    </h1>
  <?php else: ?>
    <h1><?php echo $title ?></h1>
  <?php endif; ?>
<?php end_slot() ?>

<?php slot('content') ?>
  <?php if ($sf_user->hasFlash('error')): ?>
    <div class="messages error">
      <h3><?php echo __('Error encountered') ?></h3>
      <div><?php echo $sf_user->getFlash('error') ?></div>
    </div>
  <?php endif; ?>

  <?php if (isset($resource)): ?>
    <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'object', 'action' => 'import')), array('enctype' => 'multipart/form-data')) ?>
  <?php else: ?>
    <?php echo $form->renderFormTag(url_for(array('module' => 'object', 'action' => 'import')), array('enctype' => 'multipart/form-data')) ?>
  <?php endif; ?>

    <?php echo $form->renderHiddenFields() ?>

    <section id="content">

      <fieldset class="collapsible">

        <legend><?php echo $title ?></legend>

        <div class="form-item">
          <label><?php echo __('Select a file to import') ?></label>
          <input name="file" type="file"/>
        </div>

        <input type="hidden" name="importType" value="<?php echo esc_entities($type) ?>"/>

        <?php if ('csv' != $type): ?>
          <div>
            <p><?php echo __('If you are importing a SKOS file to a taxonomy other than subjects, please go to the %1%', array('%1%' => link_to(__('SKOS import page'), array('module' => 'sfSkosPlugin', 'action' => 'import')))) ?></p>
          </div>
        <?php endif; ?>

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

    </section>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Import') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
