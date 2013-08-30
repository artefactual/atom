<div id="search-form-wrapper">

  <form action="<?php echo url_for(array('module' => 'search')) ?>" data-autocomplete="<?php echo url_for(array('module' => 'search', 'action' => 'autocomplete')) ?>">

    <div class="input-append">

      <?php if (isset($repository)): ?>
        <input type="text" name="query"<?php if (isset($sf_request->query)) echo ' class="focused"' ?> value="<?php echo esc_entities($sf_request->query) ?>" placeholder="<?php echo __('Search %1%', array('%1%' => render_title($repository))) ?>"/>
      <?php else: ?>
        <input type="text" name="query"<?php if (isset($sf_request->query)) echo ' class="focused"' ?> value="<?php echo esc_entities($sf_request->query) ?>" placeholder="<?php echo __('Search') ?>"/>
      <?php endif; ?>

      <div class="btn-group">
        <button class="btn dropdown-toggle" data-toggle="dropdown">
          <span class="caret"></span>
        </button>
        <?php $cacheKey = 'search-box-nav-'.$sf_user->getCulture() ?>
        <?php if (!cache($cacheKey)): ?>
          <ul class="dropdown-menu pull-right">
            <?php $icons = array(
              'browseInformationObjects' => '/images/icons-large/icon-archival.png',
              'browseActors' => '/images/icons-large/icon-people.png',
              'browseRepositories' => '/images/icons-large/icon-institutions.png',
              'browseSubjects' => '/images/icons-large/icon-subjects.png',
              'browsePlaces' => '/images/icons-large/icon-places.png',
              'browseDigitalObjects' => '/images/icons-large/icon-media.png') ?>
            <?php $browseMenu = QubitMenu::getById(QubitMenu::BROWSE_ID) ?>
            <?php if ($browseMenu->hasChildren()): ?>
              <?php foreach ($browseMenu->getChildren() as $item): ?>
                <li>
                  <a href="<?php echo url_for($item->getPath(array('getUrl' => true, 'resolveAlias' => true))) ?>">
                    <?php if (isset($icons[$item->name])): ?>
                      <?php echo image_tag($icons[$item->name], array('width' => 42, 'height' => 42)) ?>
                    <?php endif; ?>
                    <?php echo $item->getLabel(array('cultureFallback' => true)) ?>
                  </a>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
            <li class="divider"></li>
            <li class="advanced-search">
              <a href="<?php echo url_for(array('module' => 'search', 'action' => 'advanced')) ?>">
                <i class="icon-zoom-in"></i>
                <?php echo __('Advanced search') ?>
              </a>
            </li>
          </ul>
          <?php cache_save($cacheKey) ?>
        <?php endif; ?>
      </div>

    </div>

    <div id="search-realm" class="search-popover">

      <?php if (sfConfig::get('app_multi_repository')): ?>

        <div>
          <label>
            <?php if (isset($repository)): ?>
              <input name="realm" type="radio" value="all" data-placeholder="<?php echo __('Search') ?>">
            <?php else: ?>
              <input name="realm" type="radio" value="all" checked="checked" data-placeholder="<?php echo __('Search') ?>">
            <?php endif; ?>
            <?php echo __('Global search') ?>
          </label>
        </div>

        <?php if (isset($repository)): ?>
          <div>
            <label>
              <input name="realm" checked="checked" type="radio" value="<?php echo $repository->id ?>"/>
              <?php echo __('Search <span>%1%</span>', array('%1%' => render_title($repository))) ?>
            </label>
          </div>
        <?php endif; ?>

        <?php if (isset($altRepository)): ?>
          <div>
            <label>
              <input name="realm" type="radio" value="<?php echo $altRepository->id ?>"/>
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
