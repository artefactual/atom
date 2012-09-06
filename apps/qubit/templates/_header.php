<div id="header">
  <div class="section clearfix">

    <?php echo get_component('i18n', 'changeLanguageList') ?>

    <?php echo get_component('menu', 'quickLinks') ?>

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

      </div> <!-- /#name-and-slogan -->

    <?php endif; ?>

    <?php echo get_component_slot('header') ?>

  </div> <!-- /.section -->
</div> <!-- /#header -->

<?php echo get_component('search', 'box') ?>

<?php echo get_component('menu', 'browseMenu', array('sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID())) ?>
