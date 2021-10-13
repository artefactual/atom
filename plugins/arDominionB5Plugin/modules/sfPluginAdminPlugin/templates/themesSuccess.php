<h1><?php echo __('List themes'); ?></h1>

<?php echo $form->renderGlobalErrors(); ?>

<?php echo $form->renderFormTag(url_for(['module' => 'sfPluginAdminPlugin', 'action' => 'themes'])); ?>

  <?php echo $form->renderHiddenFields(); ?>

  <table class="table table-bordered sticky-enabled">
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
    </thead><tbody>
      <?php foreach ($sf_data->getRaw('plugins') as $name => $plugin) { ?>
        <tr>
          <td>
            <?php if (file_exists($plugin->getRootDir().'/images/image.png')) { ?>
              <a href="#" class="plugin-screenshot"><?php echo image_tag('/plugins/'.$name.'/images/image', ['alt' => $name]); ?></a>
            <?php } ?>
            <p class="plugin-name"><?php echo $name; ?></p>
            <div class="plugin-description">
              <?php echo $plugin::$summary; ?>
            </div>
          </td><td>
            <?php echo $plugin::$version; ?>
          </td><td align="center">
            <input<?php if ($form->isBound() && in_array($name, $form->getValue('enabled')) || !$form->isBound() && in_array($name, $form->getDefault('enabled'))) { ?> checked="checked"<?php } ?> name="enabled[]" type="radio" value="<?php echo $name; ?>"
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

  <section class="actions">
    <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
  </section>

</form>
