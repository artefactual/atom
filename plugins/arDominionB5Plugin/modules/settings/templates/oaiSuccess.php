<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('OAI repository settings'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <form action="<?php echo url_for('settings/oai'); ?>" method="post">

    <div class="accordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="oai-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#oai-collapse" aria-expanded="true" aria-controls="oai-collapse">
            <?php echo __('OAI repository settings'); ?>
          </button>
        </h2>
        <div id="oai-collapse" class="accordion-collapse collapse show" aria-labelledby="oai-heading">
          <div class="accordion-body">
            <p><?php echo __('The OAI-PMH API can be secured, optionally, by requiring API requests authenticate using API keys (granted to specific users).'); ?></p>

            <?php echo render_field($oaiRepositoryForm->oai_authentication_enabled); ?>

            <?php echo render_field($oaiRepositoryForm->oai_repository_code); ?>

            <?php echo render_field($oaiRepositoryForm->oai_admin_emails); ?>

            <?php echo render_field($oaiRepositoryForm->oai_repository_identifier); ?>

            <?php echo render_field($oaiRepositoryForm->sample_oai_identifier); ?>

            <?php echo render_field($oaiRepositoryForm->resumption_token_limit, null, ['type' => 'number']); ?>

            <?php echo render_field($oaiRepositoryForm->oai_additional_sets_enabled); ?>
          </div>
        </div>
      </div>
    </div>

    <section class="actions">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
