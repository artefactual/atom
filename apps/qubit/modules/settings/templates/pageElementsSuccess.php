<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_component('settings', 'menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Default page elements') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <form action="<?php echo url_for('settings/pageElements') ?>" method="post">

    <?php echo $form->renderGlobalErrors() ?>
    <p><?php echo __('Enable or disable the display of certain page elements. Unless they have been overridden by a specific theme, these settings will be used site wide.') ?></p>

    <div id="content">

      <table class="table sticky-enabled">
        <thead>
          <tr>
            <th><?php echo __('Name')?></th>
            <th><?php echo __('Value')?></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><?php echo $form->toggleLogo->label('Logo')->renderLabel() ?></td>
            <td><?php echo $form->toggleLogo ?></td>
          </tr>
          <tr>
            <td><?php echo $form->toggleTitle->label('Title')->renderLabel() ?></td>
            <td><?php echo $form->toggleTitle ?></td>
          </tr>
          <tr>
            <td><?php echo $form->toggleDescription->label('Description')->renderLabel() ?></td>
            <td><?php echo $form->toggleDescription ?></td>
          </tr>
          <tr>
            <td><?php echo $form->toggleLanguageMenu->label('Language menu')->renderLabel() ?></td>
            <td><?php echo $form->toggleLanguageMenu ?></td>
          </tr>
          <tr>
            <td><?php echo $form->toggleIoSlider->label('Digital object carousel')->renderLabel() ?></td>
            <td><?php echo $form->toggleIoSlider ?></td>
          </tr>
          <tr>
            <td>
              <?php echo $form->toggleDigitalObjectMap->label('Digital object map')->renderLabel() ?>
              <?php if (!$googleMapsApiKeySet): ?>
                <div class="description">
                  <?php echo __('This feature will not work until a Google Maps API key is specified on the %1%global%2% settings page.', array('%1%' => '<a href="'. url_for('settings/global'). '">', '%2%' => '</a>')) ?>
                </div>
              <?php endif; ?>
            </td>
            <td><?php echo $form->toggleDigitalObjectMap ?></td>
          </tr>
          <tr>
            <td><?php echo $form->toggleCopyrightFilter->label('Copyright status filter')->renderLabel() ?></td>
            <td><?php echo $form->toggleCopyrightFilter ?></td>
          </tr>
          <tr>
            <td><?php echo $form->toggleMaterialFilter->label('General material designation filter')->renderLabel() ?></td>
            <td><?php echo $form->toggleMaterialFilter ?></td>
          </tr>
        </tbody>
      </table>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
