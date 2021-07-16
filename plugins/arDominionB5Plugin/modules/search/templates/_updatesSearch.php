<div class="accordion mb-3" role="search">
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

          <p><?php echo __('Filter results by:'); ?></p>

          <div class="criteria">

            <div class="filter-row double">

              <div class="filter-left">
                <?php echo $form->className
                    ->label(__('Type'))
                    ->renderRow(); ?>
              </div>

              <div class="filter-right">
                <label class="date-of-label"><?php echo __('Date of'); ?></label>
                <div class="date-of">
                  <?php foreach ($form->getWidgetSchema()->dateOf->getChoices() as $value => $translatedText) { ?>
                    <label>
                      <input type="radio" name="dateOf" value="<?php echo $value; ?>" <?php echo $form->getValue('dateOf') == $value ? 'checked' : ''; ?>>
                      <?php echo $translatedText; ?>
                    </label>
                  <?php } ?>
                </div>
              </div>

            </div>

            <div class="filter-row double io-options">

              <?php if (sfConfig::get('app_multi_repository')) { ?>
                <div class="filter-left">
                  <?php echo $form->repository
                      ->label(__('Repository'))
                      ->renderRow(); ?>
                </div>
                <div class="filter-right">
              <?php } else { ?>
                <div class="filter-left">
              <?php } ?>
                <label class="publication-status-label"><?php echo __('Publication status'); ?></label>
                <div class="publication-status">
                  <?php foreach ($form->getWidgetSchema()->publicationStatus->getChoices() as $value => $translatedText) { ?>
                    <label>
                      <input type="radio" name="publicationStatus" value="<?php echo $value; ?>" <?php echo $form->getValue('publicationStatus') == $value ? 'checked' : ''; ?>>
                      <?php echo $translatedText; ?>
                    </label>
                  <?php } ?>
                </div>
              </div>

            </div>

            <?php if (sfConfig::get('app_audit_log_enabled', false)) { ?>
              <div class="filter-row io-options">
                <div class="filter-left">
                  <?php echo $form->user
                      ->label(__('User'))
                      ->renderLabel(); ?>
                  <?php echo $form->user->render(['class' => 'form-autocomplete']); ?>
                  <input class="list" type="hidden" value="<?php echo url_for(['module' => 'user', 'action' => 'autocomplete']); ?>"/>

                  <?php if (isset($user)) { ?>
                    <div class="filter-description">
                      <?php echo __('Currently displaying:'); ?> <?php echo $user->getUsername(); ?></em>
                    </div>
                  <?php } ?>
                </div>
              </div>
            <?php } ?>
          </div>

          <p><?php echo __('Filter by date range:'); ?></p>

          <div class="criteria">

            <div class="filter-row double">

              <div class="filter-left">
                <?php echo $form->startDate
                    ->label(__('Start'))
                    ->renderRow(); ?>
              </div>

              <div class="filter-right">
                <?php echo $form->endDate
                    ->label(__('End'))
                    ->renderRow(); ?>
              </div>
            </div>
          </div>

          <ul class="actions nav gap-2 justify-content-center">
            <li><input type="submit" class="btn atom-btn-outline-light" value="<?php echo __('Search'); ?>"></li>
            <li><input type="button" class="btn atom-btn-outline-light reset" value="<?php echo __('Reset'); ?>"></li>
          </ul>

        </form>
      </div>
    </div>
  </div>
</div>
