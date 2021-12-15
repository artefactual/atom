<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Finding Aid settings'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <form
    id="settings-finding-aid-form"
    action="<?php echo url_for('settings/findingAid'); ?>"
    method="post"
    data-cy="settings-finding-aid-form"
  >
    <?php if (isset($error) && 'formInvalid' == $error) { ?>
    <div class="alert alert-error">
      <?php echo __('There was an error submitting the form.'); ?>
    </div>
    <?php } ?>

    <div id="content">

      <table class="table sticky-enabled" id="finding-aid-settings">
        <thead>
          <tr>
            <th><?php echo __('Name'); ?></th>
            <th><?php echo __('Value'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php echo $findingAidForm; ?>
        </tbody>
      </table>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
