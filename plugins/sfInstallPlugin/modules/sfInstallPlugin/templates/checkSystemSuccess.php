<?php use_helper('Javascript') ?>

<h2>System checks</h2>

<!-- TODO Consider using array keys for wiki anchors -->
<?php $error = false ?>

<?php $error |= count($dependencies = sfInstall::checkDependencies()) > 0 ?>
<?php if (isset($dependencies['php']['min'])): ?>
  <div class="messages error">
    <p>
      <?php echo link_to('Minimum PHP version', array('module' => 'sfInstallPlugin', 'action' => 'help'), array('anchor' => 'Minimum_PHP_version')) ?>: <?php echo $dependencies['php']['min'] ?>
    </p>
    <p>
      Current version is <?php echo PHP_VERSION ?>
    </p>
  </div>
<?php endif; ?>
<?php if (isset($dependencies['extensions'])): ?>
  <?php foreach ($dependencies['extensions'] as $extension): ?>
    <div class="messages error">
      <p>
        <?php echo link_to($extension.' extension', array('module' => 'sfInstallPlugin', 'action' => 'help'), array('anchor' => $extension.'_extension')) ?>
      </p>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php $error |= count($writablePaths = sfInstall::checkWritablePaths()) > 0 ?>
<?php if (count($writablePaths) > 0): ?>
  <div class="messages error">
    <p>
      <?php echo link_to('Writable paths', array('module' => 'sfInstallPlugin', 'action' => 'help'), array('anchor' => 'Writable_paths')) ?>
    </p>
    <ul>
      <?php foreach ($writablePaths as $path): ?>
        <li><?php echo $path ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php $error |= count($databasesYml = sfInstall::checkDatabasesYml()) > 0 ?>
<?php if (isset($databasesYml['notWritable'])): ?>
  <div class="messages error">
    <p>
      <?php echo link_to('databases.yml not writable', array('module' => 'sfInstallPlugin', 'action' => 'help'), array('anchor' => 'databases.yml_not_writable')) ?>
    </p>
  </div>
<?php endif; ?>

<?php $error |= count($propelIni = sfInstall::checkPropelIni()) > 0 ?>
<?php if (isset($propelIni['notWritable'])): ?>
  <div class="messages error">
    <p>
      <?php echo link_to('propel.ini not writable', array('module' => 'sfInstallPlugin', 'action' => 'help'), array('anchor' => 'propel.ini_not_writable')) ?>
    </p>
  </div>
<?php endif; ?>

<?php $error |= count($memoryLimit = sfInstall::checkMemoryLimit()) > 0 ?>
<?php if (count($memoryLimit) > 0): ?>
  <div class="messages error">
    <p>
      <?php echo link_to('Your current PHP memory limit is '.$memoryLimit.' MB which is less than the minimum limit of '.sfInstall::$MINIMUM_MEMORY_LIMIT_MB.' MB.',
        array('module' => 'sfInstallPlugin', 'action' => 'help'), array('anchor' => 'PHP_memory_limit')) ?>
    </p>
  </div>
<?php endif; ?>

<?php $error |= count($settingsYml = sfInstall::checkSettingsYml(false)) > 0 ?>
<?php if (isset($settingsYml['notWritable'])): ?>
  <div class="messages error">
    <p>
      <?php echo link_to('settings.yml not writable', array('module' => 'sfInstallPlugin', 'action' => 'help'), array('anchor' => 'settings.yml_not_writable')) ?>
    </p>
  </div>
<?php endif; ?>

<?php slot('after-content') ?>
  <section class="actions">
    <ul>
      <?php if ($error): ?>
        <li><?php echo link_to('Try again', $sf_request->getUri(), array('class' => 'c-btn')) ?></li>
        <li><?php echo link_to('Ignore errors and continue', array('module' => 'sfInstallPlugin', 'action' => 'configureDatabase'), array('class' => 'c-btn')) ?></li>
      <?php else: ?>
        <!-- If JavaScript is enabled, automatically redirect to the next task.  Include a link in case it is not. -->
        <li><?php echo link_to('Continue', array('module' => 'sfInstallPlugin', 'action' => 'configureDatabase'), array('class' => 'c-btn')) ?></li>
      <?php endif; ?>
    </ul>
  </section>
<?php end_slot() ?>
