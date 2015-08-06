<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_component('settings', 'menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Site information') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <form action="<?php echo url_for('settings/siteInformation') ?>" method="post">

    <div id="content">

      <table class="table sticky-enabled">
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
            <td><?php echo $siteInformationForm['site_base_url']->renderLabel(null,
              array('title' => __('Used to create absolute URLs, pointing to resources, in XML exports'))) ?></td>
            <td>
              <?php if (strlen($error = $siteInformationForm['site_base_url']->renderError())): ?>
                <?php echo $error ?>
              <?php elseif ($sourceCultureHelper = $siteBaseUrl->getSourceCultureHelper($culture)): ?>
                <div class="default-translation"><?php echo $sourceCultureHelper ?></div>
              <?php endif; ?>
              <?php echo $siteInformationForm['site_base_url']->render() ?>
            </td>
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
