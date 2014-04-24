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
    <div id="content">
      <fieldset class="collapsible">
        <legend><?php echo __('Rights Basis') ?></legend>

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

      <fieldset class="collapsible">
        <legend><?php echo __('Act / Granted Rights') ?></legend>


        <?php foreach ($form['grantedRights'] as $i => $gr): ?>
          <?php if($i+1 < sizeof($form['grantedRights'])): ?>
            <fieldset class="collapsible <?php echo ($i < sizeof($form['grantedRights']) ? ''  : 'collapsed') ?>">
              <legend><?php echo "Item ".($i+1) ?></legend>
              <?php echo $gr['id']->render() ?>

              <?php echo $gr['act']->renderRow() ?>

              <?php echo $gr['restriction']->renderRow() ?>

              <?php echo $gr['startDate']->renderRow() ?>

              <?php echo $gr['endDate']->renderRow() ?>
            </fieldset>
          <?php else: ?>
            <fieldset class="collapsible collapsed">
              <legend class="newItem">New Item</legend>
              <?php echo $gr['id']->render() ?>

              <?php echo $gr['act']->renderRow() ?>

              <?php echo $gr['restriction']->renderRow() ?>

              <?php echo $gr['startDate']->renderRow() ?>

              <?php echo $gr['endDate']->renderRow() ?>
            </fieldset>
          <?php endif; ?>
        <?php endforeach; ?>

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

      jQuery('#right_basis').on('change', BasisSelect.update).trigger('change');

      jQuery('#content').on('click', '.newItem a span', function(){ console.log('test'); });

    })();
  </script>

<?php end_slot() ?>