<h1><?php echo __('List plugins') ?></h1>

<?php echo $form->renderGlobalErrors() ?>

<?php echo $form->renderFormTag(url_for(array('module' => 'sfPluginAdminPlugin', 'action' => 'plugins'))) ?>

  <table class="table table-bordered sticky-enabled">
    <thead>
      <tr>
        <th>
          <?php echo __('Name') ?>
        </th><th>
          <?php echo __('Version') ?>
        </th><th>
          <?php echo __('Enabled') ?>
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($sf_data->getRaw('plugins') as $name => $plugin): ?>
        <tr>
          <td>
            <?php if (file_exists($plugin->getRootDir().'/images/image.png')): ?>
              <?php echo image_tag('/plugins/'.$name.'/images/image', array('alt' => $name)) ?>
            <?php endif; ?>
            <p class="plugin-name"><?php echo $name ?></p>
            <div class="plugin-description">
              <?php echo $plugin::$summary ?>
            </div>
          </td><td>
            <?php echo $plugin::$version ?>
          </td><td align="center">
            <input
              <?php if ($form->isBound() && in_array($name, $form->getValue('enabled'))
                          || !$form->isBound() && in_array($name, $form->getDefault('enabled'))): ?>
                checked="checked"
              <?php endif; ?>
              <?php if ($name == 'sfIsdiahPlugin'
                        || $name == 'sfIsaarPlugin'
                        || ($name == 'sfIsadPlugin' && $defaultTemplate =='isad')
                        || ($name == 'sfRadPlugin' && $defaultTemplate == 'rad')
                        || ($name == 'sfDcPlugin' && $defaultTemplate == 'dc')
                        || ($name == 'sfModsPlugin' && $defaultTemplate == 'mods')): ?>
                disabled="disabled"
              <?php endif; ?>
              name="enabled[]" type="checkbox" value="<?php echo $name ?>"
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <section class="actions">
    <ul>
      <input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/>
    </ul>
  </section>

</form>
