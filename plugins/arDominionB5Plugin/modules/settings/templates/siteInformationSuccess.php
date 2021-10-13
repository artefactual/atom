<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Site information'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <form action="<?php echo url_for('settings/siteInformation'); ?>" method="post">

    <?php echo $form->renderHiddenFields(); ?>

    <div class="table-responsive mb-3">
      <table class="table table-bordered mb-0">
        <thead>
          <tr>
            <th><?php echo __('Name'); ?></th>
            <th><?php echo __('Value'); ?></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <?php echo $form->siteTitle->renderLabel(
                           'Site title', ['title' => 'The name of the website for display in the header']); ?>
            </td>
            <td>
              <?php echo get_partial('settings/i18n_form_field',
                [
                    'name' => 'siteTitle',
                    'label' => null,
                    'settings' => $settings,
                    'form' => $form, ]); ?>
            </td>
          </tr>
          <tr>
            <td>
              <?php echo $form->siteDescription->renderLabel(
                           'Site description', ['title' => 'A brief site description or &quot;tagline&quot; for the header']); ?>
            </td>
            <td>
              <?php echo get_partial('settings/i18n_form_field',
                [
                    'name' => 'siteDescription',
                    'label' => null,
                    'settings' => $settings,
                    'form' => $form, ]); ?>
            </td>
          </tr>
          <tr>
            <td>
              <?php echo $form->siteBaseUrl->renderLabel(
                           'Site base URL (used in MODS and EAD exports)',
                           ['title' => 'Used to create absolute URLs, pointing to resources, in XML exports']); ?>
            </td>
            <td>
              <?php echo get_partial('settings/i18n_form_field',
                [
                    'name' => 'siteBaseUrl',
                    'label' => null,
                    'settings' => $settings,
                    'form' => $form, ]); ?>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <section class="actions">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
