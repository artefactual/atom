<header id="top-bar">

  <?php if (sfConfig::get('app_toggleLogo')): ?>
    <?php echo link_to(image_tag('logo'), '@homepage', array('id' => 'logo', 'rel' => 'home')) ?>
  <?php endif; ?>

  <?php if (sfConfig::get('app_toggleTitle')): ?>
    <h1 id="site-name">
      <?php echo link_to('<span>'.sfConfig::get('app_siteTitle').'</span>', '@homepage', array('rel' => 'home', 'title' => __('Home'))) ?>
    </h1>
  <?php endif; ?>

  <?php // echo get_component('search', 'box') ?>

  <nav>

    <?php if ($sf_user->isAuthenticated()): ?>
      <?php echo link_to(__('Log out'), array('module' => 'user', 'action' => 'logout'), array('class' => 'top-item top-button')) ?>
      <?php $gravatar = image_tag(url_for('https://www.gravatar.com/avatar/'.md5(strtolower(trim($sf_user->user->email))).'?s=25&d='.urlencode(public_path('/images/gravatar-anonymous.png', true)))) ?>
    <?php else: ?>
      <?php echo link_to(__('Log in'), array('module' => 'user', 'action' => 'login'), array('class' => 'top-item top-button')) ?>
    <?php endif; ?>

    <?php // echo get_component('i18n', 'changeLanguageList') ?>

    <?php // echo get_component('menu', 'quickLinks') ?>

    <?php echo get_component('menu', 'browseMenu', array('sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID())) ?>

  </nav>

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