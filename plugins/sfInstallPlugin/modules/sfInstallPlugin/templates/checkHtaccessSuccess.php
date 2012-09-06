<?php if (isset($htaccess['notWritable'])): ?>
  <div class="messages error">
    <p>
      <?php echo link_to('.htaccess not writable', array('module' => 'sfInstallPlugin', 'action' => 'help'), array('anchor' => '.htaccess_not_writable')) ?>
    </p>
  </div>
<?php endif; ?>
<?php if (isset($htaccess['ignored'])): ?>
  <div class="messages error">
    <p>
      <?php echo link_to('.htaccess files are completely ignored', array('module' => 'sfInstallPlugin', 'action' => 'help'), array('anchor' => '.htaccess_files_are_completely_ignored')) ?>
    </p>
  </div>
<?php endif; ?>
<?php if (isset($htaccess['optionsNotAllowed'])): ?>
  <div class="messages error">
    <p>
      <?php echo link_to('Options not allowed in .htaccess files', array('module' => 'sfInstallPlugin', 'action' => 'help'), array('anchor' => 'Options_not_allowed_in_.htaccess_files')) ?>
    </p>
  </div>
<?php endif; ?>
<?php if (isset($htaccess['modRewriteNotConfigured'])): ?>
  <div class="messages error">
    <p>
      <?php echo link_to('mod_rewrite not configured', array('module' => 'sfInstallPlugin', 'action' => 'help'), array('anchor' => 'mod_rewrite_not_configured')) ?>
    </p>
  </div>
<?php endif; ?>

<?php if (isset($settingsYml['notWritable'])): ?>
  <div class="messages error">
    <p>
      <?php echo link_to('settings.yml not writable', array('module' => 'sfInstallPlugin', 'action' => 'help'), array('anchor' => 'settings.yml_not_writable')) ?>
    </p>
  </div>
<?php endif; ?>
