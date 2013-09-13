<?php $sf_response->addStylesheet('/vendor/yui/container/assets/skins/sam/container', 'first') ?>

<?php $sf_response->addJavaScript('/vendor/yui/container/container-min') ?>

<!-- form for yui dialog -->
<div id="myDialog">
  <div class="hd"><?php echo __('Add %1%', array('%1%' => lcfirst(sfConfig::get('app_ui_label_repository')))) ?></div>
  <div class="bd">
    <form name="repositoryAcl" method="post">
      <div class="form-item">
        <label for="addRepository"><?php echo __('%1% name', array('%1%' => sfConfig::get('app_ui_label_repository'))) ?></label>
        <select name="addRepository" id="addRepository" class="form-autocomplete"></select>
        <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'repository', 'action' => 'autocomplete')) ?>"/>
      </div>
    </form>
  </div>
</div>
<?php
$actionLabel = __('Action');
$permissionsLabel = __('Permissions');

$tableTemplate = <<<EOL
<div class="form-item">
<table id="acl_{objectId}" class="table table-bordered">
<caption/>
<thead>
<tr>
<th scope="col">$actionLabel</th>
<th scope="col">$permissionsLabel</th>
</tr>
</thead>
<tbody>
EOL;

$row = 0;
foreach ($basicActions as $key => $item)
{
  $tableTemplate .= '<tr class="'.((0 == ++$row % 2) ? 'even' : 'odd').'">';
  $tableTemplate .= '<td>'.__($item).'</th>';
  $tableTemplate .= '<td><ul class="radio inline">';
  $tableTemplate .= '<li><input type="radio" name="acl['.$key.'_{objectId}]" value="'.QubitAcl::GRANT.'"/>'.__('Grant').'</li>';
  $tableTemplate .= '<li><input type="radio" name="acl['.$key.'_{objectId}]" value="'.QubitAcl::DENY.'"/>'.__('Deny').'</li>';
  $tableTemplate .= '<li><input type="radio" name="acl['.$key.'_{objectId}]" value="'.QubitAcl::INHERIT.'" checked/>'.__('Inherit').'</li>';
  $tableTemplate .= '</ul></td>';
  $tableTemplate .= "</tr>\n";
}

$tableTemplate .= <<<EOL
</tbody>
</table>
</div>
EOL;

$tableTemplate = str_replace("\n", '', $tableTemplate);
?>

<?php echo javascript_tag(<<<EOL
var handleCancel = function() {
    this.cancel();
};
var handleSubmit = function() {
  var repositoryInput = jQuery('input[name="addRepository"]');
  var objectUri = repositoryInput.val().split('/');
  var objectId = objectUri.pop();

  // Don't duplicate an existing table
  if (0 < jQuery('table#acl_'+objectUri).length)
  {
    // Highlight caption of duplicate table
    var caption = jQuery('table#acl_'+objectUri+' caption');
    caption.css('background', 'yellow');

    this.hide(); // Hide dialog

    setTimeout(function () {
      caption.css('background', 'none');
    }, 1000);
  }
  else if ('null' != repositoryInput.val())
  {
    var newTable = '$tableTemplate';

    // Search and replace '{objectId}'
    while (0 <= newTable.indexOf('{objectId}'))
    {
      newTable = newTable.replace('{objectId}', objectId);
    }

    newTable = jQuery(newTable);
    newTable.find('caption').text(repositoryInput.next('input').val());
    newTable.hide();
    jQuery('a#addRepositoryLink').parent('div').before(newTable);
    newTable.slideDown();
  }

  // Hide dialog
  this.hide();

  // Clear dialog values
  repositoryInput.val('null');
  repositoryInput.next('input').val(null);
};
var myButtons = [
    { text: "Submit", handler: handleSubmit, isDefault: true },
    { text: "Cancel", handler: handleCancel }
];

var config = {
  width: "480px",
  zIndex: "100",
  fixedcenter: true,
  draggable: true,
  visible: false,
  modal: true,
  constraintoviewport: true,
  postmethod: "none" }

var myDialog = new YAHOO.widget.Dialog('myDialog', config);
myDialog.cfg.queueProperty("buttons", myButtons);
myDialog.render();

// Remove all showEvent listeners to prevent default 'focusFirst' behaviour
myDialog.showEvent.unsubscribeAll();

// Focus on 'addRepository' (visible) field
myDialog.showEvent.subscribe(function () {
  document.getElementById('addRepository').focus();
}, this, true);

EOL
) ?>

