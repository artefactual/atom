<div class="search section">

  <h2 class="element-invisible"><?php echo __('Search') ?></h2>

  <div class="content">
    <form action="<?php echo url_for(array('module' => 'search')) ?>">
      <input name="query"<?php if (isset($sf_request->query)) echo ' class="focused"' ?> value="<?php echo esc_entities($sf_request->query) ?>"/>
      <input class="form-submit" type="submit" value="<?php echo __('Search') ?>"/>
      <div class="advanced-search">
        <?php echo link_to(__('Advanced search'), array('module' => 'search', 'action' => 'advanced'),
                           array('query_string' => (isset($sf_request->query)) ? 'searchFields[0][field]=&searchFields[0][match]=keyword&searchFields[0][operator]=and&searchFields[0][query]=' . $sf_request->query : null)) ?>
      </div>
    </form>
  </div>

</div>
