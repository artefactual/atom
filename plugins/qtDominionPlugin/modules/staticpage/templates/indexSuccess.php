<?php if ('home' == $resource->slug): ?>

  <div id="homepagehero" class="row">

    <div class="span8" id="mainnav">

      <p><?php echo __('Browse by') ?></p>
      <ul>
        <li><?php echo link_to(image_tag('/plugins/qtDominionPlugin/images/icons-large/icon-institutions.png', array('width' => '42', 'height' => '42')).' '.__('Institutions'), array('module' => 'repository', 'action' => 'browse')) ?></li>
        <li><?php echo link_to(image_tag('/plugins/qtDominionPlugin/images/icons-large/icon-subjects.png', array('width' => '42', 'height' => '42')).' '.__('Subjects'), array('module' => 'taxonomy', 'action' => 'browse', 'id' => 35)) ?></li>
        <li><?php echo link_to(image_tag('/plugins/qtDominionPlugin/images/icons-large/icon-people.png', array('width' => '42', 'height' => '42')).' '.__('People &amp; Organizations'), array('module' => 'actor', 'action' => 'browse')) ?></li>
        <li><?php echo link_to(image_tag('/plugins/qtDominionPlugin/images/icons-large/icon-places.png', array('width' => '42', 'height' => '42')).' '.__('Places'), array('module' => 'taxonomy', 'action' => 'browse', 'id' => 42)) ?></li>
        <li><?php echo link_to(image_tag('/plugins/qtDominionPlugin/images/icons-large/icon-media.png', array('width' => '42', 'height' => '42')).' '.__('Media'), array('module' => 'digitalobject', 'action' => 'browse')) ?></li>
        <li><?php echo link_to(image_tag('/plugins/qtDominionPlugin/images/icons-large/icon-new.png', array('width' => '42', 'height' => '42')).' '.__('Newest additions'), array('module' => 'search', 'action' => 'updates')) ?></li>
      </ul>

    </div>

    <div class="span3" id="intro">
      <h2>The Gateway to Canada's Past</h2>
      <p>This is the national database with <?php echo QubitSearch::getInstance()->index->getType('QubitRepository')->search(new Elastica_Query(new Elastica_Query_MatchAll()))->getTotalHits(); ?> archival repositories across the country and <?php echo QubitSearch::getInstance()->index->getType('QubitInformationObject')->search(new Elastica_Query(new Elastica_Query_MatchAll()))->getTotalHits(); ?> records. You can access these holdings and so much more. </p>
    </div>

  </div>

  <div id="homepage" class="row">

    <div class="span4" id="popular">
      <h3>Popular <br/><span>this week</span></h3>
      <ol>
        <?php foreach (array(
          "Playwrights' Workshop Montreal fonds" => 'playwrights-workshop-montreal-fonds',
          "Kantokoski (Koski), Koivula & Korpela Family" => 'kantokoski-koski-koivula-korpela-family-3',
          "Caledon Mountain Trout Club fonds" => 'caledon-mountain-trout-club-fonds',
          "Toronto Psychiatric Hospital/Clarke Institute of Psychiatry fonds" => 'toronto-psychiatric-hospital-clarke-institute-of-psychiatry-fonds',
          "Ann Eva Chisholm (nee Kantokoski/Koski)" => 'ann-eva-chisholm-nee-kantokoski-koski-2',
          "Soroptimist Club of the Sudbury Nickel District" => 'soroptimist-club-of-sudbury-nickel-district-3',
          "St. James' Church (Anglican), Carp, Ontario fonds" => 'st-james-church-anglican-carp-ontario-fonds',
          "Kantokoski (Koski), Koivula & Korpela Family" => 'church-records-from-finnish-congregation') as $title => $slug): ?>
          <li><?php echo link_to($title, url_for(array('module' => 'informationobject', 'slug' => $slug))) ?></li>
        <?php endforeach; ?>
      </ol>
    </div>

    <div class="span8" id="virtualexhibit">
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

<?php else: ?>

  <div class="page">

    <h1><?php echo render_title($resource->getTitle(array('cultureFallback' => true))) ?></h1>

    <div>
      <?php echo render_value($resource->getContent(array('cultureFallback' => true))) ?>
    </div>

    <?php if (SecurityCheck::hasPermission($sf_user, array('module' => 'staticpage', 'action' => 'update'))): ?>
      <div class="actions section">

        <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

        <div class="content">
          <ul class="links">
            <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'staticpage', 'action' => 'edit'), array('title' => __('Edit this page'))) ?></li>
          </ul>
        </div>

      </div>
    <?php endif; ?>

  </div>

<?php endif; ?>
