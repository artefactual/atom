<form class="d-flex flex-grow-1 my-2" role="search" action="<?php echo url_for(['module' => 'informationobject', 'action' => 'browse']); ?>">
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
      placeholder="<?php echo __('Search'); ?>"
      data-url="<?php echo url_for(['module' => 'search', 'action' => 'autocomplete']); ?>"
      data-bs-toggle="dropdown"
      aria-label="<?php echo __('Search autocomplete'); ?>"
      aria-expanded="false">
    <ul id="search-box-results" class="dropdown-menu mt-2" aria-labelledby="search-box-input"></ul>
    <button class="btn btn-sm atom-btn-secondary" type="submit">
      <i class="fas fa-search" aria-hidden="true"></i>
      <span class="visually-hidden"><?php echo __('Search in browse page'); ?></span>
    </button>
  </div>
</form>
