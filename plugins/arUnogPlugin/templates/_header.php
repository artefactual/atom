<?php echo get_component('default', 'privacyMessage'); ?>

<?php echo get_component('default', 'updateCheck'); ?>

<?php if ($sf_user->isAdministrator() && '' === (string) QubitSetting::getByName('siteBaseUrl')) { ?>
  <div class="alert alert-primary rounded-0 text-center mb-0" role="alert">
    <?php echo link_to(__('Please configure your site base URL'), 'settings/siteInformation', ['class' => 'alert-link']); ?>
  </div>
<?php } ?>

<div id="brand-bar" class="container-fluid">
  <div class="row">
    <div class="px-3 py-2 px-lg-5 bg-unog-light-grey border-bottom border-5 border-primary">
      <i class="fas fa-home me-1" aria-hidden="true"></i>
      <?php echo link_to(__('Welcome to the United Nations'), 'https://www.un.org/en/', ['class' => 'text-secondary text-decoration-none']); ?>
    </div>
  </div>
</div>

<div id="logos" class="container-fluid">
  <div class="row py-2">
    <div class="col-6 text-end border-end border-2">
      <?php echo image_tag('/plugins/arUnogPlugin/images/unog-logo.jpg', ['alt' => __('United Nations Geneva logo'), 'class' => 'img-fluid']); ?>
    </div>
    <div class="col-6 text-start border-start border-2">
      <?php echo link_to(image_tag('/plugins/arUnogPlugin/images/library-logo.png', ['alt' => __('Library & archives logo'), 'class' => 'img-fluid']), '@homepage'); ?>
    </div>
  </div>
</div>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
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
      <?php if (!('staticpage' == $sf_context->getModuleName() && 'home' == $sf_context->getActionName())) { ?>
        <div class="d-flex flex-wrap flex-lg-nowrap flex-grow-1">
          <?php echo get_component('menu', 'browseMenu', ['sf_cache_key' => 'dominion-b5'.$sf_user->getCulture().$sf_user->getUserID()]); ?>
          <?php echo get_component('search', 'box'); ?>
        </div>
      <?php } ?>
      <div class="d-flex flex-nowrap flex-column flex-lg-row align-items-strech align-items-lg-center">
        <ul class="navbar-nav mx-lg-2">
          <li class="nav-item d-flex flex-column">
            <a class="nav-link p-0" href="<?php echo url_for('@homepage'); ?>" role="button">
              <i class="fas fa-2x fa-home px-0 px-lg-2 py-2" aria-hidden="true"></i>
              <span class="d-lg-none ms-2" aria-hidden="true"><?php echo __('Home'); ?></span>
              <span class="visually-hidden"><?php echo __('Home'); ?></span>
            </a>
          </li>
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
</nav>

<?php if (sfConfig::get('app_toggleDescription') && !empty(sfConfig::get('app_siteDescription'))) { ?>
  <div class="bg-secondary text-white">
    <div class="container-xl py-1">
      <?php echo esc_specialchars(sfConfig::get('app_siteDescription')); ?>
    </div>
  </div>
<?php } ?>
