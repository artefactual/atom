<?php use_helper('Javascript') ?>

<h2>System checks</h2>

<div id="progress"></div>
<?php $checkHtaccessUrl = json_encode(url_for(array('module' => 'sfInstallPlugin', 'action' =>  'checkHtaccess'))) ?>
<?php echo javascript_tag(<<<EOF
var progress = new Drupal.progressBar('checkSystem');
progress.setProgress(-1, 'Check system');
jQuery('#progress').append(progress.element);
progress.setProgress(0, 'Check .htaccess');
jQuery.ajax({
  url: $checkHtaccessUrl,
  complete: function (request)
    {
      jQuery('#progress').after(request.responseText);
      progress.setProgress(100, 'Check system');
    } });
EOF
) ?>
<!-- TODO We currently do this logic in the template instead of the action to give the user more immediate feedback, but symfony apparently buffers output and does not start sending it to the user until it is finished being generated : ( -->

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

<div class="actions section">
  <h2 class="element-invisible">Actions</h2>
  <div class="content">
    <ul class="clearfix links">
      <?php if ($error): ?>
        <li><?php echo link_to('Try again', $sf_request->getUri()) ?></li>
        <li><?php echo link_to('Ignore errors and continue', array('module' => 'sfInstallPlugin', 'action' => 'configureDatabase')) ?></li>
      <?php else: ?>
        <!-- If JavaScript is enabled, automatically redirect to the next task.  Include a link in case it is not. -->
        <li><?php echo link_to('Continue', array('module' => 'sfInstallPlugin', 'action' => 'configureDatabase')) ?></li>
      <?php endif; ?>
    </ul>
  </div>
</div>
