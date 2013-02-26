<?php echo get_component_slot('header') ?>

<?php // echo get_component('menu', 'mainMenu') ?>
<?php // echo get_component('menu', 'quickLinks') ?>
<?php // echo get_component('i18n', 'changeLanguageList') ?>
<?php // echo get_component('menu', 'browseMenu', array('sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID())) ?>

<div id="header">

  <div class="container">

    <div class="row" id="header-lvl1">
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

    <div class="row" id="header-lvl2">

      <div id="logo-and-name" class="span6">
        <h1><?php echo link_to(image_tag('/plugins/arArchivesCanadaPlugin/images/logo'), '@homepage', array('rel' => 'home', 'title' => __('Home'))) ?></h1>
      </div>

      <div id="header-search" class="span6">

        <?php echo get_component('search', 'box') ?>

        <div id="browse-menu" class="btn-group dropdown">
          <button class="btn dropdown-toggle" data-toggle="dropdown">
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><?php echo link_to(image_tag('/images/icons-large/icon-institutions.png', array('width' => '24', 'height' => '24')).' '.__('Institutions'), array('module' => 'repository', 'action' => 'browse')) ?></li>
            <li><?php echo link_to(image_tag('/images/icons-large/icon-subjects.png', array('width' => '24', 'height' => '24')).' '.__('Subjects'), array('module' => 'taxonomy', 'action' => 'browse', 'id' => 35)) ?></li>
            <li><?php echo link_to(image_tag('/images/icons-large/icon-people.png', array('width' => '24', 'height' => '24')).' '.__('People &amp; Organizations'), array('module' => 'actor', 'action' => 'browse')) ?></li>
            <li><?php echo link_to(image_tag('/images/icons-large/icon-places.png', array('width' => '24', 'height' => '24')).' '.__('Places'), array('module' => 'taxonomy', 'action' => 'browse', 'id' => 42)) ?></li>
            <li><?php echo link_to(image_tag('/images/icons-large/icon-media.png', array('width' => '24', 'height' => '24')).' '.__('Media'), array('module' => 'digitalobject', 'action' => 'browse')) ?></li>
            <li><?php echo link_to(image_tag('/images/icons-large/icon-new.png', array('width' => '24', 'height' => '24')).' '.__('Newest additions'), array('module' => 'search', 'action' => 'updates')) ?></li>
          </ul>
        </div>

      </div>

    </div>

  </div>

</div>
