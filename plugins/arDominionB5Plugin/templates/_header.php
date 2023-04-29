<div class="visually-hidden-focusable p-3 border-bottom">
  <a class="btn btn-sm btn-secondary" href="#main-column">
    <?php echo __('Skip to main content'); ?>
  </a>
</div>
    
<?php echo get_component('default', 'privacyMessage'); ?>

<?php echo get_component('default', 'updateCheck'); ?>

<?php if ($sf_user->isAdministrator() && '' === (string) QubitSetting::getByName('siteBaseUrl')) { ?>
  <div class="alert alert-warning rounded-0 text-center mb-0" role="alert">
    <?php echo link_to(__('Please configure your site base URL'), 'settings/siteInformation', ['class' => 'alert-link']); ?>
  </div>
<?php } ?>

<header id="top-bar" class="navbar navbar-expand-lg navbar-dark bg-dark" role="navigation" aria-label="<?php echo __('Main navigation'); ?>">
  <div class="container-fluid">
    <?php if (sfConfig::get('app_toggleLogo') || sfConfig::get('app_toggleTitle')) { ?>
      <a class="navbar-brand d-flex flex-wrap flex-lg-nowrap align-items-center py-0 me-0" href="<?php echo url_for('@homepage'); ?>" title="<?php echo __('Home'); ?>" rel="home">
        <?php if (sfConfig::get('app_toggleLogo')) { ?>
          <?php echo image_tag('/plugins/arDominionB5Plugin/images/logo', ['alt' => __('AtoM logo'), 'class' => 'd-inline-block my-2 me-3', 'height' => '35']); ?>
        <?php } ?>
        <?php if (sfConfig::get('app_toggleTitle') && !empty(sfConfig::get('app_siteTitle'))) { ?>
          <span class="text-wrap my-1 me-3"><?php echo esc_specialchars(sfConfig::get('app_siteTitle')); ?></span>
        <?php } ?>
      </a>
    <?php } ?>
    <button class="navbar-toggler atom-btn-secondary my-2 me-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-content" aria-controls="navbar-content" aria-expanded="false">
      <span class="navbar-toggler-icon"></span>
      <span class="visually-hidden"><?php echo __('Toggle navigation'); ?></span>
    </button>
    <div class="collapse navbar-collapse flex-wrap justify-content-end me-1" id="navbar-content">
      <div class="d-flex flex-wrap flex-lg-nowrap flex-grow-1">
        <?php echo get_component('menu', 'browseMenu', ['sf_cache_key' => 'dominion-b5'.$sf_user->getCulture().$sf_user->getUserID()]); ?>
        <?php echo get_component('search', 'box'); ?>
      </div>
      <div class="d-flex flex-nowrap flex-column flex-lg-row align-items-strech align-items-lg-center">
        <ul class="navbar-nav mx-lg-2">
          <?php echo get_component('menu', 'mainMenu', ['sf_cache_key' => 'dominion-b5'.$sf_user->getCulture().$sf_user->getUserID()]); ?>
          <?php echo get_component('menu', 'clipboardMenu'); ?>
          <?php if (sfConfig::get('app_toggleLanguageMenu')) { ?>
            <?php echo get_component('menu', 'changeLanguageMenu'); ?>
          <?php } ?>
          <?php echo get_component('menu', 'quickLinksMenu'); ?>
        </ul>
        <?php echo get_component('menu', 'userMenu'); ?>
      </div>
    </div>
  </div>
</header>

<?php if (sfConfig::get('app_toggleDescription') && !empty(sfConfig::get('app_siteDescription'))) { ?>
  <div class="bg-secondary text-white">
    <div class="container-xl py-1">
      <?php echo esc_specialchars(sfConfig::get('app_siteDescription')); ?>
    </div>
  </div>
<?php } ?>
