<div class="accordion mb-3 adv-search" role="search">
  <div class="accordion-item">
    <h2 class="accordion-header" id="heading-adv-search">
      <button class="accordion-button<?php echo $show ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-adv-search" aria-expanded="<?php echo $show ? 'true' : 'false'; ?>" aria-controls="collapse-adv-search">
        <?php echo __('Advanced search options'); ?>
      </button>
    </h2>
    <div id="collapse-adv-search" class="accordion-collapse collapse<?php echo $show ? ' show' : ''; ?>" aria-labelledby="heading-adv-search">
      <div class="accordion-body">
        <?php echo $form->renderFormTag(url_for(['module' => 'actor', 'action' => 'browse']), ['name' => 'advanced-search-form', 'method' => 'get']); ?>

          <?php foreach ($hiddenFields as $name => $value) { ?>
            <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>"/>
          <?php } ?>

          <h5><?php echo __('Find results with:'); ?></h5>

          <div class="criteria mb-4">

            <?php if (isset($criteria)) { ?>

              <?php foreach ($criteria as $key => $item) { ?>

                <div class="criterion row align-items-center">

                  <div class="col-xl-auto mb-3 adv-search-boolean">
                    <select class="form-select" name="so<?php echo $key; ?>" aria-label="<?php echo __('Boolean'); ?>">
                      <option value="and"<?php echo 'and' == $item['operator'] ? ' selected="selected"' : ''; ?>><?php echo __('and'); ?></option>
                      <option value="or"<?php echo 'or' == $item['operator'] ? ' selected="selected"' : ''; ?>><?php echo __('or'); ?></option>
                      <option value="not"<?php echo 'not' == $item['operator'] ? ' selected="selected"' : ''; ?>><?php echo __('not'); ?></option>
                    </select>
                  </div>

                  <div class="col-xl-auto flex-grow-1 mb-3">
                    <input class="form-control" type="text" aria-label="<?php echo __('Search'); ?>" placeholder="<?php echo __('Search'); ?>" name="sq<?php echo $key; ?>" value="<?php echo $item['query']; ?>">
                  </div>

                  <div class="col-xl-auto mb-3 text-center">
                    <span class="form-text"><?php echo __('in'); ?></span>
                  </div>

                  <div class="col-xl-auto mb-3">
                    <select class="form-select" name="sf<?php echo $key; ?>">
                      <option value=""<?php echo '' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Any field'); ?></option>
                      <?php foreach ($fieldOptions as $name => $label) { ?>
                        <option value="<?php echo $name; ?>"<?php echo $item['field'] == $name ? ' selected="selected"' : ''; ?>><?php echo $label; ?></option>
                      <?php } ?>
                    </select>
                  </div>

                  <div class="col-xl-auto mb-3">
                    <a href="#" class="delete-criterion" aria-label="<?php echo __('Delete criterion'); ?>">
                      <i aria-hidden="true" class="fas fa-times text-muted"></i>
                    </a>
                  </div>

                </div>

              <?php } ?>

            <?php } ?>

            <?php $count = isset($key) ? $key++ : 0; ?>

            <div class="criterion row align-items-center">

              <div class="col-xl-auto mb-3 adv-search-boolean">
                <select class="form-select" name="so<?php echo $count; ?>" aria-label="<?php echo __('Boolean'); ?>">
                  <option value="and"><?php echo __('and'); ?></option>
                  <option value="or"><?php echo __('or'); ?></option>
                  <option value="not"><?php echo __('not'); ?></option>
                </select>
              </div>

              <div class="col-xl-auto flex-grow-1 mb-3">
                <input class="form-control" type="text" aria-label="<?php echo __('Search'); ?>" placeholder="<?php echo __('Search'); ?>" name="sq<?php echo $count; ?>">
              </div>

              <div class="col-xl-auto mb-3 text-center">
                <span class="form-text"><?php echo __('in'); ?></span>
              </div>

              <div class="col-xl-auto mb-3">
                <select class="form-select" name="sf<?php echo $count; ?>">
                  <option value=""><?php echo __('Any field'); ?></option>
                  <?php foreach ($fieldOptions as $name => $label) { ?>
                    <option value="<?php echo $name; ?>"><?php echo $label; ?></option>
                  <?php } ?>
                </select>
              </div>

              <div class="col-xl-auto mb-3">
                <a href="#" class="d-none d-xl-block delete-criterion" aria-label="<?php echo __('Delete criterion'); ?>">
                  <i aria-hidden="true" class="fas fa-times text-muted"></i>
                </a>
                <a href="#" class="d-xl-none delete-criterion btn btn-outline-danger w-100 mb-3">
                  <?php echo __('Delete criterion'); ?>
                </a>
              </div>

            </div>

            <div class="add-new-criteria mb-3">
              <a id="add-criterion-dropdown-menu" class="btn atom-btn-white dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><?php echo __('Add new criteria'); ?></a>
              <ul class="dropdown-menu mt-2" aria-labelledby="add-criterion-dropdown-menu">
                <li><a class="dropdown-item" href="#" id="add-criterion-and"><?php echo __('And'); ?></a></li>
                <li><a class="dropdown-item" href="#" id="add-criterion-or"><?php echo __('Or'); ?></a></li>
                <li><a class="dropdown-item" href="#" id="add-criterion-not"><?php echo __('Not'); ?></a></li>
              </ul>
            </div>

          </div>

          <?php if (sfConfig::get('app_multi_repository')) { ?>
            <h5><?php echo __('Limit results to:'); ?></h5>

            <div class="criteria mb-4">
              <?php echo render_field($form->repository->label(__('Repository'))); ?>
            </div>
          <?php } ?>

          <h5><?php echo __('Filter results by:'); ?></h5>

          <div class="criteria row mb-2">

            <div class="col-md-4">
              <?php echo render_field($form->hasDigitalObject->label(__('%1% available', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]))); ?>
            </div>

            <div class="col-md-4">
              <?php echo render_field($form->entityType->label(__('%1% available', ['%1%' => __('Entity type')]))); ?>
            </div>

            <div class="col-md-4">
              <?php echo render_field($form->emptyField->label(__('Empty field'))); ?>
            </div>

          </div>

          <h5><?php echo __('Find results where:'); ?></h5>

          <div class="criteria row mb-2">

            <div class="col-md-3">
              <?php echo render_field($form->relatedType->label(__('Relationship'))); ?>
            </div>

            <div class="col-md-9">
              <?php echo render_field(
                $form->relatedAuthority->label(__('Related %1%', ['%1%' => sfConfig::get('app_ui_label_actor')])),
                null,
                [
                    'class' => 'form-autocomplete',
                    'extraInputs' => '<input class="list" type="hidden" value="'
                        .url_for([
                            'module' => 'actor',
                            'action' => 'autocomplete',
                        ])
                        .'">',
                ]); ?>
            </div>

          </div>

          <ul class="actions mb-1 nav gap-2 justify-content-center">
            <li><input type="button" class="btn atom-btn-outline-danger reset" value="<?php echo __('Reset'); ?>"></li>
            <li><input type="submit" class="btn atom-btn-outline-light" value="<?php echo __('Search'); ?>"></li>
          </ul>

        </form>
      </div>
    </div>
  </div>
</div>
