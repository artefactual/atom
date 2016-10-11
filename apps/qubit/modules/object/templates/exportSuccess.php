<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <?php if (isset($resource)): ?>
    <h1 class="multiline">
      <?php echo $title ?>
      <span class="sub"><?php echo render_title($resource) ?></span>
    </h1>
  <?php else: ?>
    <h1><?php echo $title ?></h1>
  <?php endif; ?>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php if ($sf_user->hasFlash('error')): ?>
    <div class="messages error">
      <h3><?php echo __('Error encountered') ?></h3>
      <div><?php echo $sf_user->getFlash('error', ESC_RAW) ?></div>
    </div>
  <?php endif; ?>

  <?php echo $form->renderFormTag(url_for(array('module' => 'object', 'action' => 'export'))) ?>

    <?php echo $form->renderHiddenFields() ?>

    <section id="content">
      <div id="export-options" data-export-toggle="tooltip" data-export-title="<?php echo __('Export') ?>" data-export-alert-message="<?php echo __('Error: Must select a level of description or include all descendants.') ?>">
      <fieldset class="collapsible">

        <legend><?php echo __('Export options') ?></legend>

        <input type="hidden" name="exportType" value="<?php echo esc_entities($type) ?>"/>

        <div class="form-item">
          <label><?php echo __('Type') ?></label>
          <select name="objectType">
            <option value="informationObject"><?php echo sfConfig::get('app_ui_label_informationobject') ?></option>
            <option value="authorityRecord"><?php echo sfConfig::get('app_ui_label_actor') ?></option>
            <option value="repository"><?php echo sfConfig::get('app_ui_label_repository') ?></option>
          </select>
        </div>

        <div class="form-item">
          <label><?php echo __('Format') ?></label>
          <?php if ('csv' == $type): ?>
            <select name="format">
              <option value="xml"><?php echo __('XML') ?></option>
              <option value="csv" selected="selected"><?php echo __('CSV') ?></option>
            </select>
          <?php endif; ?>

          <?php if ('csv' != $type): ?>
            <select name="format">
              <option value="xml" selected="selected"><?php echo __('XML') ?></option>
              <option value="csv"><?php echo __('CSV') ?></option>
            </select>
          <?php endif; ?>
        </div>

        <div class="form-item">
          <div class="panel panel-default" id="exportOptions">
            <div class="panel-body">
              <label>
                <input name="includeDescendants" type="checkbox"/>
                <?php echo __('Include descendants') ?>
              </label>
              <label>
                <input name="includeDrafts" type="checkbox"/>
                <?php echo __('Include draft records') ?>
              </label>
              <label>
                <input name="includeAllLevels" type="checkbox"/>
                <?php echo __('Include all levels of description') ?>
              </label>
              <div class="hidden" id="exportLevels">
                <?php echo $form->levels
                  ->label(__('Levels of description'))
                  ->help(__('Select the levels of description to be included in the export. If no levels are selected, the export will fail. You can use the control (Mac ⌘) and/or shift keys to multi-select values from the Levels of description menu. Descriptions that are descendants of levels not included in the export will also be excluded.'))
                  ->renderRow(array('class' => 'form-autocomplete')) ?>
              </div>
              <br />
              <?php $taxonomy = QubitTaxonomy::getById(QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID) ?>

            </div>
          </div>
        </div>


      </fieldset>
      </div>
    </section>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" id="exportSubmit" value="<?php echo __('Export') ?>"/></li>
        <li><?php echo link_to(__('Cancel'), array('module' => 'user', 'action' => 'clipboard'), array('class' => 'c-btn')) ?></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
