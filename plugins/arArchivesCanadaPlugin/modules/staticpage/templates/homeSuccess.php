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
      <li><?php echo link_to(image_tag('/images/icons-large/icon-new.png', array('width' => '42', 'height' => '42')).' '.__('Newest additions'), array('module' => 'search', 'action' => 'updates')) ?></li>
    </ul>

  </div>

  <div class="span3" id="intro">
    <h2>The Gateway to Canada's Past</h2>
    <p>Praesent mollis, lorem ornare rhoncus luctus, tortor felis sagittis dolor, vel sodales velit nulla at justo. Praesent in posuere dui. Suspendisse eu malesuada augue. Nullam urna tortor, ultrices sed adipiscing et, dictum ac est. Cras et justo eu tellus porta eleifend. Nulla at arcu vel arcu congue pellentesque. Fusce id leo odio, non tincidunt dui.</p>
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
