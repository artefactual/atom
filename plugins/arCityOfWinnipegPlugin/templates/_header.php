<?php echo get_component('default', 'updateCheck') ?>

<?php if ('production' !== getenv('ATOM_ENVIRONMENT')): ?>

<?php endif; ?>
<div id="top-bar-container">
  <header id="top-bar">
    <div class="winn-bar-container">
      <div class="winn-bar">
        <?php if (sfConfig::get('app_toggleLogo')): ?>
          <?php echo link_to(image_tag('/plugins/arCityOfWinnipegPlugin/images/logo.png'), '@homepage', array('id' => 'logo', 'rel' => 'home')) ?>
        <?php endif; ?>

        <?php if (sfConfig::get('app_toggleTitle')): ?>
          <h1 id="site-name">
            <?php echo link_to('<span>'.sfConfig::get('app_siteTitle').'</span>', '@homepage', array('rel' => 'home', 'title' => __('Home'))) ?>
          </h1>
        <?php endif; ?>

        <?php echo link_to(image_tag('/plugins/arCityOfWinnipegPlugin/images/city_of_winnipeg_extratext.png'), '@homepage', array('id' => 'logo-city', 'rel' => 'home')) ?>
      </div>
    </div>

    <div class="search-bar-container">
      <div id="search-bar">
        <?php echo get_component('menu', 'browseMenu', array('sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID())) ?>
        <?php echo get_component('search', 'box') ?>

        <nav>
          <?php if ('production' !== getenv('ATOM_ENVIRONMENT')): ?>
            <?php echo get_component('menu', 'userMenu') ?>
          <?php endif; ?>

          <?php echo get_component('menu', 'quickLinksMenu') ?>

          <?php if (sfConfig::get('app_toggleLanguageMenu')): ?>
            <?php echo get_component('menu', 'changeLanguageMenu') ?>
          <?php endif; ?>

          <?php echo get_component('menu', 'clipboardMenu') ?>

          <?php if ($sf_user->isAuthenticated()): ?>
            <?php if ($sf_user->user->username !== 'readingroom'): ?>
              <?php echo get_component('menu', 'mainMenu', array('sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID())) ?>
            <?php endif; ?>
          <?php endif; ?>

        </nav>
      </div>
    </div>

    <?php echo get_component_slot('header') ?>

  </header>
</div>

<?php if (sfConfig::get('app_toggleDescription')): ?>
  <div id="site-slogan">
    <div class="container">
      <div class="row">
        <div class="span12">
          <span><?php echo sfConfig::get('app_siteDescription') ?></span>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>
