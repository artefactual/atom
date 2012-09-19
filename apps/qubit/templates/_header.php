<div id="header">

  <div class="container">

    <?php // echo get_component('i18n', 'changeLanguageList') ?>
    <?php // echo get_component('menu', 'quickLinks') ?>
    <?php // echo get_component('menu', 'browseMenu', array('sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID())) ?>
    <?php // echo get_component_slot('header') ?>

    <?php if ($sf_user->isAuthenticated()): ?>
      <div class="row" id="header-admin">
        <div class="span12">
          <div class="navbar navbar-fixed-top navbar-inverse">
            <div class="navbar-inner">
              <ul class="nav">
                <?php echo get_component('menu', 'mainMenu') ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <div class="row" id="header-lvl1">
      <div class="span12">
        <a id="header-council" href="http://www.cdncouncilarchives.ca/"><?php echo image_tag('/plugins/qtDominionPlugin/images/council.png', array('width' => '156', 'height' => '42')) ?></a>
        <ul id="header-nav" class="nav nav-pills">
          <li><?php echo link_to(__('Home'), '@homepage') ?></li>
          <li><?php echo link_to(__('Sitemap'), array('module' => 'staticpage', 'slug' => 'sitemap')) ?></li>
          <li><?php echo link_to(__('Contact us'), array('module' => 'staticpage', 'slug' => 'contact')) ?></li>
          <li><?php echo link_to(__('Log in'), array('module' => 'user', 'action' => 'login')) ?></li>
          <?php foreach (array('en', 'fr') as $item): ?>
            <?php if ($sf_user->getCulture() != $item): ?>
              <li><?php echo link_to(format_language($item, $item), array('sf_culture' => $item) + $sf_request->getParameterHolder()->getAll()) ?></li>
              <?php break; ?>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div class="row" id="header-lvl2">
      <?php // Restore old snippet (settings: app_toggleLogo, app_toggleTitle, app_toggleDescription, app_sitleTitle, app_sitleDescription) ?>
      <div id="logo-and-name" class="span6">
        <h1><?php echo link_to(image_tag('/plugins/qtDominionPlugin/images/logo'), '@homepage', array('rel' => 'home', 'title' => __('Home'))) ?></h1>
      </div>
      <div id="header-search">
        <?php echo get_component('search', 'box') ?>
      </div>
    </div>

    <?php if (false): ?>
    <div class="row">
      <?php if (sfConfig::get('app_toggleLogo')): ?>
        <?php echo link_to(image_tag('logo', array('alt' => __('Home'))), '@homepage', array('id' => 'logo', 'rel' => 'home', 'title' => __('Home'))) ?>
      <?php endif; ?>
      <?php if (sfConfig::get('app_toggleTitle') || sfConfig::get('app_toggleDescription')): ?>
        <div id="name-and-slogan">
          <?php if (sfConfig::get('app_toggleTitle')): ?>
            <h1 id="site-name">
              <?php echo link_to('<span>'.sfConfig::get('app_siteTitle').'</span>', '@homepage', array('rel' => 'home', 'title' => __('Home'))) ?>
            </h1>
          <?php endif; ?>
          <?php if (sfConfig::get('app_toggleDescription')): ?>
            <div id="site-slogan">
              <?php echo sfConfig::get('app_siteDescription') ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>


  </div>

</div>
