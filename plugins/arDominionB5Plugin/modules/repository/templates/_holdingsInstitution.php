<section class="card mb-3">
  <div class="card-body">
    <?php include_component('repository', 'logo', ['resource' => $resource]); ?>

    <form class="mb-3" role="search" aria-label="<?php echo sfConfig::get('app_ui_label_institutionSearchHoldings'); ?>" action="<?php echo url_for(['module' => 'informationobject', 'action' => 'browse']); ?>">
      <input type="hidden" name="repos" value="<?php echo $resource->id; ?>">
      <label for="institution-search-query" class="h5 mb-2 form-label"><?php echo sfConfig::get('app_ui_label_institutionSearchHoldings'); ?></label>
      <div class="input-group">
        <input type="text" class="form-control" id="institution-search-query" name="query" value="<?php echo $sf_request->query; ?>" placeholder="<?php echo __('Search'); ?>" required>
        <button class="btn atom-btn-white" type="submit" aria-label=<?php echo __('Search'); ?>>
          <i aria-hidden="true" class="fas fa-search"></i>
        </button>
      </div>
    </form>

    <?php echo get_component('menu', 'browseMenuInstitution', ['sf_cache_key' => 'dominion-b5'.$sf_user->getCulture().$sf_user->getUserID()]); ?>
  </div>
</section>
