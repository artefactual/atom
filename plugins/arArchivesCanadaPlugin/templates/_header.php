<?php echo get_component_slot('header') ?>

<?php echo get_component('menu', 'mainMenu') ?>

<div id="header">

  <div class="container">

    <div class="row" id="header-lvl1">
      <div class="span12">
        <a id="header-council" href="http://www.cdncouncilarchives.ca/"><?php echo image_tag('/plugins/arDominionPlugin/images/council.png', array('width' => '156', 'height' => '42')) ?></a>
        <ul id="header-nav" class="nav nav-pills">

          <?php // echo get_component('menu', 'quickLinks') ?>

          <li><?php echo link_to(__('Home'), '@homepage') ?></li>
          <li><?php echo link_to(__('Contact us'), array('module' => 'staticpage', 'slug' => 'contact')) ?></li>

          <?php if ($sf_user->isAuthenticated()): ?>
            <li><?php echo link_to(__('Log out'), array('module' => 'user', 'action' => 'logout')) ?></li>
          <?php else: ?>
            <li><?php echo link_to(__('Log in'), array('module' => 'user', 'action' => 'login')) ?></li>
          <?php endif; ?>

          <?php // echo get_component('i18n', 'changeLanguageList') ?>
          <?php foreach (array('en', 'fr') as $item): ?>
            <?php if ($sf_user->getCulture() != $item): ?>
              <li><?php echo link_to(format_language($item, $item), array('sf_culture' => $item) + $sf_request->getParameterHolder()->getAll()) ?></li>
              <?php break; ?>
            <?php endif; ?>
          <?php endforeach; ?>

          <?php // echo get_component('menu', 'browseMenu', array('sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID())) ?>
          <li id="header-browser">
            <div class="btn-group">
              <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><?php echo __('Browse') ?></a>
              <ul class="dropdown-menu">
                <li><?php echo link_to(image_tag('/images/icons-large/icon-institutions.png', array('width' => '24', 'height' => '24')).' '.__('Institutions'), array('module' => 'repository', 'action' => 'browse')) ?></li>
                <li><?php echo link_to(image_tag('/images/icons-large/icon-subjects.png', array('width' => '24', 'height' => '24')).' '.__('Subjects'), array('module' => 'taxonomy', 'action' => 'browse', 'id' => 35)) ?></li>
                <li><?php echo link_to(image_tag('/images/icons-large/icon-people.png', array('width' => '24', 'height' => '24')).' '.__('People &amp; Organizations'), array('module' => 'actor', 'action' => 'browse')) ?></li>
                <li><?php echo link_to(image_tag('/images/icons-large/icon-places.png', array('width' => '24', 'height' => '24')).' '.__('Places'), array('module' => 'taxonomy', 'action' => 'browse', 'id' => 42)) ?></li>
                <li><?php echo link_to(image_tag('/images/icons-large/icon-media.png', array('width' => '24', 'height' => '24')).' '.__('Media'), array('module' => 'digitalobject', 'action' => 'browse')) ?></li>
                <li><?php echo link_to(image_tag('/images/icons-large/icon-new.png', array('width' => '24', 'height' => '24')).' '.__('Newest additions'), array('module' => 'search', 'action' => 'updates')) ?></li>
              </ul>
            </div>
          </li>

        </ul>
      </div>
    </div>

    <div class="row" id="header-lvl2">
      <?php // Restore old snippet (settings: app_toggleLogo, app_toggleTitle, app_toggleDescription, app_sitleTitle, app_sitleDescription) ?>
      <div id="logo-and-name" class="span6">
        <h1><?php echo link_to(image_tag('/plugins/arDominionPlugin/images/logo'), '@homepage', array('rel' => 'home', 'title' => __('Home'))) ?></h1>
      </div>
      <div id="header-search" class="span6">
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
