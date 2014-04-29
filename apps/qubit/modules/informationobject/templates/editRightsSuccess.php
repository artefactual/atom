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
            ->help(__('Basis for the permissions granted or for the restriction of rights'))
            ->renderRow() ?>

          <?php echo $form->copyrightStatus
            ->help(__('A coded designation for the copyright status of the object at the time the rights statement is recorded.'))
            ->renderRow() ?>

          <?php echo $form->copyrightStatusDate
            ->help(__('The date the copyright status applies.'))
            ->renderRow() ?>

          <?php echo $form->copyrightJurisdiction
            ->help(__('The country whose copyright laws apply.'))
            ->renderRow() ?>

          <?php echo $form->copyrightNote
            ->help(__('Notes regarding the copyright.'))
            ->renderRow() ?>

          <?php echo $form->licenseIdentifier
            ->help(__('Can be text value or URI (e.g. to Creative Commons, GNU or other online licenses). Used to identify the granting agreement uniquely within the repository system.'))
            ->renderRow() ?>

          <?php echo $form->licenseTerms
            ->help(__('Text describing the license or agreement by which permission was granted or link to full-text hosted online. This can contain the actual text of the license or agreement or a paraphrase or summary.'))
            ->renderRow() ?>

          <?php echo $form->licenseNote
            ->help(__('Additional information about the license, such as contact persons, action dates, or interpretations. The note may also indicated the location of the license, if it is available online or embedded in the object itself.'))
            ->renderRow() ?>

          <?php echo $form->statuteJurisdiction
            ->help(__('The country or other political body that has enacted the statute.'))
            ->renderRow() ?>

          <?php echo $form->statuteCitation
            ->help(__('An identifying designation for the statute. Use standard citation form when applicable, e.g. bibliographic citation.'))
            ->renderRow() ?>

          <?php echo $form->statuteDeterminationDate
            ->help(__('Date that the decision to ascribe the right to this statute was made. As context for any future review/re-interpretation.'))
            ->renderRow() ?>

          <?php echo $form->statuteNote
            ->help(__('Additional information about the statute.'))
            ->renderRow() ?>

          <?php echo $form->startDate
            ->help(__('The beginning date of the permission granted.'))
            ->renderRow() ?>

          <?php echo $form->endDate
            ->help(__('The ending date of the permission granted. Omit end date if the ending date is unknown.'))
            ->renderRow() ?>

          <div class="form-item">
            <?php echo $form->rightsHolder->renderLabel() ?>
            <?php echo $form->rightsHolder->render(array('class' => 'form-autocomplete')) ?>
            <input class="add" type="hidden" value="<?php echo url_for(array('module' => 'rightsholder', 'action' => 'add')) ?> #authorizedFormOfName"/>
            <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'rightsholder', 'action' => 'autocomplete')) ?>"/>
            <?php echo $form->rightsHolder
              ->help(__('Name of the person(s) or organization(s) which has the authority to grant permissions or set rights restrictions.'))
              ->renderHelp() ?>
          </div>

          <?php echo $form->rightsNote
            ->help(__('Notes for this Rights Basis.'))
            ->label(__('Rights note(s)'))->renderRow() ?>


      </fieldset>

      <fieldset class="collapsible">
        <legend><?php echo __('Act / Granted Rights') ?></legend>


        <?php foreach ($form['grantedRights'] as $i => $gr): ?>
          <?php $collapsed = ($i+1 < sizeof($form['grantedRights']) ? ' collapsed' : '') ?>
          <fieldset class="collapsible<?php echo $collapsed ?>">
            <?php 
              // build a title
              if( $gr['act']->getValue() && $gr['restriction']->getValue() !== null )
              {
                $act = $this->context->routing->parse(Qubit::pathInfo($gr['act']->getValue()));
                $act = $act['_sf_route']->resource;
                $restriction = $gr['restriction']->getValue() === '0' ? __('Disallow') : __('Allow');
                $title = "{$act} {$restriction}";
              } else {
                $title = "Granted Right ".($i+1);
              }
            ?>
            <legend><?php echo $title ?></legend>
            <?php echo $gr['id']->render() ?>
            <?php echo $gr['act']
               ->renderRow(null, null, __('The action which is permitted or restricted.')) ?>
            <?php echo $gr['restriction']
              ->renderRow(null, null, __('A condition or limitation on the act.')) ?>
            <?php echo $gr['startDate']
              ->renderRow(null, null, __('The beginning date of the permission granted.')) ?>
            <?php echo $gr['endDate']
              ->renderRow(null, null, __('The ending date of the permission granted. Omit end date if the ending date is unknown.')) ?>
            <?php echo $gr['notes']
              ->renderRow(null, null, __('Notes for this Granted Right.')) ?>
          </fieldset>
        <?php endforeach; ?>

        <fieldset class="collapsible" id="blank">
          <legend>Blank Item</legend>
            <?php echo $form['blank']['id']->render() ?>
            <?php echo $form['blank']['act']
              ->renderRow(null, null, __('The action which is permitted or restricted.')) ?>
            <?php echo $form['blank']['restriction']
              ->renderRow(null, null, __('A condition or limitation on the act.')) ?>
            <?php echo $form['blank']['startDate']
              ->renderRow(null, null, __('The beginning date of the permission granted.')) ?>
            <?php echo $form['blank']['endDate']
              ->renderRow(null, null, __('The ending date of the permission granted. Omit end date if the ending date is unknown.')) ?>
            <?php echo $form['blank']['notes']
              ->renderRow(null, null, __('Notes for this Granted Right.')) ?>
        </fieldset>

        <fieldset>
          <legend><a class="newItem">New Granted Right</a></legend>
        </fieldset>

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

      jQuery('#blank').toggle(false);

      jQuery('#right_basis').on('change', BasisSelect.update).trigger('change');

      jQuery('#content').on('click', 'a.newItem', function(){
        var blank = jQuery('#blank');
        var added = blank.clone().insertBefore(blank);
        // fix the added fieldset: name attributes, etc
        added.removeAttr('id');
        var count = jQuery('.fieldset-wrapper fieldset').length - 3;
        added.find('[name]').each(function(){
          $this = jQuery(this);
          $this.attr('name', $this.attr('name').replace('[blank]', '[grantedRights]['+count+']'));
          // right_blank_act becomes right_grantedRights_0_act
          $this.attr('id', $this.attr('id').replace('_blank_', '_grantedRights_'+count+'_'));
        })
        added.find('legend').replaceWith("<legend>New Granted Right "+(count+1)+"</legend>");
        Drupal.behaviors.collapse.attach();
        Drupal.behaviors.description.attach();
        added.toggle(true);
      });

    })();
  </script>

<?php end_slot() ?>