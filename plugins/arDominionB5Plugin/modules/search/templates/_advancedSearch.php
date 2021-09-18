<div class="accordion mb-3 adv-search" role="search">
  <div class="accordion-item">
    <h2 class="accordion-header" id="heading-adv-search">
      <button class="accordion-button<?php echo $show ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-adv-search" aria-expanded="<?php echo $show ? 'true' : 'false'; ?>" aria-controls="collapse-adv-search">
        <?php echo __('Advanced search options'); ?>
      </button>
    </h2>
    <div id="collapse-adv-search" class="accordion-collapse collapse<?php echo $show ? ' show' : ''; ?>" aria-labelledby="heading-adv-search">
      <div class="accordion-body">
        <?php echo $form->renderFormTag(url_for(['module' => 'informationobject', 'action' => 'browse']), ['name' => 'advanced-search-form', 'method' => 'get']); ?>

          <?php foreach ($hiddenFields as $name => $value) { ?>
            <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>"/>
          <?php } ?>

          <h5><?php echo __('Find results with:'); ?></h5>

          <div class="criteria mb-4">

            <?php if (isset($criteria)) { ?>

              <?php foreach ($criteria as $key => $item) { ?>

                <div class="criterion row align-items-center">

                  <div class="col-xl-auto mb-3 adv-search-boolean">
                    <select class="form-select" name="so<?php echo $key; ?>">
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
                      <option value="title"<?php echo 'title' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Title'); ?></option>
                      <?php if (('rad' == $template && check_field_visibility('app_element_visibility_rad_archival_history'))
                        || ('isad' == $template && check_field_visibility('app_element_visibility_isad_archival_history'))
                        || ('isad' != $template && 'rad' != $template)) { ?>
                        <option value="archivalHistory"<?php echo 'archivalHistory' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Archival history'); ?></option>
                      <?php } ?>
                      <option value="scopeAndContent"<?php echo 'scopeAndContent' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Scope and content'); ?></option>
                      <option value="extentAndMedium"<?php echo 'extentAndMedium' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Extent and medium'); ?></option>
                      <option value="subject"<?php echo 'subject' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Subject access points'); ?></option>
                      <option value="name"<?php echo 'name' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Name access points'); ?></option>
                      <option value="place"<?php echo 'place' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Place access points'); ?></option>
                      <option value="genre"<?php echo 'genre' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Genre access points'); ?></option>
                      <option value="identifier"<?php echo 'identifier' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Identifier'); ?></option>
                      <option value="referenceCode"<?php echo 'referenceCode' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Reference code'); ?></option>
                      <option value="digitalObjectTranscript"<?php echo 'digitalObjectTranscript' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Digital object text'); ?></option>
                      <option value="findingAidTranscript"<?php echo 'findingAidTranscript' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Finding aid text'); ?></option>
                      <option value="creator"<?php echo 'creator' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Creator'); ?></option>
                      <option value="allExceptFindingAidTranscript"<?php echo 'allExceptFindingAidTranscript' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Any field except finding aid text'); ?></option>
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
                <select class="form-select" name="so<?php echo $count; ?>">
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
                  <option value="title"><?php echo __('Title'); ?></option>
                  <?php if (('rad' == $template && check_field_visibility('app_element_visibility_rad_archival_history'))
                    || ('isad' == $template && check_field_visibility('app_element_visibility_isad_archival_history'))
                    || ('isad' != $template && 'rad' != $template)) { ?>
                    <option value="archivalHistory"><?php echo __('Archival history'); ?></option>
                  <?php } ?>
                  <option value="scopeAndContent"><?php echo __('Scope and content'); ?></option>
                  <option value="extentAndMedium"><?php echo __('Extent and medium'); ?></option>
                  <option value="subject"><?php echo __('Subject access points'); ?></option>
                  <option value="name"><?php echo __('Name access points'); ?></option>
                  <option value="place"><?php echo __('Place access points'); ?></option>
                  <option value="genre"><?php echo __('Genre access points'); ?></option>
                  <option value="identifier"><?php echo __('Identifier'); ?></option>
                  <option value="referenceCode"><?php echo __('Reference code'); ?></option>
                  <option value="digitalObjectTranscript"><?php echo __('Digital object text'); ?></option>
                  <option value="findingAidTranscript"><?php echo __('Finding aid text'); ?></option>
                  <option value="creator"><?php echo __('Creator'); ?></option>
                  <option value="allExceptFindingAidTranscript"><?php echo __('Any field except finding aid text'); ?></option>
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
              <ul class="dropdown-menu" aria-labelledby="add-criterion-dropdown-menu">
                <li><a class="dropdown-item" href="#" id="add-criterion-and"><?php echo __('And'); ?></a></li>
                <li><a class="dropdown-item" href="#" id="add-criterion-or"><?php echo __('Or'); ?></a></li>
                <li><a class="dropdown-item" href="#" id="add-criterion-not"><?php echo __('Not'); ?></a></li>
              </ul>
            </div>

          </div>

          <h5><?php echo __('Limit results to:'); ?></h5>

          <div class="criteria mb-4">

            <?php if (sfConfig::get('app_multi_repository')) { ?>
              <?php echo render_field($form->repos->label(__('Repository'))); ?>
            <?php } ?>

            <?php echo render_field(
                $form->collection->label(__('Top-level description')),
                null,
                [
                    'class' => 'form-autocomplete',
                    'extraInputs' => '<input class="list" type="hidden" value="'
                        .url_for([
                            'module' => 'informationobject',
                            'action' => 'autocomplete',
                            'parent' => QubitInformationObject::ROOT_ID,
                            'filterDrafts' => true,
                        ])
                        .'">',
                ]
            ); ?>

          </div>

          <h5><?php echo __('Filter results by:'); ?></h5>

          <div class="criteria mb-4">

            <div class="row">

              <div class="col-md-4">
                <?php echo render_field($form->levels->label(__('Level of description'))); ?>
              </div>

              <div class="col-md-4">
                <?php echo render_field($form->onlyMedia->label(__('%1% available', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]))); ?>
              </div>

              <div class="col-md-4">
                <?php echo render_field($form->findingAidStatus->label(__('Finding aid'))); ?>
              </div>

            </div>

            <div class="row">

              <?php if (sfConfig::get('app_toggleCopyrightFilter')) { ?>
                <div class="col-md-6">
                  <?php echo render_field($form->copyrightStatus->label(__('Copyright status'))); ?>
                </div>
              <?php } ?>

              <?php if (sfConfig::get('app_toggleMaterialFilter')) { ?>
                <div class="col-md-6">
                  <?php echo render_field($form->materialType->label(__('General material designation'))); ?>
                </div>
              <?php } ?>

              <fieldset class="col-12">
                <legend class="visually-hidden"><?php echo __('Top-level description filter'); ?></legend>
                <div class="d-grid d-sm-block mt-2">
                  <div class="form-check d-inline-block me-2">
                    <input class="form-check-input" type="radio" name="topLod" id="adv-search-top-lod-1" value="1" <?php echo $topLod ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="adv-search-top-lod-1"><?php echo __('Top-level descriptions'); ?></label>
                  </div>
                  <div class="form-check d-inline-block">
                    <input class="form-check-input" type="radio" name="topLod" id="adv-search-top-lod-0" value="0" <?php echo !$topLod ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="adv-search-top-lod-0"><?php echo __('All descriptions'); ?></label>
                  </div>
                </div>
              </fieldset>

            </div>

          </div>

          <h5><?php echo __('Filter by date range:'); ?></h5>

          <div class="criteria row mb-2">

            <div class="col-md-4 start-date">
              <?php echo render_field($form->startDate->label(__('Start')), null, ['type' => 'date']); ?>
            </div>

            <div class="col-md-4 end-date">
              <?php echo render_field($form->endDate->label(__('End')), null, ['type' => 'date']); ?>
            </div>

            <fieldset class="col-md-4 date-type">
              <legend class="fs-6">
                <span><?php echo __('Results'); ?></span>
                <a
                  href="#"
                  class="ms-1"
                  data-bs-toggle="tooltip"
                  data-bs-trigger="hover focus"
                  data-bs-placement="auto"
                  title='<?php echo __('Use these options to specify how the date range returns results. "Exact" means that the start and end dates of descriptions returned must fall entirely within the date range entered. "Overlapping" means that any description whose start or end dates touch or overlap the target date range will be returned.'); ?>'>
                  <i aria-hidden="true" class="fas fa-question-circle text-muted me-2"></i>
                </a>
              </legend>
              <div class="d-grid d-sm-block">
                <div class="form-check d-inline-block me-2">
                  <input class="form-check-input" type="radio" name="rangeType" id="adv-search-date-range-inclusive" value="inclusive" <?php echo 'inclusive' == $rangeType ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="adv-search-date-range-inclusive"><?php echo __('Overlapping'); ?></label>
                </div>
                <div class="form-check d-inline-block">
                  <input class="form-check-input" type="radio" name="rangeType" id="adv-search-date-range-exact" value="exact" <?php echo 'exact' == $rangeType ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="adv-search-date-range-exact"><?php echo __('Exact'); ?></label>
                </div>
              </div>
            </fieldset>

          </div>

          <ul class="actions mb-1 nav gap-2 justify-content-center">
            <li><input type="button" class="btn atom-btn-outline-light reset" value="<?php echo __('Reset'); ?>"></li>
            <li><input type="submit" class="btn atom-btn-outline-light" value="<?php echo __('Search'); ?>"></li>
          </ul>

        </form>
      </div>
    </div>
  </div>
</div>
