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

    <div class="accordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="permissions-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#permissions-collapse" aria-expanded="true" aria-controls="permissions-collapse">
            <?php echo __('PREMIS access permissions'); ?>
          </button>
        </h2>
        <div id="permissions-collapse" class="accordion-collapse collapse show" aria-labelledby="permissions-heading">
          <div class="accordion-body">
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
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="statements-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#statements-collapse" aria-expanded="false" aria-controls="statements-collapse">
            <?php echo __('PREMIS access statements'); ?>
          </button>
        </h2>
        <div id="statements-collapse" class="accordion-collapse collapse" aria-labelledby="statements-heading">
          <div class="accordion-body">
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
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="copyright-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#copyright-collapse" aria-expanded="false" aria-controls="copyright-collapse">
            <?php echo __('Copyright statement'); ?>
          </button>
        </h2>
        <div id="copyright-collapse" class="accordion-collapse collapse" aria-labelledby="copyright-heading">
          <div class="accordion-body">
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
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="preservation-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#preservation-collapse" aria-expanded="false" aria-controls="preservation-collapse">
            <?php echo __('Preservation system access statement'); ?>
          </button>
        </h2>
        <div id="preservation-collapse" class="accordion-collapse collapse" aria-labelledby="preservation-heading">
          <div class="accordion-body">
            <?php echo $permissionsPreservationSystemAccessStatementForm->preservationSystemAccessStatementEnabled
                ->label(__('Enable access statement'))
                ->renderRow(); ?>

            <br />
            <div class="alert alert-info">
              <?php echo __('When enabled the following text will appear in the %1% metadata section to describe how a user may access the original and preservation copy of the file stored in a linked digital preservation system. The text appears in the "Permissions" field. When disabled, the "Permissions" field is not displayed.', ['%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))]); ?>
            </div>

            <?php echo render_field($permissionsPreservationSystemAccessStatementForm->preservationSystemAccessStatement
                ->label(__('Access statement')), $preservationSystemAccessStatementSetting, ['name' => 'value', 'class' => 'resizable']); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
