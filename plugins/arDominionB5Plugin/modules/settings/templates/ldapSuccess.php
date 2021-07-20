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

    <div class="accordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="ldap-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ldap-collapse" aria-expanded="true" aria-controls="ldap-collapse">
            <?php echo __('LDAP authentication settings'); ?>
          </button>
        </h2>
        <div id="ldap-collapse" class="accordion-collapse collapse show" aria-labelledby="ldap-heading">
          <div class="accordion-body">
            <?php echo $form->ldapHost
                ->label(__('Host'))
                ->renderRow(); ?>

            <?php echo $form->ldapPort
                ->label(__('Port'))
                ->renderRow(); ?>

            <?php echo $form->ldapBaseDn
                ->label(__('Base DN'))
                ->renderRow(); ?>

            <?php echo $form->ldapBindAttribute
                ->label(__('Bind Lookup Attribute'))
                ->renderRow(); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
