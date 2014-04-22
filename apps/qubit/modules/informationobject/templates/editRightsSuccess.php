<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo render_title($resource) ?>
    <span class="sub">Rights Management</span>
  </h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderGlobalErrors() ?>

  <form method="post">

    <?php echo $form->renderHiddenFields() ?>

    <div id="content">
      <fieldset class="collapsible">
        <legend><?php echo __('Add New Rights Basis') ?></legend>

          <?php echo $form->basis
            ->renderRow() ?>

          <?php echo $form->copyrightStatus
            ->renderRow() ?>

          <?php echo $form->copyrightStatusDate->renderRow() ?>

          <?php echo $form->copyrightJurisdiction

            ->renderRow() ?>

          <?php echo $form->copyrightNote
            ->renderRow() ?>

          <?php echo $form->licenseIdentifier
            ->renderRow() ?>

          <?php echo $form->licenseTerms
            ->renderRow() ?>

          <?php echo $form->licenseNote
            ->renderRow() ?>

          <?php echo $form->statuteJurisdiction
            ->renderRow() ?>

          <?php echo $form->statuteCitation
            ->renderRow() ?>

          <?php echo $form->statuteDeterminationDate
            ->renderRow() ?>

          <?php echo $form->statuteNote
            ->renderRow() ?>

          <?php echo $form->act
            ->renderRow() ?>

          <?php echo $form->restriction
            ->renderRow() ?>

          <?php echo $form->startDate
            ->renderRow() ?>

          <?php echo $form->endDate
            ->renderRow() ?>

          <div class="form-item">
            <?php echo $form->rightsHolder->renderLabel() ?>
            <?php echo $form->rightsHolder->render(array('class' => 'form-autocomplete')) ?>
            <input class="add" type="hidden" value="<?php echo url_for(array('module' => 'rightsholder', 'action' => 'add')) ?> #authorizedFormOfName"/>
            <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'rightsholder', 'action' => 'autocomplete')) ?>"/>
          </div>

          <?php echo $form->rightsNote
            ->label(__('Rights note(s)'))
            ->renderRow() ?>


      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>

  </form>

  <script type="text/javascript">
    (function(){
      BasisSelect = {
        fieldsets: {
          copyright: jQuery('#content div[class *= form-item-copyright]'),
          license: jQuery('#content div[class *= form-item-license]'),
          statute: jQuery('#content div[class *= form-item-statute]'),
        },

        update: function()
        {
          var selectValue = jQuery(this).attr('value').match('[^/]*$')[0];
          jQuery.each(BasisSelect.fieldsets, function(value, fields) {
            console.log([selectValue, value]);
            fields.toggle(selectValue == value);
          });
        }
      }

      jQuery('#editRight_basis').on('change', BasisSelect.update).trigger('change');

    })();
  </script>

<?php end_slot() ?>