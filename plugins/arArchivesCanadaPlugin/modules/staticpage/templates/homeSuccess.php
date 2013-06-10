<div id="homepage-hero" class="row">

  <div class="span8" id="homepage-nav">

    <p><?php echo __('Browse by') ?></p>
    <ul>
      <li><?php echo link_to(image_tag('/images/icons-large/icon-archival.png', array('width' => '42', 'height' => '42')).' '.sfConfig::get('app_ui_label_informationobject'), array('module' => 'informationobject', 'action' => 'browse')) ?></li>
      <li><?php echo link_to(image_tag('/images/icons-large/icon-institutions.png', array('width' => '42', 'height' => '42')).' '.sfConfig::get('app_ui_label_repository'), array('module' => 'repository', 'action' => 'browse')) ?></li>
      <li><?php echo link_to(image_tag('/images/icons-large/icon-subjects.png', array('width' => '42', 'height' => '42')).' '.sfConfig::get('app_ui_label_subject'), array('module' => 'taxonomy', 'action' => 'browse', 'id' => 35)) ?></li>
      <li><?php echo link_to(image_tag('/images/icons-large/icon-people.png', array('width' => '42', 'height' => '42')).' '.sfConfig::get('app_ui_label_actor'), array('module' => 'actor', 'action' => 'browse')) ?></li>
      <li><?php echo link_to(image_tag('/images/icons-large/icon-places.png', array('width' => '42', 'height' => '42')).' '.sfConfig::get('app_ui_label_place'), array('module' => 'taxonomy', 'action' => 'browse', 'id' => 42)) ?></li>
      <li><?php echo link_to(image_tag('/images/icons-large/icon-media.png', array('width' => '42', 'height' => '42')).' '.sfConfig::get('app_ui_label_digitalobject'), array('module' => 'digitalobject', 'action' => 'browse')) ?></li>
    </ul>

  </div>

  <div class="span3" id="intro">
    <?php if ('fr' == $sf_user->getCulture()): ?>
      <h2>
        <span class="title">ArchivesCanada</span>
        Votre accèss à l’histoire du Canada
      </h2>
      <p>ARCHIVESCANADA.ca est votre portail à les ressources archivistiques à travers le Canada:<br />Par l'entremise de cet portail, vous pouvez faire une recherche pour les details (descriptions archivistiques), visionnez des photographies, des cartes, ou d’autres documents numérisés ainsi que des expositions virtuelles, et découvrir les dépôts d’archives avec l’information que vous avez besoin. ArchivesCanada.com est votre portail archivistique nationale à la patrimoine documentaire du Canada que l’on retrouve dans plus de 800 dépôts.</p>
    <?php else: ?>
      <h2>
        <span class="title">ArchivesCanada</span>
        The Gateway to Canada's Past
      </h2>
      <p>ARCHIVESCANADA.ca is your gateway to resources in archives across Canada:<br />Through this gateway, search for details (descriptions) about archival materials, find digital images, visit virtual exhibits, browse information about archives in every province and territory, and discover the archives with the information you need.  ArchivesCanada.ca is your national portal to Canada's documentary heritage, found in over 800 archives.</p>
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
