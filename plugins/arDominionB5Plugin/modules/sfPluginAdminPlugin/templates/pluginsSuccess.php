<h1><?php echo __('List plugins'); ?></h1>

<?php echo $form->renderGlobalErrors(); ?>

<?php echo $form->renderFormTag(url_for(['module' => 'sfPluginAdminPlugin', 'action' => 'plugins'])); ?>

  <?php echo $form->renderHiddenFields(); ?>

  <div class="table-responsive mb-3">
    <table class="table table-bordered mb-0">
      <thead>
        <tr>
          <th>
            <?php echo __('Name'); ?>
          </th><th>
            <?php echo __('Version'); ?>
          </th><th>
            <?php echo __('Enabled'); ?>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($sf_data->getRaw('plugins') as $name => $plugin) { ?>
          <tr>
            <td>
              <?php if (file_exists($plugin->getRootDir().'/images/image.png')) { ?>
                <?php echo image_tag('/plugins/'.$name.'/images/image', ['alt' => $name]); ?>
              <?php } ?>
              <p class="plugin-name"><?php echo $name; ?></p>
              <div class="plugin-description">
                <?php echo $plugin::$summary; ?>
              </div>
            </td><td>
              <?php echo $plugin::$version; ?>
            </td><td align="center">
              <input
                <?php if ($form->isBound() && in_array($name, $form->getValue('enabled'))
                            || !$form->isBound() && in_array($name, $form->getDefault('enabled'))) { ?>
                  checked="checked"
                <?php } ?>
                <?php if ('sfIsdiahPlugin' == $name
                          || 'sfIsaarPlugin' == $name
                          || ('sfIsadPlugin' == $name && 'isad' == $defaultTemplate)
                          || ('sfRadPlugin' == $name && 'rad' == $defaultTemplate)
                          || ('sfDcPlugin' == $name && 'dc' == $defaultTemplate)
                          || ('sfModsPlugin' == $name && 'mods' == $defaultTemplate)) { ?>
                  disabled="disabled"
                <?php } ?>
                name="enabled[]" type="checkbox" value="<?php echo $name; ?>"
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <section class="actions">
    <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
  </section>

</form>
