<form
  id="inline-search"
  method="get"
  action="<?php echo $route; ?>"
  role="search"
  aria-label="<?php echo $landmarkLabel; ?>">

  <?php if (isset($sf_request->view)) { ?>
    <input type="hidden" name="view" value="<?php echo $sf_request->view; ?>"/>
  <?php } ?>

  <div class="input-group flex-nowrap">
    <?php if (isset($fields)) { ?>
      <?php
          $fields = $sf_data->getRaw('fields');
          if (isset(
              $sf_request->subqueryField,
              $fields[$sf_request->subqueryField]
          )) {
              $active = $sf_request->subqueryField;
          } else {
              $active = array_key_first($fields);
          }
      ?>

      <button
        id="inline-search-options"
        class="btn btn-sm atom-btn-white dropdown-toggle"
        type="button"
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        aria-expanded="false">
        <i class="fas fa-cog" aria-hidden="true"></i>
        <span class="visually-hidden"><?php echo __('Search options'); ?></span>
      </button>
      <div class="dropdown-menu mt-2" aria-labelledby="inline-search-options">
        <div class="px-3 py-2">
          <?php foreach ($fields as $value => $text) { ?>
            <div class="form-check">
              <input
                class="form-check-input"
                type="radio"
                name="subqueryField"
                id="option-<?php echo $value; ?>"
                <?php echo $active == $value ? 'checked' : ''; ?>
                value="<?php echo $value; ?>">
              <label class="form-check-label" for="option-<?php echo $value; ?>">
                <?php echo $text; ?>
              </label>
            </div>
          <?php } ?>
        </div>
      </div>
    <?php } ?>

    <input
      class="form-control form-control-sm"
      type="search"
      name="subquery"
      value="<?php echo $sf_request->subquery; ?>"
      placeholder="<?php echo $label; ?>"
      aria-label="<?php echo $label; ?>">

    <?php if (!empty($sf_request->subquery)) { ?>
      <a
        href="<?php echo $cleanRoute; ?>"
        class="btn btn-sm atom-btn-white d-flex align-items-center"
        role="button">
        <i class="fas fa-undo" aria-hidden="true"></i>
        <span class="visually-hidden"><?php echo __('Reset search'); ?></span>
      </a>
    <?php } ?>

    <button class="btn btn-sm atom-btn-white" type="submit">
      <i class="fas fa-search" aria-hidden="true"></i>
      <span class="visually-hidden"><?php echo __('Search'); ?></span>
    </button>
  </div>

</form>
