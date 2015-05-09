<div class="sidebar-lowering settings-menu">
    <table class="table table-bordered table-hover table-condensed">
      <tbody>
        <tr><td><?php echo link_to(__('Global'), array('module' => 'settings', 'action' => 'global')) ?></td></tr>
        <tr><td><?php echo link_to(__('Site information'), array('module' => 'settings', 'action' => 'siteInformation')) ?></td></tr>
        <tr><td><?php echo link_to(__('Default page elements'), array('module' => 'settings', 'action' => 'pageElements')) ?></td></tr>
        <tr><td><?php echo link_to(__('Default template'), array('module' => 'settings', 'action' => 'template')) ?></td></tr>
        <tr><td><?php echo link_to(__('User interface label'), array('module' => 'settings', 'action' => 'interfaceLabel')) ?></td></tr>
        <tr><td><?php echo link_to(__('I18n languages'), array('module' => 'settings', 'action' => 'language')) ?></td></tr>
        <?php if ($sf_context->getConfiguration()->isPluginEnabled('arOaiPlugin')): ?>
            <tr><td><?php echo link_to(__('OAI repository'), array('module' => 'settings', 'action' => 'oai')) ?></td></tr>
        <?php endif; ?>
        <tr><td><?php echo link_to(__('Finding Aid'), array('module' => 'settings', 'action' => 'findingAid')) ?></td></tr>
        <tr><td><?php echo link_to(__('Security'), array('module' => 'settings', 'action' => 'security')) ?></td></tr>
        <tr><td><?php echo link_to(__('Permissions'), array('module' => 'settings', 'action' => 'permissions')) ?></td></tr>
        <tr><td><?php echo link_to(__('Inventory'), array('module' => 'settings', 'action' => 'inventory')) ?></td></tr>
        <tr><td><?php echo link_to(__('Digital object derivatives'), array('module' => 'settings', 'action' => 'digitalObjectDerivatives')) ?></td></tr>
      </tbody>
    </table>
</div>
