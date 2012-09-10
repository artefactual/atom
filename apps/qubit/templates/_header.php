<div id="header">

  <?php echo get_component('search', 'box') ?>

  <div class="container">

    <div class="row">
      <div class="span12">
        <ul id="header-nav" class="nav nav-pills">
          <?php // echo get_component('i18n', 'changeLanguageList') ?>
          <?php // echo get_component('menu', 'quickLinks') ?>
          <?php // echo get_component('menu', 'browseMenu', array('sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID())) ?>
          <?php // echo get_component_slot('header') ?>
        </ul>
      </div>
    </div>

    <div class="row">
      <?php if (false): ?>
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
      <?php endif; ?>
    </div>

  </div>

</div>
