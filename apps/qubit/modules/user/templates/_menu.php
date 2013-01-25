<div>
  <?php if ($sf_user->isAuthenticated()): ?>
    <?php echo link_to(__('Log out'), array('module' => 'user', 'action' => 'logout'), array('class' => 'top-item top-button')) ?>
    <?php $gravatar = image_tag(url_for('https://www.gravatar.com/avatar/'.md5(strtolower(trim($sf_user->user->email))).'?s=25&d='.urlencode(public_path('/images/gravatar-anonymous.png', true)))) ?>
  <?php else: ?>
    <?php echo link_to(__('Log in'), array('module' => 'user', 'action' => 'login'), array('class' => 'top-item top-button')) ?>
  <?php endif; ?>
</div>
