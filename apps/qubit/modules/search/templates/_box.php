<div id="search-form-wrapper" role="search">

  <h2><?php echo __('Search') ?></h2>

  <form action="<?php echo url_for(array('module' => 'search')) ?>" data-autocomplete="<?php echo url_for(array('module' => 'search', 'action' => 'autocomplete')) ?>" autocomplete="off">

    <?php if (isset($repository)): ?>
      <input type="text" name="query"<?php if (isset($sf_request->query)) echo ' class="focused"' ?> value="<?php echo $sf_request->query ?>" placeholder="<?php echo __('Search %1%', array('%1%' => render_title($repository))) ?>"/>
    <?php else: ?>
      <input type="text" name="query"<?php if (isset($sf_request->query)) echo ' class="focused"' ?> value="<?php echo $sf_request->query ?>" placeholder="<?php echo __('Search') ?>"/>
    <?php endif; ?>

    <button action="<?php echo url_for(array('module' => 'search')) ?>" data-autocomplete="<?php echo url_for(array('module' => 'search', 'action' => 'autocomplete')) ?>" autocomplete="off"><span><?php echo __('Search') ?></span></button>

    <div id="search-realm" class="search-popover">

      <?php if (sfConfig::get('app_multi_repository')): ?>

        <div>
          <label>
            <?php if (isset($repository)): ?>
              <input name="repos" type="radio" value data-placeholder="<?php echo __('Search') ?>">
            <?php else: ?>
              <input name="repos" type="radio" value checked="checked" data-placeholder="<?php echo __('Search') ?>">
            <?php endif; ?>
            <?php echo __('Global search') ?>
          </label>
        </div>

        <?php if (isset($repository)): ?>
          <div>
            <label>
              <input name="repos" checked="checked" type="radio" value="<?php echo $repository->id ?>" data-placeholder="<?php echo __('Search %1%', array('%1%' => render_title($repository))) ?>"/>
              <?php echo __('Search <span>%1%</span>', array('%1%' => render_title($repository))) ?>
            </label>
          </div>
        <?php endif; ?>

        <?php if (isset($altRepository)): ?>
          <div>
            <label>
              <input name="repos" type="radio" value="<?php echo $altRepository->id ?>" data-placeholder="<?php echo __('Search %1%', array('%1%' => render_title($altRepository))) ?>"/>
              <?php echo __('Search <span>%1%</span>', array('%1%' => render_title($altRepository))) ?>
            </label>
          </div>
        <?php endif; ?>

      <?php endif; ?>

      <div class="search-realm-advanced">
        <a href="<?php echo url_for(array('module' => 'search', 'action' => 'advanced')) ?>">
          <?php echo __('Advanced search') ?>&nbsp;&raquo;
        </a>
      </div>

    </div>

  </form>

</div>
