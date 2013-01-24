<br />
<br />
<br />

<p><?php echo __('Browse by') ?></p>

<ul>
  <li><?php echo link_to(image_tag('/images/icons-large/icon-institutions.png', array('width' => '42', 'height' => '42')).' '.__('Institutions'), array('module' => 'repository', 'action' => 'browse')) ?></li>
  <li><?php echo link_to(image_tag('/images/icons-large/icon-subjects.png', array('width' => '42', 'height' => '42')).' '.__('Subjects'), array('module' => 'taxonomy', 'action' => 'browse', 'id' => 35)) ?></li>
  <li><?php echo link_to(image_tag('/images/icons-large/icon-people.png', array('width' => '42', 'height' => '42')).' '.__('People &amp; Organizations'), array('module' => 'actor', 'action' => 'browse')) ?></li>
  <li><?php echo link_to(image_tag('/images/icons-large/icon-places.png', array('width' => '42', 'height' => '42')).' '.__('Places'), array('module' => 'taxonomy', 'action' => 'browse', 'id' => 42)) ?></li>
  <li><?php echo link_to(image_tag('/images/icons-large/icon-media.png', array('width' => '42', 'height' => '42')).' '.__('Media'), array('module' => 'digitalobject', 'action' => 'browse')) ?></li>
  <li><?php echo link_to(image_tag('/images/icons-large/icon-new.png', array('width' => '42', 'height' => '42')).' '.__('Newest additions'), array('module' => 'search', 'action' => 'updates')) ?></li>
</ul>
