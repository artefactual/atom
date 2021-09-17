<div class="accordion mb-3 adv-search" role="search">
  <div class="accordion-item">
    <h2 class="accordion-header" id="heading-adv-search">
      <button class="accordion-button<?php echo $show ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-adv-search" aria-expanded="<?php echo $show ? 'true' : 'false'; ?>" aria-controls="collapse-adv-search">
        <?php echo __('Filter options'); ?>
      </button>
    </h2>
    <div id="collapse-adv-search" class="accordion-collapse collapse<?php echo $show ? ' show' : ''; ?>" aria-labelledby="heading-adv-search">
      <div class="accordion-body">
        <?php echo $form->renderFormTag(url_for(['module' => 'search', 'action' => 'descriptionUpdates']), ['name' => 'advanced-search-form', 'method' => 'get']); ?>

          <input type="hidden" name="showForm" value="1"/>

          <h5><?php echo __('Filter results by:'); ?></h5>

          <div class="criteria row mb-2">

            <div class="col-md-6">
              <?php echo render_field($form->className->label(__('Type'))); ?>
            </div>

            <fieldset class="col-md-6">
              <legend class="fs-6"><?php echo __('Date of'); ?></legend>
              <?php foreach ($form->getWidgetSchema()->dateOf->getChoices() as $value => $translatedText) { ?>
                <div class="form-check d-inline-block me-2">
                  <?php $radioID = 'dateOf-'.QubitSlug::slugify($value); ?>
                  <label class="form-check-label" for="<?php echo $radioID; ?>"><?php echo $translatedText; ?></label>
                  <input class="form-check-input" id="<?php echo $radioID; ?>" type="radio" name="dateOf" value="<?php echo $value; ?>" <?php echo $form->getValue('dateOf') == $value ? 'checked' : ''; ?>>
                </div>
              <?php } ?>
            </fieldset>

            <?php if (sfConfig::get('app_multi_repository')) { ?>
              <div class="col-md-6">
                <?php echo render_field($form->repository->label(__('Repository'))); ?>
              </div>
            <?php } ?>

            <fieldset class="col-md-6">
              <legend class="fs-6"><?php echo __('Publication status'); ?></legend>
              <?php foreach ($form->getWidgetSchema()->publicationStatus->getChoices() as $value => $translatedText) { ?>
                <div class="form-check d-inline-block me-2">
                  <?php $radioID = 'publicationStatus-'.QubitSlug::slugify($value); ?>
                  <label class="form-check-label" for="<?php echo $radioID; ?>"><?php echo $translatedText; ?></label>
                  <input class="form-check-input" id="<?php echo $radioID; ?>" type="radio" name="publicationStatus" value="<?php echo $value; ?>" <?php echo $form->getValue('publicationStatus') == $value ? 'checked' : ''; ?>>
                </div>
              <?php } ?>
            </fieldset>

            <?php if (sfConfig::get('app_audit_log_enabled', false)) { ?>
              <div class="col-md-6">
                <?php echo render_field(
                  $form->user->label(__('User')),
                  null,
                  [
                      'class' => 'form-autocomplete',
                      'extraInputs' => '<input class="list" type="hidden" value="'
                          .url_for([
                              'module' => 'user',
                              'action' => 'autocomplete',
                          ])
                          .'">',
                  ]
                ); ?>
                <?php if (isset($user)) { ?>
                  <div class="form-text">
                    <?php echo __('Currently displaying:'); ?> <?php echo $user->getUsername(); ?></em>
                  </div>
                <?php } ?>
              </div>
            <?php } ?>

          </div>

          <h5><?php echo __('Filter by date range:'); ?></h5>

          <div class="criteria row mb-2">

            <div class="col-md-6 start-date">
              <?php echo render_field($form->startDate->label(__('Start')), null, ['type' => 'date']); ?>
            </div>

            <div class="col-md-6 end-date">
              <?php echo render_field($form->endDate->label(__('End')), null, ['type' => 'date']); ?>
            </div>

          </div>

          <ul class="actions mb-1 nav gap-2 justify-content-center">
            <li><input type="submit" class="btn atom-btn-outline-light" value="<?php echo __('Search'); ?>"></li>
            <li><input type="button" class="btn atom-btn-outline-light reset" value="<?php echo __('Reset'); ?>"></li>
          </ul>

        </form>
      </div>
    </div>
  </div>
</div>
