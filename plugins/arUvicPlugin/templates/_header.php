<?php echo get_component('default', 'updateCheck') ?>

<?php if ($sf_user->isAdministrator() && (string)QubitSetting::getByName('siteBaseUrl') === ''): ?>
  <div id="update-check">
    <?php echo link_to('Please configure your site base URL', 'settings/siteInformation', array('rel' => 'home', 'title' => __('Home'))) ?>
  </div>
<?php endif; ?>

<header>

  <div class="ccap-top-bar">

    <?php if (sfConfig::get('app_toggleTitle')): ?>
      <h1 id="site-name">
        <?php echo link_to('<span>'.esc_specialchars(sfConfig::get('app_siteTitle')).'</span>', '@homepage', array('rel' => 'home', 'title' => __('Home'))) ?>
      </h1>
    <?php endif; ?>

    <nav>

      <?php echo get_component('menu', 'userMenu') ?>

      <?php echo get_component('menu', 'quickLinksMenu') ?>

      <?php echo get_component('menu', 'changeLanguageMenu') ?>

      <?php echo get_component('menu', 'mainMenu', array('sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID())) ?>

    </nav>

    <div id="search-bar">

      <?php echo get_component('menu', 'browseMenu', array('sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID())) ?>

      <?php echo get_component('search', 'box') ?>

    </div>

  </div>

  <div class="ccap-navigation-bar">

      <ul>
        <?php $browseMenu = QubitMenu::getById(QubitMenu::BROWSE_ID) ?>
        <?php if ($browseMenu->hasChildren()): ?>
          <?php foreach ($browseMenu->getChildren() as $item): ?>
            <li>
              <a href="<?php echo url_for($item->getPath(array('getUrl' => true, 'resolveAlias' => true))) ?>">
                <?php echo esc_specialchars(ucfirst($item->getLabel(array('cultureFallback' => true)))) ?>
              </a>
            </li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>

  </div>

  <?php echo get_component_slot('header') ?>

</header>
