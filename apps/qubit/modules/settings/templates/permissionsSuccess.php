<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_component('settings', 'menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Permissions') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <form action="<?php echo url_for('settings/permissions') ?>" method="post">

    <div id="content">

      <fieldset class="collapsible" id="premisAccessPermissionsArea">

        <legend><?php echo __('PREMIS access permissions') ?></legend>

        <?php echo $permissionsForm['granted_right']
          ->label(__('PREMIS act'))
          ->renderRow() ?>

        <table>
          <caption>
            <?php echo __('Permissions') ?>
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
            <?php foreach ($permissionsForm['permissions'] as $k => $sf): ?>
              <tr>
                <td class="premis-permissions-basis-value">
                  <span><?php echo $basis[$k] ?></span>
                </td>
                <td class="premis-permissions-mrt">
                  <div>
                    <ul>
                      <li class="cbx"><?php echo $sf['allow_master'] ?></li>
                      <li class="cbx"><?php echo $sf['allow_reference'] ?></li>
                      <li class="cbx"><?php echo $sf['allow_thumb'] ?></li>
                    </ul>
                  </div>
                </td>
                <td class="premis-permissions-mrt">
                  <div>
                    <ul>
                      <li class="cbx"><?php echo $sf['conditional_master'] ?></li>
                      <li class="cbx"><?php echo $sf['conditional_reference'] ?></li>
                      <li class="cbx"><?php echo $sf['conditional_thumb'] ?></li>
                    </ul>
                  </div>
                </td>
                <td class="premis-permissions-mrt">
                  <div>
                    <ul>
                      <li class="cbx"><?php echo $sf['disallow_master'] ?></li>
                      <li class="cbx"><?php echo $sf['disallow_reference'] ?></li>
                      <li class="cbx"><?php echo $sf['disallow_thumb'] ?></li>
                    </ul>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <ul class="premis-permissions-toggle">
          <li><a href="#" class="all">All</a></li>
          <li class="separator">/</li>
          <li><a href="#" class="none">None</a></li>
        </ul>

      </fieldset>

      <fieldset class="collapsible" id="copyrightStatementArea">

        <legend><?php echo __('Copyright statement') ?></legend>

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

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
        <li><?php echo link_to(__('Cancel'), array('module' => 'settings', 'action' => 'permissions'), array('class' => 'c-btn')) ?></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
