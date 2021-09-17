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
          </th>
          <th>
            <?php echo __('Version'); ?>
          </th>
          <th id="plugins-enabled-head">
            <?php echo __('Enabled'); ?>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($sf_data->getRaw('plugins') as $name => $plugin) { ?>
          <tr>
            <td>
              <?php if (file_exists($plugin->getRootDir().'/images/image.png')) { ?>
                <?php echo image_tag('/plugins/'.$name.'/images/image', ['alt' => $name, 'class' => 'mb-2']); ?>
              <?php } ?>
              <p class="mb-2"><?php echo $name; ?></p>
              <span class="small text-muted">
                <?php echo $plugin::$summary; ?>
              </span>
            </td>
            <td>
              <?php echo $plugin::$version; ?>
            </td>
            <td align="center">
              <input
                <?php if (
                    $form->isBound() && in_array($name, $form->getValue('enabled'))
                    || !$form->isBound() && in_array($name, $form->getDefault('enabled'))
                ) { ?>
                  checked="checked"
                <?php } ?>
                <?php if (
                    'sfIsdiahPlugin' == $name
                    || 'sfIsaarPlugin' == $name
                    || ('sfIsadPlugin' == $name && 'isad' == $defaultTemplate)
                    || ('sfRadPlugin' == $name && 'rad' == $defaultTemplate)
                    || ('sfDcPlugin' == $name && 'dc' == $defaultTemplate)
                    || ('sfModsPlugin' == $name && 'mods' == $defaultTemplate)
                ) { ?>
                  disabled="disabled"
                <?php } ?>
                class="form-check-input"
                arial-labelledby="plugins-enabled-head"
                name="enabled[]"
                type="checkbox"
                value="<?php echo $name; ?>">
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <section class="actions mb-3">
    <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
  </section>

</form>
