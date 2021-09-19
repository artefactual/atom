<h1><?php echo __('User %1%', ['%1%' => render_title($resource)]); ?></h1>

<?php if (!$resource->active) { ?>
  <div class="alert alert-danger" role="alert">
    <?php echo __('This user is inactive'); ?>
  </div>
<?php } ?>

<?php echo get_component('user', 'aclMenu'); ?>

<section id="content">

  <section id="userDetails">

    <?php echo render_b5_section_heading(
        __('User details'),
        QubitAcl::check($resource, 'update'),
        [$resource, 'module' => 'user', 'action' => 'edit'],
        ['class' => 'rounded-top']
    ); ?>

    <?php echo render_show(__('User name'), render_value_inline($resource->username.($sf_user->user === $resource ? ' ('.__('you').')' : ''))); ?>

    <?php echo render_show(__('Email'), $resource->email); ?>

    <?php if (!$sf_user->isAdministrator()) { ?>
      <?php echo render_show(__('Password'), link_to(__('Reset password'), [$resource, 'module' => 'user', 'action' => 'passwordEdit'])); ?>
    <?php } ?>

    <?php if (0 < count($groups = $resource->getAclGroups())) { ?>
      <?php echo render_show(__('User groups'), $groups); ?>
    <?php } ?>

    <?php if (
        sfConfig::get('app_multi_repository')
        && 0 < count($repositories = $resource->getRepositories())
    ) { ?>
      <?php
          $repos = [];
          foreach ($repositories as $item) {
              $repos[] = render_title($item);
          }
          echo render_show(__('Repository affiliation'), $repos);
      ?>
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
          <div class="accordion-item rounded-bottom">
            <h2 class="accordion-header" id="history-heading">
              <button class="accordion-button collapsed text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#history-collapse" aria-expanded="false" aria-controls="history-collapse">
                <?php echo __('Editing history'); ?>
                <span id="editingHistoryActivityIndicator">
                  <i class="fas fa-spinner fa-spin ms-2" aria-hidden="true"></i>
                  <span class="visually-hidden"><?php echo __('Loading ...'); ?></span>
                </span>
              </button>
            </h2>
            <div id="history-collapse" class="accordion-collapse collapse" aria-labelledby="history-heading">
              <div class="accordion-body">
                <div class="table-responsive mb-3">
                  <table class="table table-bordered mb-0">
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
                </div>

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
