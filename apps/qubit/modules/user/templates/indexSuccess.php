<h1><?php echo __('User %1%', array('%1%' => render_title($resource))) ?></h1>

<?php echo get_component('user', 'aclMenu') ?>

<?php if (!$resource->active): ?>
  <div class="messages error">
    <ul>
      <?php if (!$resource->active): ?>
        <li><?php echo __('This user is inactive') ?></li>
      <?php endif; ?>
    </ul>
  </div>
<?php endif; ?>

<section id="content">

  <section id="userDetails">

    <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('User details').'</h2>', array($resource, 'module' => 'user', 'action' => 'edit')) ?>

    <?php echo render_show(__('User name'), $resource->username.($sf_user->user === $resource ? ' ('.__('you').')' : '')) ?>

    <?php echo render_show(__('Email'), $resource->email) ?>

    <?php if (!$sf_user->isAdministrator()): ?>
      <div class="field">
        <h3><?php echo __('Password') ?></h3>
        <div><?php echo link_to(__('Reset password'), array($resource, 'module' => 'user', 'action' => 'passwordEdit')) ?></div>
      </div>
    <?php endif; ?>

    <?php if (0 < count($groups = $resource->getAclGroups())): ?>
      <div class="field">
        <h3><?php echo __('User groups') ?></h3>
        <div>
          <ul>
            <?php foreach ($groups as $item): ?>
              <?php if (100 <= $item->id): ?>
                <li><?php echo $item->__toString() ?></li>
              <?php else: ?>
                <li><span class="note2"><?php echo $item->__toString() ?></li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    <?php endif; ?>

    <?php if (sfConfig::get('app_multi_repository')): ?>
      <?php if (0 < count($repositories = $resource->getRepositories())): ?>
        <div class="field">
          <h3><?php echo __('Repository affiliation') ?></h3>
          <div>
            <ul>
              <?php foreach ($repositories as $item): ?>
                <li><?php echo render_title($item) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>

    <?php if ($sf_context->getConfiguration()->isPluginEnabled('arRestApiPlugin')): ?>
      <div class="field">
        <h3><?php echo __('REST API key') ?></h3>
        <div>
          <?php if (isset($restApiKey)): ?>
            <code><?php echo $restApiKey ?></code>
          <?php else: ?>
            <?php echo __('Not generated yet.') ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($sf_context->getConfiguration()->isPluginEnabled('arOaiPlugin')): ?>
      <div class="field">
        <h3><?php echo __('OAI-PMH API key') ?></h3>
        <div>
          <?php if (isset($oaiApiKey)): ?>
            <code><?php echo $oaiApiKey ?></code>
          <?php else: ?>
            <?php echo __('Not generated yet.') ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>

  </section>

</section>

<?php echo get_partial('showActions', array('resource' => $resource)) ?>
