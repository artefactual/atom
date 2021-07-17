<section class="advanced-search-section" role="search" aria-label="<?php echo __('Advanced %1%', ['%1%' => sfConfig::get('app_ui_label_actor')]); ?>">

  <a href="#" class="advanced-search-toggle <?php echo $show ? 'open' : ''; ?>" aria-expanded="<?php echo $show ? 'true' : 'false'; ?>"><?php echo __('Advanced search options'); ?></a>

  <div class="advanced-search animateNicely" <?php echo !$show ? 'style="display: none;"' : ''; ?>>

    <?php echo $form->renderFormTag(url_for(['module' => 'actor', 'action' => 'browse']), ['name' => 'advanced-search-form', 'method' => 'get']); ?>

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
                <?php foreach ($fieldOptions as $name => $label) { ?>
                  <option value="<?php echo $name; ?>"<?php echo $item['field'] == $name ? ' selected="selected"' : ''; ?>><?php echo $label; ?></option>
                <?php } ?>
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

          <input class="query" type="text" aria-label="<?php echo __('Search'); ?>" placeholder="<?php echo __('Search'); ?>" name="sq<?php echo $count; ?>"/>

          <span><?php echo __('in'); ?></span>

          <select class="field" name="sf<?php echo $count; ?>">
            <option value=""><?php echo __('Any field'); ?></option>
            <?php foreach ($fieldOptions as $name => $label) { ?>
              <option value="<?php echo $name; ?>"><?php echo $label; ?></option>
            <?php } ?>
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

      <?php if (sfConfig::get('app_multi_repository')) { ?>
        <div class="criteria">

          <div class="filter-row">
            <div class="filter">
              <?php echo $form->repository
                  ->label(__('Repository'))
                  ->renderRow(); ?>
            </div>
          </div>

        </div>
      <?php } ?>

      <p><?php echo __('Filter results by:'); ?></p>

      <div class="criteria">

        <div class="filter-row triple">

          <div class="filter-left">
            <?php echo $form->hasDigitalObject
                ->label(__('%1% available', ['%1%' => sfConfig::get('app_ui_label_digitalobject')]))
                ->renderRow(); ?>
          </div>

          <div class="filter-center">
            <?php echo $form->entityType
                ->label(__('%1% available', ['%1%' => __('Entity type')]))
                ->renderRow(); ?>
          </div>

          <div class="filter-right">
            <?php echo $form->emptyField
                ->label(__('Empty field'))
                ->renderRow(); ?>
          </div>

        </div>
      </div>

      <p><?php echo __('Find results where:'); ?></p>

      <div class="criteria">

        <div class="filter-row">

          <div class="filter-left relation">
            <?php echo $form->relatedType
                ->label(__('Relationship'))
                ->renderRow(); ?>
          </div>

          <div class="filter-right relation">
            <?php echo $form->relatedAuthority
                ->label(__('Related %1%', ['%1%' => sfConfig::get('app_ui_label_actor')]))
                ->renderLabel(); ?>
            <?php echo $form->relatedAuthority->render(['class' => 'form-autocomplete']); ?>
            <input class="list" type="hidden" value="<?php echo url_for(['module' => 'actor', 'action' => 'autocomplete']); ?>"/>
          </div>

        </div>
      </div>

      <ul class="actions nav gap-2 justify-content-center">
        <li><input type="submit" class="btn atom-btn-outline-light" value="<?php echo __('Search'); ?>"></li>
        <li><input type="button" class="btn atom-btn-outline-light reset" value="<?php echo __('Reset'); ?>"></li>
      </ul>

    </form>

  </div>
</section>
