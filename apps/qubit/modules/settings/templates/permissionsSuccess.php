<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Permissions'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $permissionsForm->renderGlobalErrors(); ?>
  <?php echo $permissionsAccessStatementsForm->renderGlobalErrors(); ?>
  <?php echo $permissionsCopyrightStatementForm->renderGlobalErrors(); ?>
  <?php echo $permissionsPreservationSystemAccessStatementForm->renderGlobalErrors(); ?>

  <form action="<?php echo url_for('settings/permissions'); ?>" method="post" autocomplete="off">

    <?php echo $permissionsForm->renderHiddenFields(); ?>
    <?php echo $permissionsAccessStatementsForm->renderHiddenFields(); ?>
    <?php echo $permissionsCopyrightStatementForm->renderHiddenFields(); ?>
    <?php echo $permissionsPreservationSystemAccessStatementForm->renderHiddenFields(); ?>

    <div id="content">

      <fieldset class="collapsible" id="premisAccessPermissionsArea">

        <legend><?php echo __('PREMIS access permissions'); ?></legend>

        <?php echo $permissionsForm['granted_right']
            ->label(__('PREMIS act'))
            ->renderRow(); ?>

        <table>
          <caption>
            <?php echo __('Permissions'); ?>
          </caption>
          <thead>
            <tr>
              <th>&nbsp;</th>
              <th>Allow</th>
              <th>Conditional</th>
              <th>Disallow</th>
            </tr>
            <tr>
              <th class="premis-permissions-basis">Basis</th>
              <th class="premis-permissions-mrt">
                <div>
                  <ul>
                    <li><a href="#" class="btn btn-small btn-check-col">Master</a></li>
                    <li><a href="#" class="btn btn-small btn-check-col">Reference</a></li>
                    <li><a href="#" class="btn btn-small btn-check-col">Thumb</a></li>
                  </ul>
                </div>
              </th>
              <th class="premis-permissions-mrt">
                <div>
                  <ul>
                    <li><a href="#" class="btn btn-small btn-check-col">Master</a></li>
                    <li><a href="#" class="btn btn-small btn-check-col">Reference</a></li>
                    <li><a href="#" class="btn btn-small btn-check-col">Thumb</a></li>
                  </ul>
                </div>
              </th>
              <th class="premis-permissions-mrt">
                <div>
                  <ul>
                    <li><a href="#" class="btn btn-small btn-check-col">Master</a></li>
                    <li><a href="#" class="btn btn-small btn-check-col">Reference</a></li>
                    <li><a href="#" class="btn btn-small btn-check-col">Thumb</a></li>
                  </ul>
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($permissionsForm['permissions'] as $k => $sf) { ?>
              <tr>
                <td class="premis-permissions-basis-value">
                  <span><?php echo $basis[$k]; ?></span>
                </td>
                <td class="premis-permissions-mrt">
                  <div>
                    <ul>
                      <li class="cbx"><?php echo $sf['allow_master']; ?></li>
                      <li class="cbx"><?php echo $sf['allow_reference']; ?></li>
                      <li class="cbx"><?php echo $sf['allow_thumb']; ?></li>
                    </ul>
                  </div>
                </td>
                <td class="premis-permissions-mrt">
                  <div>
                    <ul>
                      <li class="cbx"><?php echo $sf['conditional_master']; ?></li>
                      <li class="cbx"><?php echo $sf['conditional_reference']; ?></li>
                      <li class="cbx"><?php echo $sf['conditional_thumb']; ?></li>
                    </ul>
                  </div>
                </td>
                <td class="premis-permissions-mrt">
                  <div>
                    <ul>
                      <li class="cbx"><?php echo $sf['disallow_master']; ?></li>
                      <li class="cbx"><?php echo $sf['disallow_reference']; ?></li>
                      <li class="cbx"><?php echo $sf['disallow_thumb']; ?></li>
                    </ul>
                  </div>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>

        <ul class="premis-permissions-toggle">
          <li><a href="#" class="all">All</a></li>
          <li class="separator">/</li>
          <li><a href="#" class="none">None</a></li>
        </ul>

      </fieldset>

      <fieldset class="collapsible" id="premisAccessStatementsArea">

        <legend><?php echo __('PREMIS access statements'); ?></legend>

        <div class="tabbable tabs-left">
          <ul class="nav nav-tabs">
            <?php foreach ($basis as $basisSlug => $basisName) { ?>
              <li><a href="<?php echo "#tab{$basisSlug}"; ?>" data-toggle="tab"><?php echo $basisName; ?></a></li>
            <?php } ?>
          </ul>
          <div class="tab-content">
            <?php $settings = $permissionsAccessStatementsForm->getSettings(); ?>
            <?php foreach ($basis as $basisSlug => $basisName) { ?>
              <div class="tab-pane" id="<?php echo "tab{$basisSlug}"; ?>">
                <?php $name = "{$basisSlug}_disallow"; ?>
                <?php $field = $permissionsAccessStatementsForm[$name]; ?>
                <?php echo render_field($field->label(__('Disallow statement')), $settings[$name], ['name' => 'value', 'class' => 'resizable']); ?>

                <?php $name = "{$basisSlug}_conditional"; ?>
                <?php $field = $permissionsAccessStatementsForm[$name]; ?>
                <?php echo render_field($field->label(__('Conditional statement')), $settings[$name], ['name' => 'value', 'class' => 'resizable']); ?>
              </div>
            <?php } ?>
          </div>
        </div>

      </fieldset>

      <fieldset class="collapsible" id="copyrightStatementArea">

        <legend><?php echo __('Copyright statement'); ?></legend>

        <?php echo $permissionsCopyrightStatementForm->copyrightStatementEnabled
            ->label(__('Enable copyright statement'))
            ->renderRow(); ?>

        <br />
        <div class="alert alert-info">
          <?php echo __('When enabled the following text will appear whenever a user tries to download a %1% master with an associated rights statement where the Basis = copyright and the Restriction = conditional. You can style and customize the text as in a static page.', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]); ?>
        </div>

        <?php echo render_field($permissionsCopyrightStatementForm->copyrightStatement
            ->label(__('Copyright statement')), $copyrightStatementSetting, ['name' => 'value', 'class' => 'resizable']); ?>

        <input class="btn" type="submit" name="preview" value="<?php echo __('Preview'); ?>"/>

        <br />
        <br />
        <?php echo $permissionsCopyrightStatementForm->copyrightStatementApplyGlobally
            ->label(__('Apply to every %1%', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]))
            ->renderRow(); ?>

        <br />
        <div class="alert alert-info">
          <?php echo __('When enabled, the copyright pop-up will be applied to every %1%, regardless of whether there is an accompanying Rights statement.', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]); ?>
        </div>

      </fieldset>

      <fieldset class="collapsible" id="preservationSystemAccessStatementArea">

        <legend><?php echo __('Preservation system access statement'); ?></legend>

        <?php echo $permissionsPreservationSystemAccessStatementForm->preservationSystemAccessStatementEnabled
            ->label(__('Enable access statement'))
            ->renderRow(); ?>

        <br />
        <div class="alert alert-info">
          <?php echo __('When enabled the following text will appear in the %1% metadata section to describe how a user may access the original and preservation copy of the file stored in a linked digital preservation system. The text appears in the "Permissions" field. When disabled, the "Permissions" field is not displayed.', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]); ?>
        </div>

        <?php echo render_field($permissionsPreservationSystemAccessStatementForm->preservationSystemAccessStatement
            ->label(__('Access statement')), $preservationSystemAccessStatementSetting, ['name' => 'value', 'class' => 'resizable']); ?>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
        <li><?php echo link_to(__('Cancel'), ['module' => 'settings', 'action' => 'permissions'], ['class' => 'c-btn']); ?></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
