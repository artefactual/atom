<?php echo get_component('default', 'privacyMessage'); ?>

<?php echo get_component('default', 'updateCheck'); ?>

<?php if ($sf_user->isAdministrator() && '' === (string) QubitSetting::getByName('siteBaseUrl')) { ?>
  <div class="site-warning text-center p-1">
    <?php echo link_to(__('Please configure your site base URL'), 'settings/siteInformation'); ?>
  </div>
<?php } ?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <?php if (sfConfig::get('app_toggleLogo') || sfConfig::get('app_toggleTitle')) { ?>
      <a class="navbar-brand d-flex flex-wrap flex-lg-nowrap align-items-center" href="<?php echo url_for('@homepage'); ?>" title="<?php echo __('Home'); ?>" rel="home">
        <?php if (sfConfig::get('app_toggleLogo')) { ?>
          <?php echo image_tag('/plugins/arDominionB5Plugin/images/logo', ['alt' => __('AtoM logo'), 'class' => 'd-inline-block me-2']); ?>
        <?php } ?>
        <?php if (sfConfig::get('app_toggleTitle')) { ?>
          <span class="align-middle"><?php echo esc_specialchars(sfConfig::get('app_siteTitle')); ?></span>
        <?php } ?>
      </a>
    <?php } ?>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-content" aria-controls="navbar-content" aria-expanded="false" aria-label="<?php echo __('Toggle navigation'); ?>">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse flex-wrap justify-content-end" id="navbar-content">
      <div class="d-flex flex-wrap flex-lg-nowrap me-auto mt-2 mt-lg-0">
        <?php echo get_component('menu', 'browseMenu', ['sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID()]); ?>
        <form class="d-flex mb-2 mb-lg-0">
          <div class="input-group flex-nowrap">
            <input class="form-control form-control-sm" type="search" placeholder="<?php echo __('Search'); ?>" aria-label="<?php echo __('Search'); ?>">
            <button class="btn btn-sm btn-secondary" type="submit">
              <i class="fas fa-search" aria-hidden="true"></i>
              <span class="sr-only"><?php echo __('Search'); ?></span>
            </button>
          </div>
        </form>
      </div>
      <div class="d-flex flex-nowrap flex-column flex-lg-row align-items-strech align-items-lg-center">
        <ul class="navbar-nav ms-lg-2">
          <?php echo get_component('menu', 'mainMenu', ['sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID()]); ?>
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
</nav>

<?php if (sfConfig::get('app_toggleDescription')) { ?>
  <div class="bg-secondary text-white">
    <div class="container-xl py-1">
      <?php echo esc_specialchars(sfConfig::get('app_siteDescription')); ?>
    </div>
  </div>
<?php } ?>
