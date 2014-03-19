<?php echo get_component('default', 'updateCheck') ?>

<header id="top-bar">

  <h1 id="site-name">
    <?php echo link_to('<span>DRMC-MA</span>', '@homepage', array('id' => 'logo', 'rel' => 'home', 'title' => __('Home'))) ?>
  </h1>

  <nav>

    <?php echo get_component('menu', 'userMenu') ?>

    <?php echo get_component('menu', 'quickLinksMenu') ?>

    <?php echo get_component('menu', 'changeLanguageMenu') ?>

    <?php echo get_component('menu', 'mainMenu', array('sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID())) ?>

  </nav>

  <div id="search-bar">

    <?php echo get_component('search', 'box') ?>

    <?php echo get_component('menu', 'browseMenu', array('sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID())) ?>

  </div>

  </section>

  <?php echo get_component_slot('header') ?>

</header>

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
