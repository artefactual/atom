<?php echo get_component('user', 'aclMenu') ?>

<h1><?php echo __('View user profile') ?></h1>

<h1 class="label"><?php echo link_to_if(SecurityCheck::HasPermission($sf_user, array('module' => 'user', 'action' => 'edit')), render_title($resource), array($resource, 'module' => 'user', 'action' => 'edit'), array('title' => __('Edit user'))) ?></h1>

<?php if (0 < $notesCount || !$resource->active): ?>
  <div class="messages error">
    <ul>
      <?php if (0 < $notesCount): ?>
        <li><?php echo __('This user has %1% notes in the system and therefore it cannot be removed', array('%1%' => $notesCount)) ?></li>
      <?php endif; ?>
      <?php if (!$resource->active): ?>
        <li><?php echo __('This user is inactive') ?></li>
      <?php endif; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="section">

  <?php echo render_show(__('User name'), $resource->username.($sf_user->user === $resource ? ' ('.__('you').')' : '')) ?>

  <?php echo render_show(__('Email'), $resource->email) ?>

  <?php if (!$sf_user->hasCredential('administrator')): ?>
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

</div>

<?php echo get_partial('showActions', array('resource' => $resource)) ?>
