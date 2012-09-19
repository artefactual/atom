<div id="header">

  <div class="container">

    <?php if ($sf_user->isAuthenticated()): ?>
      <div class="row" id="header-admin">
        <div class="span12">
          <div class="navbar navbar-fixed-top navbar-inverse">
            <div class="navbar-inner">
              <ul class="nav">

                <li class="active"><a href="#">Home</a></li>
                <li><a href="#">Link</a></li>
                <li><a href="#">Link</a></li>

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

  </div>

</div>
