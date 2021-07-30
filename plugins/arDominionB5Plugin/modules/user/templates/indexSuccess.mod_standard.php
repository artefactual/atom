<h1><?php echo __('User %1%', ['%1%' => render_title($resource)]); ?></h1>

<?php if (!$resource->active) { ?>
  <div class="alert alert-danger" role="alert">
    <?php echo __('This user is inactive'); ?>
  </div>
<?php } ?>

<?php echo get_component('user', 'aclMenu'); ?>

<section id="content" class="p-0">

  <section id="userDetails">

    <?php echo link_to_if(QubitAcl::check($resource, 'update'), render_b5_section_label(__('User details')), [$resource, 'module' => 'user', 'action' => 'edit'], ['class' => 'text-primary']); ?>

    <?php echo render_show(__('User name'), render_value_inline($resource->username.($sf_user->user === $resource ? ' ('.__('you').')' : ''))); ?>

    <?php echo render_show(__('Email'), $resource->email); ?>

    <?php if (!$sf_user->isAdministrator()) { ?>
      <?php echo render_show(__('Password'), link_to(__('Reset password'), [$resource, 'module' => 'user', 'action' => 'passwordEdit'])); ?>
    <?php } ?>

    <?php if (0 < count($groups = $resource->getAclGroups())) { ?>
      <div class="field <?php echo render_b5_show_field_css_classes(); ?>">
        <?php echo render_b5_show_label(__('User groups')); ?>
        <div class="<?php echo render_b5_show_value_css_classes(); ?>">
          <ul class="<?php echo render_b5_show_list_css_classes(); ?>">
            <?php foreach ($groups as $item) { ?>
              <li><?php echo $item->__toString(); ?></li>
            <?php } ?>
          </ul>
        </div>
      </div>
    <?php } ?>

    <?php if (
        sfConfig::get('app_multi_repository')
        && 0 < count($repositories = $resource->getRepositories())
    ) { ?>
      <div class="field <?php echo render_b5_show_field_css_classes(); ?>">
        <?php echo render_b5_show_label(__('Repository affiliation')); ?>
        <div class="<?php echo render_b5_show_value_css_classes(); ?>">
          <ul class="<?php echo render_b5_show_list_css_classes(); ?>">
            <?php foreach ($repositories as $item) { ?>
              <li><?php echo render_title($item); ?></li>
            <?php } ?>
          </ul>
        </div>
      </div>
    <?php } ?>

    <?php if ($sf_context->getConfiguration()->isPluginEnabled('arRestApiPlugin')) { ?>
      <?php echo render_show(
          __('OAI-PMH API key'),
          isset($restApiKey) ? '<code>'.$restApiKey.'</code>' : __('Not generated yet.')
      ); ?>
    <?php } ?>

    <?php if ($sf_context->getConfiguration()->isPluginEnabled('arOaiPlugin')) { ?>
      <?php echo render_show(
          __('OAI-PMH API key'),
          isset($oaiApiKey) ? '<code>'.$oaiApiKey.'</code>' : __('Not generated yet.')
      ); ?>
    <?php } ?>

    <?php if (sfConfig::get('app_audit_log_enabled', false)) { ?>
      <div id="editing-history-wrapper">
        <div class="accordion accordion-flush border-top hidden" id="editingHistory">
          <div class="accordion-item">
            <h2 class="accordion-header" id="history-heading">
              <button class="accordion-button collapsed bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#history-collapse" aria-expanded="false" aria-controls="history-collapse">
                <?php echo __('Editing history'); ?>
                <span id="editingHistoryActivityIndicator">
                  <i class="fas fa-spinner fa-spin ms-2" aria-hidden="true"></i>
                  <span class="visually-hidden"><?php echo __('Loading ...'); ?></span>
                </span>
              </button>
            </h2>
            <div id="history-collapse" class="accordion-collapse collapse" aria-labelledby="history-heading">
              <div class="accordion-body table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>
                        <?php echo __('Title'); ?>
                      </th>
                      <th>
                        <?php echo __('Date'); ?>
                      </th>
                      <th>
                        <?php echo __('Type'); ?>
                      </th>
                    </tr>
                  </thead>
                  <tbody id="editingHistoryRows">
                  </tbody>
                </table>

                <div class="text-end">
                  <input class="btn atom-btn-white" type="button" id='previousButton' value='<?php echo __('Previous'); ?>'>
                  <input class="btn atom-btn-white ms-2" type="button" id='nextButton' value='<?php echo __('Next'); ?>'>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php } ?>

  </section>
</section>

<?php echo get_partial('showActions', ['resource' => $resource]); ?>
