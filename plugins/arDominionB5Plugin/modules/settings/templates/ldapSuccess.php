<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('LDAP authentication'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'settings', 'action' => 'ldap'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="ldap-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ldap-collapse" aria-expanded="true" aria-controls="ldap-collapse">
            <?php echo __('LDAP authentication settings'); ?>
          </button>
        </h2>
        <div id="ldap-collapse" class="accordion-collapse collapse show" aria-labelledby="ldap-heading">
          <div class="accordion-body">
            <?php echo render_field($form->ldapHost->label(__('Host'))); ?>

            <?php echo render_field($form->ldapPort->label(__('Port'))); ?>

            <?php echo render_field($form->ldapBaseDn->label(__('Base DN'))); ?>

            <?php echo render_field($form->ldapBindAttribute->label(__('Bind Lookup Attribute'))); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions mb-3">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
