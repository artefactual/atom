<div id="homepage-hero" class="row">

  <?php $cacheKey = 'homepage-nav-'.$sf_user->getCulture() ?>
  <?php if (!cache($cacheKey)): ?>
    <div class="span8" id="homepage-nav">
      <p><?php echo __('Browse by') ?></p>
      <ul>
        <?php $icons = array(
          'browseInformationObjects' => '/images/icons-large/icon-archival.png',
          'browseActors' => '/images/icons-large/icon-people.png',
          'browseRepositories' => '/images/icons-large/icon-institutions.png',
          'browseSubjects' => '/images/icons-large/icon-subjects.png',
          'browseFunctions' => '/images/icons-large/icon-functions.png',
          'browsePlaces' => '/images/icons-large/icon-places.png',
          'browseDigitalObjects' => '/images/icons-large/icon-media.png') ?>
        <?php $browseMenu = QubitMenu::getById(QubitMenu::BROWSE_ID) ?>
        <?php if ($browseMenu->hasChildren()): ?>
          <?php foreach ($browseMenu->getChildren() as $item): ?>
            <li>
              <a href="<?php echo url_for($item->getPath(array('getUrl' => true, 'resolveAlias' => true))) ?>">
                <?php if (isset($icons[$item->name])): ?>
                  <?php echo image_tag($icons[$item->name], array('width' => 42, 'height' => 42, 'alt' => '')) ?>
                <?php endif; ?>
                <?php echo esc_specialchars($item->getLabel(array('cultureFallback' => true))) ?>
              </a>
            </li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    </div>
    <?php cache_save($cacheKey) ?>
  <?php endif; ?>

  <div class="span3" id="intro">
    <?php if ('fr' == $sf_user->getCulture()): ?>
      <h2>
        <span class="title">ARCHIVESCANADA.ca</span>
        Votre accès à l’histoire du Canada
      </h2>
      <p>ARCHIVESCANADA.ca est un portail vous donnant accès à des ressources archivistiques à travers le Canada:<br />Par l'entremise de ce portail, vous pouvez faire une recherche dans les descriptions de documents d'archives, visionner des photographies, des cartes, ou d’autres documents numérisés ainsi que visiter des expositions virtuelles, et découvrir les dépôts d’archives qui détient l’information dont vous avez besoin. ARCHIVESCANADA.ca est votre portail archivistique national pour découvrir le patrimoine documentaire du Canada que l’on retrouve dans plus de 800 dépôts d'archives.</p>
    <?php else: ?>
      <h2>
        <span class="title">ARCHIVESCANADA.ca</span>
        The Gateway to Canada's Past
      </h2>
      <p>ARCHIVESCANADA.ca is your gateway to resources in archives across Canada:<br />Through this gateway, search descriptions of archival materials, find digital images, visit virtual exhibits, browse information about archives in every province and territory, and discover the archives with the information you need. ARCHIVESCANADA.ca is your national portal to Canada's documentary heritage, found in over 800 archives.</p>
    <?php endif; ?>
  </div>

</div>

<div id="homepage" class="row">

  <div class="span4">
    <?php echo get_component('default', 'popular', array('limit' => 10, 'sf_cache_key' => $sf_user->getCulture())) ?>
  </div>

  <div class="span8" id="virtual-exhibit">
    <a href="http://scaa.usask.ca/gallery/northern/dommasch/">
      <h3>
        <?php echo __('Virtual exhibits') ?><br />
        <span class="title">Hans S. Dommasch: Canada North of 60</span>
        <span class="small">University of Saskatchewan Archives</span>
      </h3>
      <div>&nbsp;</div>
    </a>
  </div>

</div>
