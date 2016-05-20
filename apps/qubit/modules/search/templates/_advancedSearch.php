<section class="advanced-search-section">

  <a href="#" class="advanced-search-toggle <?php echo $show ? 'open' : '' ?>" aria-expanded="<?php echo $show ? 'true' : 'false' ?>"><?php echo __('Advanced search options') ?></a>

  <div class="advanced-search animateNicely" <?php echo !$show ? 'style="display: none;"' : '' ?>>

    <?php echo $form->renderFormTag(url_for(array('module' => 'informationobject', 'action' => 'browse')), array('name' => 'advanced-search-form', 'method' => 'get')) ?>

      <input type="hidden" name="showAdvanced" value="1"/>

      <?php foreach ($hiddenFields as $name => $value): ?>
        <input type="hidden" name="<?php echo $name ?>" value="<?php echo $value ?>"/>
      <?php endforeach; ?>

      <p><?php echo __('Find results with:') ?></p>

      <div class="criteria">

        <?php if (isset($criteria)): ?>

          <?php foreach ($criteria as $key => $item): ?>

            <div class="criterion">

              <select class="boolean" name="so<?php echo $key ?>">
                <option value="and"<?php echo $item['operator'] == 'and' ? ' selected="selected"' : '' ?>><?php echo __('and') ?></option>
                <option value="or"<?php echo $item['operator'] == 'or' ? ' selected="selected"' : '' ?>><?php echo __('or') ?></option>
                <option value="not"<?php echo $item['operator'] == 'not' ? ' selected="selected"' : '' ?>><?php echo __('not') ?></option>
              </select>

              <input class="query" type="text" placeholder="<?php echo __('Search') ?>" name="sq<?php echo $key ?>" value="<?php echo $item['query'] ?>"/>

              <span><?php echo __('in') ?></span>

              <select class="field" name="sf<?php echo $key ?>">
                <option value=""<?php echo $item['field'] == '' ? ' selected="selected"' : '' ?>><?php echo __('Any field') ?></option>
                <option value="title"<?php echo $item['field'] == 'title' ? ' selected="selected"' : '' ?>><?php echo __('Title') ?></option>
                <?php if (($template == 'rad' && check_field_visibility('app_element_visibility_rad_archival_history'))
                  || ($template == 'isad' && check_field_visibility('app_element_visibility_isad_archival_history'))
                  || ($template != 'isad' && $template != 'rad')): ?>
                  <option value="archivalHistory"<?php echo $item['field'] == 'archivalHistory' ? ' selected="selected"' : '' ?>><?php echo __('Archival history') ?></option>
                <?php endif; ?>
                <option value="scopeAndContent"<?php echo $item['field'] == 'scopeAndContent' ? ' selected="selected"' : '' ?>><?php echo __('Scope and content') ?></option>
                <option value="extentAndMedium"<?php echo $item['field'] == 'extentAndMedium' ? ' selected="selected"' : '' ?>><?php echo __('Extent and medium') ?></option>
                <option value="subject"<?php echo $item['field'] == 'subject' ? ' selected="selected"' : '' ?>><?php echo __('Subject access points') ?></option>
                <option value="name"<?php echo $item['field'] == 'name' ? ' selected="selected"' : '' ?>><?php echo __('Name access points') ?></option>
                <option value="place"<?php echo $item['field'] == 'place' ? ' selected="selected"' : '' ?>><?php echo __('Place access points') ?></option>
                <option value="genre"<?php echo $item['field'] == 'genre' ? ' selected="selected"' : '' ?>><?php echo __('Genre access points') ?></option>
                <option value="identifier"<?php echo $item['field'] == 'identifier' ? ' selected="selected"' : '' ?>><?php echo __('Identifier') ?></option>
                <option value="referenceCode"<?php echo $item['field'] == 'referenceCode' ? ' selected="selected"' : '' ?>><?php echo __('Reference code') ?></option>
                <option value="digitalObjectTranscript"<?php echo $item['field'] == 'digitalObjectTranscript' ? ' selected="selected"' : '' ?>><?php echo __('Digital object text') ?></option>
                <option value="findingAidTranscript"<?php echo $item['field'] == 'findingAidTranscript' ? ' selected="selected"' : '' ?>><?php echo __('Finding aid text') ?></option>
                <option value="allExceptFindingAidTranscript"<?php echo $item['field'] == 'allExceptFindingAidTranscript' ? ' selected="selected"' : '' ?>><?php echo __('Any field except finding aid text') ?></option>
              </select>

              <a href="#" class="delete-criterion"><i class="fa fa-times"></i></a>

            </div>

          <?php endforeach; ?>

        <?php endif; ?>

        <?php $count = isset($key) ? $key++ : 0 ?>

        <div class="criterion">

          <select class="boolean" name="so<?php echo $count ?>">
            <option value="and"><?php echo __('and') ?></option>
            <option value="or"><?php echo __('or') ?></option>
            <option value="not"><?php echo __('not') ?></option>
          </select>

          <input class="query" type="text" placeholder="<?php echo __('Search') ?>" name="sq<?php echo $count?>"/>

          <span><?php echo __('in') ?></span>

          <select class="field" name="sf<?php echo $count ?>">
            <option value=""><?php echo __('Any field') ?></option>
            <option value="title"><?php echo __('Title') ?></option>
            <?php if (($template == 'rad' && check_field_visibility('app_element_visibility_rad_archival_history'))
              || ($template == 'isad' && check_field_visibility('app_element_visibility_isad_archival_history'))
              || ($template != 'isad' && $template != 'rad')): ?>
              <option value="archivalHistory"><?php echo __('Archival history') ?></option>
            <?php endif; ?>
            <option value="scopeAndContent"><?php echo __('Scope and content') ?></option>
            <option value="extentAndMedium"><?php echo __('Extent and medium') ?></option>
            <option value="subject"><?php echo __('Subject access points') ?></option>
            <option value="name"><?php echo __('Name access points') ?></option>
            <option value="place"><?php echo __('Place access points') ?></option>
            <option value="genre"><?php echo __('Genre access points') ?></option>
            <option value="identifier"><?php echo __('Identifier') ?></option>
            <option value="referenceCode"><?php echo __('Reference code') ?></option>
            <option value="digitalObjectTranscript"><?php echo __('Digital object text') ?></option>
            <option value="findingAidTranscript"><?php echo __('Finding aid text') ?></option>
            <option value="allExceptFindingAidTranscript"><?php echo __('Any field except finding aid text') ?></option>
          </select>

          <a href="#" class="delete-criterion"><i class="fa fa-times"></i></a>

        </div>

        <div class="add-new-criteria">
          <div class="btn-group">
            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
              <?php echo __('Add new criteria') ?><span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="#" id="add-criterion-and"><?php echo __('And') ?></a></li>
              <li><a href="#" id="add-criterion-or"><?php echo __('Or') ?></a></li>
              <li><a href="#" id="add-criterion-not"><?php echo __('Not') ?></a></li>
            </ul>
          </div>
        </div>

      </div>

      <p><?php echo __('Limit results to:') ?></p>

      <div class="criteria">

        <?php if (sfConfig::get('app_multi_repository')): ?>
          <div class="filter-row">
            <div class="filter">
              <?php echo $form->repos
                ->label(__('Repository'))
                ->renderRow() ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="filter-row">
          <div class="filter">
            <?php echo $form->collection
              ->label(__('Top-level description'))
              ->renderLabel() ?>
            <?php echo $form->collection->render(array('class' => 'form-autocomplete')) ?>
            <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'informationobject', 'action' => 'autocomplete', 'parent' => QubitInformationObject::ROOT_ID, 'filterDrafts' => true)) ?>"/>
          </div>
        </div>

      </div>

      <p><?php echo __('Filter results by:') ?></p>

      <div class="criteria">

        <div class="filter-row triple">

          <div class="filter-left">
            <?php echo $form->levels
              ->label(__('Level of description'))
              ->renderRow() ?>
          </div>

          <div class="filter-center">
            <?php echo $form->onlyMedia
              ->label(__('%1% available', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))))
              ->renderRow() ?>
          </div>

          <div class="filter-right">
            <?php echo $form->findingAidStatus
              ->label(__('Finding aid'))
              ->renderRow() ?>
          </div>

        </div>

        <?php $showCopyright = sfConfig::get('app_toggleCopyrightFilter') ?>
        <?php $showMaterial  = sfConfig::get('app_toggleMaterialFilter') ?>

        <?php if ($showCopyright || $showMaterial): ?>
          <div class="filter-row">

            <?php if ($showCopyright): ?>
              <div class="filter<?php echo $showMaterial ? '-left' : '' ?>">
                <?php echo $form->copyrightStatus
                  ->label(__('Copyright status'))
                  ->renderRow() ?>
              </div>
            <?php endif; ?>

            <?php if ($showMaterial): ?>
              <div class="filter<?php echo $showCopyright ? '-right' : '' ?>">
                <?php echo $form->materialType
                  ->label(__('General material designation'))
                  ->renderRow() ?>
              </div>
            <?php endif; ?>

          </div>
        <?php endif; ?>

        <div class="filter-row">

          <div class="lod-filter">
            <label>
              <input type="radio" name="topLod" value="1" <?php echo $topLod ? 'checked' : '' ?>>
              <?php echo __('Top-level descriptions') ?>
            </label>
            <label>
              <input type="radio" name="topLod" value="0" <?php echo !$topLod ? 'checked' : '' ?>>
              <?php echo __('All descriptions') ?>
            </label>
          </div>

        </div>

      </div>

      <p><?php echo __('Filter by date range:') ?></p>

      <div class="criteria">

        <div class="filter-row">

          <div class="start-date">
            <?php echo $form->startDate
              ->label(__('Start'))
              ->renderRow() ?>
          </div>

          <div class="end-date">
            <?php echo $form->endDate
              ->label(__('End'))
              ->renderRow() ?>
          </div>

          <div class="date-type">
            <label>
              <input type="radio" name="rangeType" value="inclusive" <?php echo $rangeType == 'inclusive' ? 'checked' : '' ?>>
              <?php echo __('Overlapping') ?>
            </label>
            <label>
              <input type="radio" name="rangeType" value="exact" <?php echo $rangeType == 'exact' ? 'checked' : '' ?>>
              <?php echo __('Exact') ?>
            </label>
          </div>

          <a href="#" class="date-range-help-icon" aria-expanded="false"><i class="fa fa-question-circle"></i></a>

        </div>

        <div class="alert alert-info date-range-help animateNicely">
          <?php echo __('Use these options to specify how the date range returns results. "Exact" means that the start and end dates of descriptions returned must fall entirely within the date range entered. "Overlapping" means that any description whose start or end dates touch or overlap the target date range will be returned.') ?>
        </div>

      </div>

      <section class="actions">
        <input type="submit" class="c-btn c-btn-submit" value="<?php echo __('Search') ?>"/>
        <input type="button" class="reset c-btn c-btn-delete" value="<?php echo __('Reset') ?>"/>
      </section>

    </form>

  </div>

</section>
