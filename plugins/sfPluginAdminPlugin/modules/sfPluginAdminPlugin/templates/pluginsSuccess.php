<h1><?php echo __('List plugins') ?></h1>

<?php echo $form->renderGlobalErrors() ?>

<?php echo $form->renderFormTag(url_for(array('module' => 'sfPluginAdminPlugin', 'action' => 'plugins'))) ?>

  <table class="sticky-enabled">
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
      <?php foreach ($plugins as $name => $plugin): ?>
        <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
          <td>
            <?php if (file_exists($plugin->getRootDir().'/images/image.png')): ?>
              <?php echo image_tag('/plugins/'.$name.'/images/image', array('alt' => $name)) ?>
            <?php endif; ?>
            <h2><?php echo $name ?></h2>
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

  <div class="actions section">
    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>
    <div class="content">
      <ul class="clearfix links">
        <input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/>
      </ul>
    </div>
  </div>

</form>
