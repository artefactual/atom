<div id="search-form-wrapper">

  <form action="<?php echo url_for(['module' => 'informationobject', 'action' => 'browse']); ?>" data-autocomplete="<?php echo url_for(['module' => 'search', 'action' => 'autocomplete']); ?>">

    <input type="hidden" name="topLod" value="0"/>

    <div class="input-append">

      <input type="text" name="query"<?php echo isset($sf_request->query) ? ' class="focused"' : ''; ?> value="<?php echo $sf_request->query; ?>" placeholder="<?php echo __('Search'); ?>"/>

      <div class="btn-group">
        <button class="btn dropdown-toggle" data-toggle="dropdown">
          <span class="caret"></span>
        </button>
        <?php $cacheKey = 'search-box-nav-'.$sf_user->getCulture(); ?>
        <?php if (!cache($cacheKey)) { ?>
          <ul class="dropdown-menu pull-right">
            <?php $icons = [
                'browseInformationObjects' => '/images/icons-large/icon-archival.png',
                'browseActors' => '/images/icons-large/icon-people.png',
                'browseRepositories' => '/images/icons-large/icon-institutions.png',
                'browseSubjects' => '/images/icons-large/icon-subjects.png',
                'browseFunctions' => '/images/icons-large/icon-functions.png',
                'browsePlaces' => '/images/icons-large/icon-places.png',
                'browseDigitalObjects' => '/images/icons-large/icon-media.png', ]; ?>
            <?php $browseMenu = QubitMenu::getById(QubitMenu::BROWSE_ID); ?>
            <?php if ($browseMenu->hasChildren()) { ?>
              <?php foreach ($browseMenu->getChildren() as $item) { ?>
                <li>
                  <a href="<?php echo url_for($item->getPath(['getUrl' => true, 'resolveAlias' => true])); ?>">
                    <?php if (isset($icons[$item->name])) { ?>
                      <?php echo image_tag($icons[$item->name], ['width' => 42, 'height' => 42, 'alt' => '']); ?>
                    <?php } ?>
                    <?php echo esc_entities($item->getLabel(['cultureFallback' => true])); ?>
                  </a>
                </li>
              <?php } ?>
            <?php } ?>
            <li class="divider"></li>
            <li class="advanced-search">
              <a href="<?php echo url_for(['module' => 'informationobject', 'action' => 'browse', 'showAdvanced' => true, 'topLod' => false]); ?>">
                <i class="icon-zoom-in"></i>
                <?php echo __('Advanced search'); ?>
              </a>
            </li>
          </ul>
          <?php cache_save($cacheKey); ?>
        <?php } ?>
      </div>

    </div>

    <div id="search-realm" class="search-popover">

      <?php if (sfConfig::get('app_multi_repository')) { ?>

        <div>
          <label>
            <input name="repos" type="radio" value checked="checked" data-placeholder="<?php echo __('Search'); ?>">
            <?php echo __('Global search'); ?>
          </label>
        </div>

        <?php if (isset($repository)) { ?>
          <div>
            <label>
              <input name="repos" type="radio" value="<?php echo $repository->id; ?>"/>
              <?php echo __('Search <span>%1%</span>', ['%1%' => render_title($repository)]); ?>
            </label>
          </div>
        <?php } ?>

        <?php if (isset($altRepository)) { ?>
          <div>
            <label>
              <input name="repos" type="radio" value="<?php echo $altRepository->id; ?>"/>
              <?php echo __('Search <span>%1%</span>', ['%1%' => render_title($altRepository)]); ?>
            </label>
          </div>
        <?php } ?>

      <?php } ?>

      <div class="search-realm-advanced">
        <a href="<?php echo url_for(['module' => 'informationobject', 'action' => 'browse', 'showAdvanced' => true, 'topLod' => false]); ?>">
          <?php echo __('Advanced search'); ?>&nbsp;&raquo;
        </a>
      </div>

    </div>

  </form>

</div>
