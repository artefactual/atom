<div class="logo">
  <?php echo image_tag('logo', ['id' => 'logo', 'alt' => 'Home']); ?>
</div>

<h1>Installation</h1>

<div class="section">
  <div class="content">
    <ol class="clearfix task-list">
      <li <?php echo isset($checkSystemStatus) ? 'class="'.$checkSystemStatus.'"' : ''; ?>>Check system</li>
      <li <?php echo isset($configureDatabaseStatus) ? 'class="'.$configureDatabaseStatus.'"' : ''; ?>>Configure database</li>
      <li <?php echo isset($configureSearchStatus) ? 'class="'.$configureSearchStatus.'"' : ''; ?>>Configure search</li>
      <li <?php echo isset($loadDataStatus) ? 'class="'.$loadDataStatus.'"' : ''; ?>>Load data</li>
      <li <?php echo isset($configureSiteStatus) ? 'class="'.$configureSiteStatus.'"' : ''; ?>>Configure site</li>
    </ol>
  </div>
</div>
