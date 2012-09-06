<?php echo image_tag('logo', array('id' => 'logo', 'alt' => 'Home')) ?>

<h1>Installation</h1>

<div class="section">

  <div class="content">
    <ol class="clearfix task-list">
      <li<?php switch ($sf_request->action): case 'checkSystem': ?> class="active"<?php break; case 'configureDatabase': case 'loadData': case 'configureSite': case 'finishInstall': ?> class="done"<?php endswitch; ?>>Check system</li>
      <li<?php switch ($sf_request->action): case 'configureDatabase': ?> class="active"<?php break; case 'loadData': case 'configureSite': case 'finishInstall': ?> class="done"<?php endswitch; ?>>Configure database</li>
      <li<?php switch ($sf_request->action): case 'loadData': ?> class="active"<?php break; case 'configureSite': case 'finishInstall': ?> class="done"<?php endswitch; ?>>Load data</li>
      <li<?php switch ($sf_request->action): case 'configureSite': ?> class="active"<?php break; case 'finishInstall': ?> class="done"<?php endswitch; ?>>Configure site</li>
    </ol>
  </div>

</div>

<div class="section">

  <h2>Help</h2>

  <div class="content">
    <ul class="clearfix links">
      <li><a href="http://www.qubit-toolkit.org/wiki/Administrator_manual">Administrator manual</a></li>
      <li><a href="http://www.qubit-toolkit.org/wiki/FAQ">Frequently asked questions</a></li>
    </ul>
  </div>

</div>
