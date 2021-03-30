<section class="advanced-search-section" id="description-updates-section">

  <a href="#" class="advanced-search-toggle <?php echo $show ? 'open' : ''; ?>" aria-expanded="<?php echo $show ? 'true' : 'false'; ?>"><?php echo __('Filter options'); ?></a>

  <div class="advanced-search animateNicely" <?php echo !$show ? 'style="display: none;"' : ''; ?>>

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

      <section class="actions">
        <input type="submit" class="c-btn c-btn-submit" value="<?php echo __('Search'); ?>"/>
        <input type="button" class="reset c-btn c-btn-delete" value="<?php echo __('Reset'); ?>"/>
      </section>

    </form>
  </div>
</section>
