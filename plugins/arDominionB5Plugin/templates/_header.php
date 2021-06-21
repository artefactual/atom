<?php echo get_component('default', 'privacyMessage'); ?>

<?php echo get_component('default', 'updateCheck'); ?>

<?php if ($sf_user->isAdministrator() && '' === (string) QubitSetting::getByName('siteBaseUrl')) { ?>
  <div class="site-warning text-center p-1">
    <?php echo link_to(__('Please configure your site base URL'), 'settings/siteInformation'); ?>
  </div>
<?php } ?>

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <?php if (sfConfig::get('app_toggleLogo') || sfConfig::get('app_toggleTitle')) { ?>
      <a class="navbar-brand" href="<?php echo url_for('@homepage'); ?>" title="<?php echo __('Home'); ?>" rel="home">
        <?php if (sfConfig::get('app_toggleLogo')) { ?>
          <?php echo image_tag('logo', ['alt' => __('AtoM logo'), 'class' => 'd-inline-block']); ?>
        <?php } ?>
        <?php if (sfConfig::get('app_toggleTitle')) { ?>
          <span class="align-middle"><?php echo esc_specialchars(sfConfig::get('app_siteTitle')); ?></span>
        <?php } ?>
      </a>
    <?php } ?>
  </div>
</nav>






<header id="top-bar">

  <nav>

    <?php echo get_component('menu', 'userMenu'); ?>

    <?php echo get_component('menu', 'quickLinksMenu'); ?>

    <?php if (sfConfig::get('app_toggleLanguageMenu')) { ?>
      <?php echo get_component('menu', 'changeLanguageMenu'); ?>
    <?php } ?>

    <?php echo get_component('menu', 'clipboardMenu'); ?>

    <?php echo get_component('menu', 'mainMenu', ['sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID()]); ?>

  </nav>

  <div id="search-bar">

    <?php echo get_component('menu', 'browseMenu', ['sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID()]); ?>

    <?php echo get_component('search', 'box'); ?>

  </div>

  <?php echo get_component_slot('header'); ?>

</header>

<?php if (sfConfig::get('app_toggleDescription')) { ?>
  <div id="site-slogan">
    <div class="container">
      <div class="row">
        <div class="span12">
          <span><?php echo esc_specialchars(sfConfig::get('app_siteDescription')); ?></span>
        </div>
      </div>
    </div>
  </div>
<?php } ?>
