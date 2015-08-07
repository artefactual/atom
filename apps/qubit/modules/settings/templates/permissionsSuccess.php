<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_component('settings', 'menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Permissions') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <form action="<?php echo url_for('settings/permissions') ?>" method="post">

    <h3><?php echo __('Administrate PREMIS access permissions') ?></h3>

    <?php echo $permissionsForm['granted_right']->render() ?>

    <div class="well well-large" style="background-color: #fff">

      <p><?php echo __('Allow') ?></p>

      <div class="well well-small">
        <div>
          <?php echo $permissionsForm['permissions']['allow_master']->renderLabelName() ?>
          <?php echo $permissionsForm['permissions']['allow_master']->render(array('style' => 'display: inline; width: auto;')) ?>
        </div>
        <div>
          <?php echo $permissionsForm['permissions']['allow_reference']->renderLabelName() ?>
          <?php echo $permissionsForm['permissions']['allow_reference']->render(array('style' => 'display: inline; width: auto;')) ?>
        </div>
        <div>
          <?php echo $permissionsForm['permissions']['allow_thumb']->renderLabelName() ?>
          <?php echo $permissionsForm['permissions']['allow_thumb']->render(array('style' => 'display: inline; width: auto;')) ?>
        </div>
      </div>

      <p><?php echo __('Conditional') ?></p>

      <div class="well well-small">
        <div>
          <?php echo $permissionsForm['permissions']['conditional_master']->renderLabelName() ?>
          <?php echo $permissionsForm['permissions']['conditional_master']->render(array('style' => 'display: inline; width: auto;')) ?>
        </div>
        <div>
          <?php echo $permissionsForm['permissions']['conditional_reference']->renderLabelName() ?>
          <?php echo $permissionsForm['permissions']['conditional_reference']->render(array('style' => 'display: inline; width: auto;')) ?>
        </div>
        <div>
          <?php echo $permissionsForm['permissions']['conditional_thumb']->renderLabelName() ?>
          <?php echo $permissionsForm['permissions']['conditional_thumb']->render(array('style' => 'display: inline; width: auto;')) ?>
        </div>
      </div>

      <p><?php echo __('Disallow') ?></p>

      <div class="well well-small">
        <div>
          <?php echo $permissionsForm['permissions']['disallow_master']->renderLabelName() ?>
          <?php echo $permissionsForm['permissions']['disallow_master']->render(array('style' => 'display: inline; width: auto;')) ?>
        </div>
        <div>
          <?php echo $permissionsForm['permissions']['disallow_reference']->renderLabelName() ?>
          <?php echo $permissionsForm['permissions']['disallow_reference']->render(array('style' => 'display: inline; width: auto;')) ?>
        </div>
        <div>
          <?php echo $permissionsForm['permissions']['disallow_thumb']->renderLabelName() ?>
          <?php echo $permissionsForm['permissions']['disallow_thumb']->render(array('style' => 'display: inline; width: auto;')) ?>
        </div>
      </div>
    </div>

    <h3><?php echo __('Copyright statement') ?></h3>

    <div class="well well-large" style="background-color: #fff">

      <?php echo $permissionsCopyrightStatementForm->copyrightStatementEnabled
        ->label(__('Enable copyright statement'))
        ->renderRow() ?>

      <br />
      <div class="alert alert-info">
        <?php echo __('When enabled the following text will appear whenever a user tries to download a digital object master with an associated rights statement where the Basis = copyright and the Restriction = conditional. You can style and customize the text as in a static page.') ?>
      </div>

      <?php echo $permissionsCopyrightStatementForm->copyrightStatement
        ->label(__('Copyright statement'))
        ->renderRow() ?>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
