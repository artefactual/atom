<h1><?php echo __('User %1%', ['%1%' => render_title($resource)]); ?></h1>

<?php if (!$resource->active) { ?>
  <div class="messages error">
    <ul>
      <?php if (!$resource->active) { ?>
        <li><?php echo __('This user is inactive'); ?></li>
      <?php } ?>
    </ul>
  </div>
<?php } ?>

<?php echo get_component('user', 'aclMenu'); ?>

<section id="content">

  <section id="userDetails">

    <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('User details').'</h2>', [$resource, 'module' => 'user', 'action' => 'edit']); ?>

    <?php echo render_show(__('User name'), render_value($resource->username.($sf_user->user === $resource ? ' ('.__('you').')' : ''))); ?>

    <?php if (0 < count($groups = $resource->getAclGroups())) { ?>
      <div class="field">
        <h3><?php echo __('User groups'); ?></h3>
        <div>
          <ul>
            <?php foreach ($groups as $item) { ?>
              <?php if (100 <= $item->id) { ?>
                <li><?php echo $item->__toString(); ?></li>
              <?php } else { ?>
                <li><span class="note2"><?php echo $item->__toString(); ?></li>
              <?php } ?>
            <?php } ?>
          </ul>
        </div>
      </div>
    <?php } ?>

    <?php if (sfConfig::get('app_multi_repository')) { ?>
      <?php if (0 < count($repositories = $resource->getRepositories())) { ?>
        <div class="field">
          <h3><?php echo __('Repository affiliation'); ?></h3>
          <div>
            <ul>
              <?php foreach ($repositories as $item) { ?>
                <li><?php echo render_title($item); ?></li>
              <?php } ?>
            </ul>
          </div>
        </div>
      <?php } ?>
    <?php } ?>

    <?php if ($sf_context->getConfiguration()->isPluginEnabled('arRestApiPlugin')) { ?>
      <div class="field">
        <h3><?php echo __('REST API key'); ?></h3>
        <div>
          <?php if (isset($restApiKey)) { ?>
            <code><?php echo $restApiKey; ?></code>
          <?php } else { ?>
            <?php echo __('Not generated yet.'); ?>
          <?php } ?>
        </div>
      </div>
    <?php } ?>

    <?php if ($sf_context->getConfiguration()->isPluginEnabled('arOaiPlugin')) { ?>
      <div class="field">
        <h3><?php echo __('OAI-PMH API key'); ?></h3>
        <div>
          <?php if (isset($oaiApiKey)) { ?>
            <code><?php echo $oaiApiKey; ?></code>
          <?php } else { ?>
            <?php echo __('Not generated yet.'); ?>
          <?php } ?>
        </div>
      </div>
    <?php } ?>

    <?php if (sfConfig::get('app_audit_log_enabled', false)) { ?>
      <div id="editing-history-wrapper">
        <div class="accordion hidden" id="editingHistory">
          <div class="accordion-item">
            <h2 class="accordion-header" id="history-heading">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#history-collapse" aria-expanded="false" aria-controls="history-collapse">
                <?php echo __('Editing history'); ?>
                <span id="editingHistoryActivityIndicator">
                  <i class="fas fa-spinner fa-spin ms-2" aria-hidden="true"></i>
                  <span class="visually-hidden"><?php echo __('Loading ...'); ?></span>
                </span>
              </button>
            </h2>
            <div id="history-collapse" class="accordion-collapse collapse" aria-labelledby="history-heading">
              <div class="accordion-body table-responsive">
                <table class="table table-bordered table-striped sticky-enabled">
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
                  <input class="btn atom-btn-white" type="button" id='nextButton' value='<?php echo __('Next'); ?>'>
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
