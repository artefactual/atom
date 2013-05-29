<?php echo get_component_slot('header') ?>

<?php // echo get_component('menu', 'mainMenu') ?>
<?php // echo get_component('menu', 'quickLinks') ?>
<?php // echo get_component('i18n', 'changeLanguageList') ?>
<?php // echo get_component('menu', 'browseMenu', array('sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID())) ?>

<div id="header">

  <div class="container">

    <div id="header-lvl1">
      <div class="row">
        <div class="span12">
          <a id="header-council" href="http://www.cdncouncilarchives.ca/"><?php echo image_tag('/plugins/arArchivesCanadaPlugin/images/council.png', array('width' => '156', 'height' => '42')) ?></a>
          <ul id="header-nav" class="nav nav-pills">

            <li><?php echo link_to(__('Home'), '@homepage') ?></li>

            <li><?php echo link_to(__('Contact us'), array('module' => 'staticpage', 'slug' => 'contact')) ?></li>

            <?php foreach (array('en', 'fr') as $item): ?>
              <?php if ($sf_user->getCulture() != $item): ?>
                <li><?php echo link_to(format_language($item, $item), array('sf_culture' => $item) + $sf_request->getParameterHolder()->getAll()) ?></li>
                <?php break; ?>
              <?php endif; ?>
            <?php endforeach; ?>

          </ul>
        </div>
      </div>
    </div>

    <div id="header-lvl2">
      <div class="row">

        <div id="logo-and-name" class="span6">
          <h1><?php echo link_to(image_tag('/plugins/arArchivesCanadaPlugin/images/logo.png'), '@homepage', array('rel' => 'home')) ?></h1>
        </div>

        <div id="header-search" class="span6">
          <?php echo get_component('search', 'box') ?>
        </div>

      </div>
    </div>

  </div>

</div>
