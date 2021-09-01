<section class="advanced-search-section" role="search" aria-label="<?php echo __('Advanced %1%', ['%1%' => sfConfig::get('app_ui_label_informationobject')]); ?>">

  <a href="#" class="advanced-search-toggle <?php echo $show ? 'open' : ''; ?>" aria-expanded="<?php echo $show ? 'true' : 'false'; ?>"><?php echo __('Advanced search options'); ?></a>

  <div class="advanced-search animateNicely" <?php echo !$show ? 'style="display: none;"' : ''; ?>>

    <?php echo $form->renderFormTag(url_for(['module' => 'informationobject', 'action' => 'browse']), ['name' => 'advanced-search-form', 'method' => 'get']); ?>

      <?php foreach ($hiddenFields as $name => $value) { ?>
        <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>"/>
      <?php } ?>

      <p><?php echo __('Find results with:'); ?></p>

      <div class="criteria">

        <?php if (isset($criteria)) { ?>

          <?php foreach ($criteria as $key => $item) { ?>

            <div class="criterion">

              <select class="boolean" name="so<?php echo $key; ?>">
                <option value="and"<?php echo 'and' == $item['operator'] ? ' selected="selected"' : ''; ?>><?php echo __('and'); ?></option>
                <option value="or"<?php echo 'or' == $item['operator'] ? ' selected="selected"' : ''; ?>><?php echo __('or'); ?></option>
                <option value="not"<?php echo 'not' == $item['operator'] ? ' selected="selected"' : ''; ?>><?php echo __('not'); ?></option>
              </select>

              <input class="query" type="text" aria-label="<?php echo __('Search'); ?>" placeholder="<?php echo __('Search'); ?>" name="sq<?php echo $key; ?>" value="<?php echo $item['query']; ?>"/>

              <span><?php echo __('in'); ?></span>

              <select class="field" name="sf<?php echo $key; ?>">
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
                <option value="alternativeIdentifiersLabel"<?php echo 'alternativeIdentifiersLabel' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Alternative identifier label'); ?></option>
                <option value="alternativeIdentifiersIdentifier"<?php echo 'alternativeIdentifiersIdentifier' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Alternative identifier'); ?></option>
                <option value="allExceptFindingAidTranscript"<?php echo 'allExceptFindingAidTranscript' == $item['field'] ? ' selected="selected"' : ''; ?>><?php echo __('Any field except finding aid text'); ?></option>
              </select>

              <a href="#" class="delete-criterion" aria-label="<?php echo __('Delete criterion'); ?>"><i aria-hidden="true" class="fa fa-times"></i></a>

            </div>

          <?php } ?>

        <?php } ?>

        <?php $count = isset($key) ? $key++ : 0; ?>

        <div class="criterion">

          <select class="boolean" name="so<?php echo $count; ?>">
            <option value="and"><?php echo __('and'); ?></option>
            <option value="or"><?php echo __('or'); ?></option>
            <option value="not"><?php echo __('not'); ?></option>
          </select>

          <input class="query" aria-label="<?php echo __('Search'); ?>" type="text" placeholder="<?php echo __('Search'); ?>" name="sq<?php echo $count; ?>"/>

          <span><?php echo __('in'); ?></span>

          <select class="field" name="sf<?php echo $count; ?>">
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

          <a href="#" class="delete-criterion" aria-label="<?php echo __('Delete criterion'); ?>"><i aria-hidden="true" class="fa fa-times"></i></a>

        </div>

        <div class="add-new-criteria">
          <div class="btn-group">
            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
              <?php echo __('Add new criteria'); ?><span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="#" id="add-criterion-and"><?php echo __('And'); ?></a></li>
              <li><a href="#" id="add-criterion-or"><?php echo __('Or'); ?></a></li>
              <li><a href="#" id="add-criterion-not"><?php echo __('Not'); ?></a></li>
            </ul>
          </div>
        </div>

      </div>

      <p><?php echo __('Limit results to:'); ?></p>

      <div class="criteria">

        <?php if (sfConfig::get('app_multi_repository')) { ?>
          <div class="filter-row">
            <div class="filter">
              <?php echo $form->repos
                  ->label(__('Repository'))
                  ->renderRow(); ?>
            </div>
          </div>
        <?php } ?>

        <div class="filter-row">
          <div class="filter">
            <?php echo $form->collection
                ->label(__('Top-level description'))
                ->renderLabel(); ?>
            <?php echo $form->collection->render(['class' => 'form-autocomplete']); ?>
            <input class="list" type="hidden" value="<?php echo url_for(['module' => 'informationobject', 'action' => 'autocomplete', 'parent' => QubitInformationObject::ROOT_ID, 'filterDrafts' => true]); ?>"/>
          </div>
        </div>

      </div>

      <p><?php echo __('Filter results by:'); ?></p>

      <div class="criteria">

        <div class="filter-row triple">

          <div class="filter-left">
            <?php echo $form->levels
                ->label(__('Level of description'))
                ->renderRow(); ?>
          </div>

          <div class="filter-center">
            <?php echo $form->onlyMedia
                ->label(__('%1% available', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]))
                ->renderRow(); ?>
          </div>

          <div class="filter-right">
            <?php echo $form->findingAidStatus
                ->label(__('Finding aid'))
                ->renderRow(); ?>
          </div>

        </div>

        <?php $showCopyright = sfConfig::get('app_toggleCopyrightFilter'); ?>
        <?php $showMaterial = sfConfig::get('app_toggleMaterialFilter'); ?>

        <?php if ($showCopyright || $showMaterial) { ?>
          <div class="filter-row">

            <?php if ($showCopyright) { ?>
              <div class="filter<?php echo $showMaterial ? '-left' : ''; ?>">
                <?php echo $form->copyrightStatus
                    ->label(__('Copyright status'))
                    ->renderRow(); ?>
              </div>
            <?php } ?>

            <?php if ($showMaterial) { ?>
              <div class="filter<?php echo $showCopyright ? '-right' : ''; ?>">
                <?php echo $form->materialType
                    ->label(__('General material designation'))
                    ->renderRow(); ?>
              </div>
            <?php } ?>

          </div>
        <?php } ?>

        <div class="filter-row">

          <div class="lod-filter">
            <label>
              <input type="radio" name="topLod" value="1" <?php echo $topLod ? 'checked' : ''; ?>>
              <?php echo __('Top-level descriptions'); ?>
            </label>
            <label>
              <input type="radio" name="topLod" value="0" <?php echo !$topLod ? 'checked' : ''; ?>>
              <?php echo __('All descriptions'); ?>
            </label>
          </div>

        </div>

      </div>

      <p><?php echo __('Filter by date range:'); ?></p>

      <div class="criteria">

        <div class="filter-row">

          <div class="start-date">
            <?php echo $form->startDate
                ->label(__('Start'))
                ->renderRow(); ?>
          </div>

          <div class="end-date">
            <?php echo $form->endDate
                ->label(__('End'))
                ->renderRow(); ?>
          </div>

          <div class="date-type">
            <label>
              <input type="radio" name="rangeType" value="inclusive" <?php echo 'inclusive' == $rangeType ? 'checked' : ''; ?>>
              <?php echo __('Overlapping'); ?>
            </label>
            <label>
              <input type="radio" name="rangeType" value="exact" <?php echo 'exact' == $rangeType ? 'checked' : ''; ?>>
              <?php echo __('Exact'); ?>
            </label>
          </div>

          <a href="#" class="date-range-help-icon" aria-expanded="false" aria-label="<?php echo __('Help'); ?>"><i aria-hidden="true" class="fa fa-question-circle"></i></a>

        </div>

        <div class="alert alert-info date-range-help animateNicely">
          <?php echo __('Use these options to specify how the date range returns results. "Exact" means that the start and end dates of descriptions returned must fall entirely within the date range entered. "Overlapping" means that any description whose start or end dates touch or overlap the target date range will be returned.'); ?>
        </div>

      </div>

      <section class="actions">
        <input type="submit" class="c-btn c-btn-submit" value="<?php echo __('Search'); ?>"/>
        <input type="button" class="reset c-btn c-btn-delete" value="<?php echo __('Reset'); ?>"/>
      </section>

    </form>

  </div>

</section>
