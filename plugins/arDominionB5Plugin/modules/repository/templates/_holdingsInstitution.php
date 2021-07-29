<section id="repo-holdings" class="list-menu"
  data-total-pages="<?php echo $pager->getLastPage(); ?>"
  data-url="<?php echo url_for(['module' => 'repository', 'action' => 'holdingsInstitution', 'id' => $resource->id]); ?>">

  <div class="panel panel-gray">
    <div class="panel-body">

      <?php use_helper('Text'); ?>

      <div class="repository-logo<?php echo $resource->existsLogo() ? '' : ' repository-logo-text'; ?>">
        <a href="<?php echo url_for([$resource, 'module' => 'repository']); ?>">
          <?php if ($resource->existsLogo()) { ?>
            <?php echo image_tag($resource->getLogoPath(),
                  ['alt' => __('Go to %1%',
                  ['%1%' => truncate_text(strip_markdown($resource), 100)])]); ?>
          <?php } else { ?>
            <h2><?php echo render_title($resource); ?></h2>
          <?php } ?>
        </a>
      </div>

      <h3><?php echo sfConfig::get('app_ui_label_institutionSearchHoldings'); ?></h3>

      <form class="sidebar-search" role="search" aria-label="<?php echo __(sfConfig::get('app_ui_label_informationobject')); ?>" action="<?php echo url_for(['module' => 'informationobject', 'action' => 'browse']); ?>">
        <input type="hidden" name="repos" value="<?php echo $resource->id; ?>">
        <div class="input-prepend input-append">
          <input type="text" name="query" aria-label="<?php echo __('Search'); ?>" value="<?php echo $sf_request->query; ?>" placeholder="<?php echo __('Search'); ?>">
          <button class="btn" type="submit" aria-label="<?php echo __('Search'); ?>">
            <i aria-hidden="true" class="fa fa-search"></i>
          </button>
        </div>
      </form>

      <?php echo get_component('menu', 'browseMenuInstitution', ['sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID()]); ?>

    </div>
  </div>
</section>
