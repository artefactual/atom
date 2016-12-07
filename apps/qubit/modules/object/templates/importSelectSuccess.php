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

  <?php if (isset($resource)): ?>
    <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'object', 'action' => 'importSelect')), array('enctype' => 'multipart/form-data')) ?>
  <?php else: ?>
    <?php echo $form->renderFormTag(url_for(array('module' => 'object', 'action' => 'importSelect')), array('enctype' => 'multipart/form-data')) ?>
  <?php endif; ?>

    <?php echo $form->renderHiddenFields() ?>

    <section id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Import options') ?></legend>

        <input type="hidden" name="importType" value="<?php echo esc_entities($type) ?>"/>

        <?php if ('csv' == $type): ?>
          <div class="form-item">
            <label><?php echo __('Type') ?></label>
            <select name="objectType">
              <option value="informationObject"><?php echo sfConfig::get('app_ui_label_informationobject') ?></option>
              <option value="accession"><?php echo sfConfig::get('app_ui_label_accession', __('Accession')) ?></option>
              <option value="authorityRecord"><?php echo sfConfig::get('app_ui_label_actor') ?></option>
              <option value="event"><?php echo sfConfig::get('app_ui_label_event', __('Event')) ?></option>
              <option value="repository"><?php echo sfConfig::get('app_ui_label_repository', __('Repository')) ?></option>
            </select>
          </div>
        <?php endif; ?>

        <?php if ('csv' != $type): ?>
          <div class="form-item">
            <label><?php echo __('Type') ?></label>
            <select name="objectType">
              <option value="ead"><?php echo __('EAD 2002') ?></option>
              <option value="eac-cpf"><?php echo __('EAC CPF') ?></option>
              <option value="mods"><?php echo __('MODS') ?></option>
              <option value="dc"><?php echo __('DC') ?></option>
            </select>

            <p class="alert alert-info text-center"><?php echo __('If you are importing a SKOS file to a taxonomy other than subjects, please go to the %1%', array('%1%' => link_to(__('SKOS import page'), array('module' => 'sfSkosPlugin', 'action' => 'import')))) ?></p>
          </div>
        <?php endif; ?>


        <div id="updateBlock">

          <?php if ('csv' == $type): ?>
            <div class="form-item">
              <label><?php echo __('Update behaviours') ?></label>
              <select name="updateType">
                <option value="import-as-new"><?php echo __('Ignore matches and create new records on import') ?></option>
                <option value="match-and-update"><?php echo __('Update matches ignoring blank fields in CSV') ?></option>
                <option value="delete-and-replace"><?php echo __('Delete matches and replace with imported records') ?></option>
              </select>
            </div>
          <?php endif; ?>

          <?php if ('csv' != $type): ?>
            <div class="form-item">
              <label><?php echo __('Update behaviours') ?></label>
              <select name="updateType">
                <option value="import-as-new"><?php echo __('Ignore matches and import as new') ?></option>
                <option value="delete-and-replace"><?php echo __('Delete matches and replace with imports') ?></option>
              </select>
            </div>
          <?php endif; ?>

          <div class="form-item">

            <div class="panel panel-default" id="matchingOptions" style="display:none;">
              <div class="panel-body">
                <label>
                  <input name="skipUnmatched" type="checkbox"/>
                  <?php echo __('Skip unmatched records') ?>
                </label>

                <div class="criteria">
                  <div class="filter-row repos-limit">
                    <div class="filter">
                      <?php echo $form->repos
                        ->label(__('Limit matches to:'))
                        ->renderRow() ?>
                    </div>
                  </div>

                  <div class="filter-row collection-limit">
                    <div class="filter">
                      <?php echo $form->collection
                        ->label(__('Top-level description'))
                        ->renderLabel() ?>
                      <?php echo $form->collection->render(array('class' => 'form-autocomplete')) ?>
                      <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'informationobject', 'action' => 'autocomplete', 'parent' => QubitInformationObject::ROOT_ID, 'filterDrafts' => true)) ?>"/>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="panel panel-default" id="importAsNewOptions">
              <div class="panel-body">
                <label>
                  <input name="skipMatched" type="checkbox"/>
                  <?php echo __('Skip matched records') ?>
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="form-item" id="noIndex">
          <label>
            <input name="noIndex" type="checkbox"/>
            <?php echo __('Do not index imported items') ?>
          </label>
        </div>

        <?php if ('csv' == $type && sfConfig::get('app_csv_transform_script_name')): ?>
          <div class="form-item">
            <label>
              <input name="doCsvTransform" type="checkbox"/>
              <?php echo __('Include transformation script') ?>
              <div class="pull-right">
                <?php echo __(sfConfig::get('app_csv_transform_script_name')) ?>
              </div>
            </label>
          </div>
        <?php endif; ?>
      </fieldset>

      <fieldset class="collapsible">
        <legend><?php echo __('Select file') ?></legend>

        <div class="form-item">
          <label><?php echo __('Select a file to import') ?></label>
          <input name="file" type="file"/>
        </div>
      </fieldset>

    </section>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Import') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
