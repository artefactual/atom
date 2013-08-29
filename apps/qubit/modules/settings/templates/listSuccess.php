<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <ul class="nav nav-list nav-stacked">
    <li><a href="#globalArea"><?php echo __('Global') ?></a></li>
    <li><a href="#siteInformationArea"><?php echo __('Site information') ?></a></li>
    <li><a href="#defaultPageElementsArea"><?php echo __('Default page elements') ?></a></li>
    <li><a href="#defaultTemplateArea"><?php echo __('Default template') ?></a></li>
    <li><a href="#userInterfaceLabelArea"><?php echo __('User interface label') ?></a></li>
    <li><a href="#i18nLanguagesArea"><?php echo __('I18n languages') ?></a></li>
    <li><a href="#oaiRepositoryArea"><?php echo __('OAI repository') ?></a></li>
    <li><a href="#jobSchedulingArea"><?php echo __('Job scheduling') ?></a></li>
    <li><a href="#securityArea"><?php echo __('Security') ?></a></li>
  </ul>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Site settings') ?></h1>

<?php end_slot() ?>

<fieldset class="collapsible" id="globalArea">

  <legend><?php echo __('Global') ?></legend>

  <form action="<?php echo url_for('settings/list') ?>" method="post">
    <table class="table">
    <thead>
      <tr>
        <th><?php echo __('Name')?></th>
        <th><?php echo __('Value')?></th>
      </tr>
    </thead>
    <tbody>
      <?php echo $globalForm ?>
      <tr>
        <td>&nbsp;</td>
        <td>
          <div style="float: right; margin: 3px 8px 0 0;">
            <input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/>
          </div>
        </td>
      </tr>
    </tbody>
    </table>
  </form>

</fieldset>

<fieldset class="collapsible" id="siteInformationArea">

  <legend><?php echo __('Site information') ?></legend>

  <form action="<?php echo url_for('settings/list') ?>" method="post">
    <table class="table">
      <thead>
        <tr>
          <th><?php echo __('Name') ?></th>
          <th><?php echo __('Value') ?></th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?php echo $siteInformationForm['site_title']->renderLabel(null,
            array('title' => __('The name of the website for display in the header'))) ?></td>
          <td>
            <?php if (strlen($error = $siteInformationForm['site_title']->renderError())): ?>
              <?php echo $error ?>
            <?php elseif ($sourceCultureHelper = $siteTitle->getSourceCultureHelper($culture)): ?>
              <div class="default-translation"><?php echo $sourceCultureHelper ?></div>
            <?php endif; ?>
            <?php echo $siteInformationForm['site_title']->render() ?>
          </td>
        </tr>
        <tr>
          <td><?php echo $siteInformationForm['site_description']->renderLabel(null,
            array('title' => __('A brief site description or &quot;tagline&quot; for the header'))) ?></td>
          <td>
            <?php if (strlen($error = $siteInformationForm['site_description']->renderError())): ?>
              <?php echo $error ?>
            <?php elseif ($sourceCultureHelper = $siteDescription->getSourceCultureHelper($culture)): ?>
              <div class="default-translation"><?php echo $sourceCultureHelper ?></div>
            <?php endif; ?>
            <?php echo $siteInformationForm['site_description']->render() ?>
          </td>
        </tr>
        <tr>
          <td>&nbsp;</td>
          <td>
            <div style="float: right; margin: 3px 8px 0 0;">
              <input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </form>

</fieldset>

<fieldset class="collapsible" id="defaultPageElementsArea">

  <legend><?php echo __('Default page elements') ?></legend>

  <?php echo $defaultPageElementsForm->renderFormTag(url_for(array('module' => 'sfThemePlugin')), array('style' => 'float: left;')) ?>

    <?php echo $defaultPageElementsForm->renderGlobalErrors() ?>
    <p><?php echo __('Enable or disable the display of certain page elements. Unless they have been overridden by a specific theme, these settings will be used site wide.') ?></p>
    <table class="table">
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
        <tr>
          <td>&nbsp;</td>
          <td>
            <div style="float: right; margin: 3px 8px 0 0;">
              <input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </form>

</fieldset>

<fieldset class="collapsible" id="defaultTemplateArea">

  <legend><?php echo __('Default template') ?></legend>

  <form action="<?php echo url_for('settings/list') ?>" method="post">
    <table class="list">
      <thead>
        <tr>
          <th><?php echo __('Name') ?></th>
          <th><?php echo __('Value') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php echo $defaultTemplateForm ?>
        <tr>
          <td>&nbsp;</td>
          <td>
            <div style="float: right; margin: 3px 8px 0 0;">
              <input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </form>

</fieldset>

<fieldset class="collapsible" id="userInterfaceLabelArea">

  <legend><?php echo __('User interface label') ?></legend>

  <form action="<?php echo url_for('settings/list') ?>" method="post">
    <table class="table">
      <thead>
        <tr>
          <th><?php echo __('Name') ?></th>
          <th><?php echo __('Value') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($uiLabelForm->getSettings() as $setting): ?>
          <tr>
            <td>
              <?php if ($sf_user->getCulture() != $setting->getSourceCulture() && !strlen($setting->getValue())): ?>
                <div class="default-translation"><?php echo $setting->getName() ?></div>
              <?php else: ?>
                <?php echo $setting->getName() ?>
              <?php endif; ?>
            </td>
            <td>
              <?php echo $uiLabelForm[$setting->getName()] ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <tr>
          <td>&nbsp;</td>
          <td>
            <div style="float: right; margin: 3px 8px 0 0;">
              <input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </form>

</fieldset>

<fieldset class="collapsible" id="i18nLanguagesArea">

  <legend><?php echo __('I18n languages') ?></legend>

  <form action="<?php echo url_for('settings/list') ?>" method="post">
    <table class="table">
      <thead>
        <tr>
          <th><?php echo __('Name') ?></th>
          <th><?php echo __('Value') ?></th>
          <th/>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($i18nLanguages as $setting): ?>
          <tr>
            <td>
              <?php echo $setting->getName() ?>
            </td>
            <td>
              <?php echo format_language($setting->getName()) ?>
            </td>
            <td>
              <?php if ($setting->deleteable): ?>
                <?php echo link_to(image_tag('delete'), array($setting, 'module' => 'settings', 'action' => 'delete')) ?>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <tr>
          <td colspan="2">
            <?php echo $form->languageCode->renderRow() ?>
          </td>
          <td>
            <div style="float: right; margin: 3px 8px 0 0;">
              <input class="form-submit" type="submit" value="<?php echo __('Add') ?>"/>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </form>

</fieldset>

<fieldset class="collapsible" id="oaiRepositoryArea">

  <legend><?php echo __('OAI Repository') ?></legend>

  <form action="<?php echo url_for('settings/list') ?>" method="post">
    <table class="table">
    <thead>
      <tr>
        <th width="30%"><?php echo __('Name')?></th>
        <th><?php echo __('Value')?></th>
      </tr>
    </thead>
    <tbody>
      <?php echo $oaiRepositoryForm ?>
      <tr>
        <td>&nbsp;</td>
        <td>
          <div style="float: right; margin: 3px 8px 0 0;">
            <input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/>
          </div>
        </td>
      </tr>
    </tbody>
    </table>
  </form>

</fieldset>

<fieldset class="collapsible" id="jobSchedulingArea">

  <legend><?php echo __('Job scheduling') ?></legend>

  <p><?php echo __('Specific Gearman job server options can be found in config/gearman.yml.') ?></p>

  <form action="<?php echo url_for('settings/list') ?>" method="post">
    <table class="table">
    <thead>
      <tr>
        <th width="30%"><?php echo __('Name') ?></th>
        <th><?php echo __('Value') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php echo $jobSchedulingForm ?>
      <tr>
        <td>&nbsp;</td>
        <td>
          <div style="float: right; margin: 3px 8px 0 0;">
            <input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/>
          </div>
        </td>
      </tr>
    </tbody>
    </table>
  </form>

</fieldset>

<fieldset class="collapsible" id="securityArea">

  <legend><?php echo __('Security') ?></legend>

  <form action="<?php echo url_for('settings/list') ?>" method="post">
    <table class="table">
    <thead>
      <tr>
        <th width="30%"><?php echo __('Name') ?></th>
        <th><?php echo __('Value') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php echo $securityForm ?>
      <tr>
        <td>&nbsp;</td>
        <td>
          <div style="float: right; margin: 3px 8px 0 0;">
            <input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/>
          </div>
        </td>
      </tr>
    </tbody>
    </table>
  </form>

</fieldset>
