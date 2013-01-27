<div id="search-form-wrapper">

  <form action="<?php echo url_for(array('module' => 'search')) ?>" data-autocomplete="<?php echo url_for(array('module' => 'search', 'action' => 'autocomplete')) ?>" autocomplete="off">

    <input type="text" name="query"<?php if (isset($sf_request->query)) echo ' class="focused"' ?> value="<?php echo esc_entities($sf_request->query) ?>" placeholder="<?php echo __('Search') ?>"/>

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

      <div id="advanced-search">
        <?php echo link_to(__('Advanced search'), array('module' => 'search', 'action' => 'advanced')) ?>
      </div>

    </div>

  </form>

</div>
