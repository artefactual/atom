<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_partial('settings/menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Default page elements') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $defaultPageElementsForm->renderFormTag(url_for(array('module' => 'sfThemePlugin')), array('style' => 'float: left;')) ?>

    <?php echo $defaultPageElementsForm->renderGlobalErrors() ?>
    <p><?php echo __('Enable or disable the display of certain page elements. Unless they have been overridden by a specific theme, these settings will be used site wide.') ?></p>

    <div id="content">

      <table class="table sticky-enabled">
        <thead>
          <tr>
            <th><?php echo __('Name')?></th>
            <th><?php echo __('Value')?></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><?php echo $defaultPageElementsForm->toggleLogo->label('Logo')->renderLabel() ?></td>
            <td><?php echo $defaultPageElementsForm->toggleLogo ?></td>
          </tr>
          <tr>
            <td><?php echo $defaultPageElementsForm->toggleTitle->label('Title')->renderLabel() ?></td>
            <td><?php echo $defaultPageElementsForm->toggleTitle ?></td>
          </tr>
          <tr>
            <td><?php echo $defaultPageElementsForm->toggleDescription->label('Description')->renderLabel() ?></td>
            <td><?php echo $defaultPageElementsForm->toggleDescription ?></td>
          </tr>
        </tbody>
      </table>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
