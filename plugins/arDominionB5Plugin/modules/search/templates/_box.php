<template id="searchbox-options-template">
  <div>
  <?php if (sfConfig::get('app_multi_repository')) { ?>
    <div class="px-3 py-2">
      <div class="form-check">
        <input
          class="form-check-input"
          type="radio"
          name="repos"
          id="search-realm-global"
          checked
          value>
        <label class="form-check-label" for="search-realm-global">
          <?php echo __('Global search'); ?>
        </label>
      </div>
      <?php if (isset($repository)) { ?>
        <div class="form-check">
          <input
            class="form-check-input"
            type="radio"
            name="repos"
            id="search-realm-repo"
            value="<?php echo $repository->id; ?>">
          <label class="form-check-label" for="search-realm-repo">
            <?php echo __('Search <span>%1%</span>', ['%1%' => render_title($repository)]); ?>
          </label>
        </div>
      <?php } ?>
      <?php if (isset($altRepository)) { ?>
        <div class="form-check">
          <input
            class="form-check-input"
            type="radio"
            name="repos"
            id="search-realm-alt-repo"
            value="<?php echo $altRepository->id; ?>">
          <label class="form-check-label" for="search-realm-alt-repo">
            <?php echo __('Search <span>%1%</span>', ['%1%' => render_title($altRepository)]); ?>
          </label>
        </div>
      <?php } ?>
    </div>
    <hr class="dropdown-divider"></hr>
  <?php } ?>
  <a class="dropdown-item" href="<?php echo url_for([
      'module' => 'informationobject',
      'action' => 'browse',
      'showAdvanced' => true,
      'topLod' => false,
  ]); ?>">
    <?php echo __('Advanced search'); ?>
  </a>
  </div>
</template>
<form
  id="search-box"
  class="d-flex flex-grow-1 my-2"
  role="search"
  action="<?php echo url_for(['module' => 'informationobject', 'action' => 'browse']); ?>">
  <h2 class="visually-hidden"><?php echo __('Search'); ?></h2>
  <input type="hidden" name="topLod" value="0">
  <input type="hidden" name="sort" value="relevance">
  <div class="input-group flex-nowrap">
    <input
      id="search-box-input"
      class="form-control form-control-sm dropdown-toggle"
      type="search"
      name="query"
      autocomplete="off"
      value="<?php echo $sf_request->query; ?>"
      placeholder="<?php echo sfConfig::get('app_ui_label_globalSearch'); ?>"
      data-url="<?php echo url_for(['module' => 'search', 'action' => 'autocomplete']); ?>"
      data-bs-toggle="dropdown"
      data-bs-auto-close="outside"
      aria-label="<?php echo sfConfig::get('app_ui_label_globalSearch'); ?>"
      aria-expanded="false">
    <div id="search-box-dropdown" class="dropdown-menu mt-2" aria-labelledby="search-box-input"></div>
    <button class="btn btn-sm atom-btn-secondary" type="submit">
      <i class="fas fa-search" aria-hidden="true"></i>
      <span class="visually-hidden"><?php echo __('Search in browse page'); ?></span>
    </button>
  </div>
</form>
