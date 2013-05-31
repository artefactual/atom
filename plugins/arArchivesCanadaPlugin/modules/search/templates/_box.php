<div id="search-form-wrapper">

  <form action="<?php echo url_for(array('module' => 'search')) ?>" data-autocomplete="<?php echo url_for(array('module' => 'search', 'action' => 'autocomplete')) ?>">

    <div class="input-append">

      <input type="text" name="query"<?php if (isset($sf_request->query)) echo ' class="focused"' ?> value="<?php echo esc_entities($sf_request->query) ?>" placeholder="<?php echo __('Search') ?>"/>

      <div class="btn-group">

        <button type="submit" class="btn" id="btn-advanced-search">
          <span class="icon-search"></span>
        </button>

        <button class="btn dropdown-toggle" data-toggle="dropdown">
          <span class="caret"></span>
        </button>
        <ul class="dropdown-menu pull-right">
          <li><?php echo link_to(image_tag('/images/icons-large/icon-archival.png', array('width' => '24', 'height' => '24')).' '.__('Archival descriptions'), array('module' => 'informationobject', 'action' => 'browse')) ?></li>
          <li><?php echo link_to(image_tag('/images/icons-large/icon-institutions.png', array('width' => '24', 'height' => '24')).' '.__('Institutions'), array('module' => 'repository', 'action' => 'browse')) ?></li>
          <li><?php echo link_to(image_tag('/images/icons-large/icon-subjects.png', array('width' => '24', 'height' => '24')).' '.__('Subjects'), array('module' => 'taxonomy', 'action' => 'browse', 'id' => 35)) ?></li>
          <li><?php echo link_to(image_tag('/images/icons-large/icon-people.png', array('width' => '24', 'height' => '24')).' '.__('People &amp; Organizations'), array('module' => 'actor', 'action' => 'browse')) ?></li>
          <li><?php echo link_to(image_tag('/images/icons-large/icon-places.png', array('width' => '24', 'height' => '24')).' '.__('Places'), array('module' => 'taxonomy', 'action' => 'browse', 'id' => 42)) ?></li>
          <li><?php echo link_to(image_tag('/images/icons-large/icon-media.png', array('width' => '24', 'height' => '24')).' '.__('Media'), array('module' => 'digitalobject', 'action' => 'browse')) ?></li>
          <li><?php echo link_to(image_tag('/images/icons-large/icon-new.png', array('width' => '24', 'height' => '24')).' '.__('Newest additions'), array('module' => 'search', 'action' => 'updates')) ?></li>
          <li class="divider"></li>
          <li class="advanced-search">
            <a href="<?php echo url_for(array('module' => 'search', 'action' => 'advanced')) ?>">
              <i class="icon-zoom-in"></i>
              <?php echo __('Advanced search') ?>
            </a>
          </li>
        </ul>

      </div>

    </div>

    <div id="search-realm" class="search-popover">

      <?php $sf_route = $sf_request->getAttribute('sf_route') ?>
      <?php if (isset($sf_route->resource)): ?>
        <?php if ($sf_route->resource instanceof QubitRepository): ?>
          <div>
            <label>
              <input name="realm" type="radio" value="<?php echo $sf_route->resource->id ?>"/>
              <?php echo __('Search <strong>%1%</strong>', array('%1%' => render_title($sf_route->resource))) ?>
            </label>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <div>
        <label>
          <input name="realm" type="radio" value="all" checked="checked">
          <?php echo __('Global search') ?>
        </label>
      </div>

      <div class="search-realm-advanced">
        <a href="<?php echo url_for(array('module' => 'search', 'action' => 'advanced')) ?>">
          <?php echo __('Advanced search') ?>&nbsp;&raquo;
        </a>
      </div>

    </div>

  </form>

</div>
