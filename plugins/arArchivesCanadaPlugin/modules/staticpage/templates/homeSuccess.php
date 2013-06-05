<div id="homepage-hero" class="row">

  <div class="span8" id="homepage-nav">

    <p><?php echo __('Browse by') ?></p>
    <ul>
      <li><?php echo link_to(image_tag('/images/icons-large/icon-archival.png', array('width' => '42', 'height' => '42')).' '.__('Archival descriptions'), array('module' => 'informationobject', 'action' => 'browse')) ?></li>
      <li><?php echo link_to(image_tag('/images/icons-large/icon-institutions.png', array('width' => '42', 'height' => '42')).' '.__('Institutions'), array('module' => 'repository', 'action' => 'browse')) ?></li>
      <li><?php echo link_to(image_tag('/images/icons-large/icon-subjects.png', array('width' => '42', 'height' => '42')).' '.__('Subjects'), array('module' => 'taxonomy', 'action' => 'browse', 'id' => 35)) ?></li>
      <li><?php echo link_to(image_tag('/images/icons-large/icon-people.png', array('width' => '42', 'height' => '42')).' '.__('People &amp; Organizations'), array('module' => 'actor', 'action' => 'browse')) ?></li>
      <li><?php echo link_to(image_tag('/images/icons-large/icon-places.png', array('width' => '42', 'height' => '42')).' '.__('Places'), array('module' => 'taxonomy', 'action' => 'browse', 'id' => 42)) ?></li>
      <li><?php echo link_to(image_tag('/images/icons-large/icon-media.png', array('width' => '42', 'height' => '42')).' '.__('Media'), array('module' => 'digitalobject', 'action' => 'browse')) ?></li>
    </ul>

  </div>

  <div class="span3" id="intro">
    <h2>The Gateway to Canada's Past</h2>
    <?php if ('fr' == $sf_user->getCulture()): ?>
      <p>Through this gateway, search for details (descriptions) about archival materials, find digital images, visit virtual exhibits, browse information about archives in every province and territory, and discover the archives with the information you need. ArchivesCanada.ca is your national portal to Canada's documentary heritage, found in over 800 archives.</p>
    <?php else: ?>
      <p>Through this gateway, search for details (descriptions) about archival materials, find digital images, visit virtual exhibits, browse information about archives in every province and territory, and discover the archives with the information you need. ArchivesCanada.ca is your national portal to Canada's documentary heritage, found in over 800 archives.</p>
    <?php endif; ?>
  </div>

</div>

<div id="homepage" class="row">

  <div class="span4">
    <?php echo get_component('default', 'popular', array('limit' => 10)) ?>
  </div>

  <div class="span8" id="virtual-exhibit">
    <a href="http://scaa.usask.ca/gallery/northern/dommasch/">
      <h3>
        Virtual Exhibits<br />
        <span class="title">Hans S. Dommasch: Canada North of 60</span>
        <span class="small">University of Saskatchewan Archives</span>
      </h3>
      <div>&nbsp;</div>
    </a>
  </div>

</div>
