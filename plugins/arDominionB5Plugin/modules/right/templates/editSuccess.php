<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1 class="multiline">
    <?php echo render_title($resource); ?>
    <span class="sub"><?php echo __('Rights management'); ?></span>
  </h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <form method="post">

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="basis-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#basis-collapse" aria-expanded="true" aria-controls="basis-collapse">
            <?php echo __('Rights basis'); ?>
          </button>
        </h2>
        <div id="basis-collapse" class="accordion-collapse collapse show" aria-labelledby="basis-heading">
          <div class="accordion-body">
            <?php echo $form->basis
                ->help(__('Basis for the permissions granted or for the restriction of rights'))
                ->renderRow(); ?>

            <?php echo $form->copyrightStatus
                ->help(__('A coded designation for the copyright status of the object at the time the rights statement is recorded.'))
                ->renderRow(); ?>

            <?php echo $form->copyrightStatusDate
                ->help(__('The date the copyright status applies.'))
                ->renderRow(); ?>

            <?php echo $form->copyrightJurisdiction
                ->help(__('The country whose copyright laws apply.'))
                ->renderRow(); ?>

            <?php echo $form->copyrightNote
                ->help(__('Notes regarding the copyright.'))
                ->renderRow(); ?>

            <?php echo $form->licenseTerms
                ->help(__('Text describing the license or agreement by which permission was granted or link to full-text hosted online. This can contain the actual text of the license or agreement or a paraphrase or summary.'))
                ->renderRow(); ?>

            <?php echo $form->licenseNote
                ->help(__('Additional information about the license, such as contact persons, action dates, or interpretations. The note may also indicated the location of the license, if it is available online or embedded in the object itself.'))
                ->renderRow(); ?>

            <?php echo $form->statuteJurisdiction
                ->help(__('The country or other political body that has enacted the statute.'))
                ->renderRow(); ?>

            <div class="form-row form-row-statuteCitation">
              <?php echo $form->statuteCitation->renderLabel(); ?>
              <?php echo $form->statuteCitation->render(['class' => 'form-autocomplete']); ?>
              <?php if (QubitAcl::check(QubitTaxonomy::getById(QubitTaxonomy::RIGHTS_STATUTES_ID), 'createTerm')) { ?>
                <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(['module' => 'term', 'action' => 'add', 'taxonomy' => url_for([QubitTaxonomy::getById(QubitTaxonomy::RIGHTS_STATUTES_ID), 'module' => 'taxonomy'])]); ?> #name"/>
              <?php } ?>
              <input class="list" type="hidden" value="<?php echo url_for(['module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for([QubitTaxonomy::getById(QubitTaxonomy::RIGHTS_STATUTES_ID), 'module' => 'taxonomy'])]); ?>"/>
              <?php echo $form->statuteCitation
                  ->help(__('An identifying designation for the statute. Use standard citation form when applicable, e.g. bibliographic citation.'))
                  ->renderHelp(); ?>
            </div>

            <?php echo $form->statuteDeterminationDate
                ->help(__('Date that the decision to ascribe the right to this statute was made. As context for any future review/re-interpretation.'))
                ->renderRow(); ?>

            <?php echo $form->statuteNote
                ->help(__('Additional information about the statute.'))
                ->renderRow(); ?>

            <?php echo $form->startDate
                ->help(__('Enter the copyright start date, if known. Acceptable date format: YYYY-MM-DD.'))
                ->renderRow(); ?>

            <?php echo $form->endDate
                ->help(__('Enter the copyright end date, if known. Acceptable date format: YYYY-MM-DD.'))
                ->renderRow(); ?>

            <div class="form-item">
              <?php echo $form->rightsHolder->renderLabel(); ?>
              <?php echo $form->rightsHolder->render(['class' => 'form-autocomplete']); ?>
              <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(['module' => 'rightsholder', 'action' => 'add']); ?> #authorizedFormOfName"/>
              <input class="list" type="hidden" value="<?php echo url_for(['module' => 'rightsholder', 'action' => 'autocomplete']); ?>"/>
              <?php echo $form->rightsHolder
                  ->help(__('Name of the person(s) or organization(s) which has the authority to grant permissions or set rights restrictions.'))
                  ->renderHelp(); ?>
            </div>

            <?php echo $form->rightsNote
                ->help(__('Notes for this Rights Basis.'))
                ->label(__('Rights note(s)'))->renderRow(); ?>

            <h3><?php echo __('Documentation Identifier'); ?></h3>
            <div class="well">
              <?php echo $form->identifierType
                  ->help(__('Can be text value or URI (e.g. to Creative Commons, GNU or other online licenses). Used to identify the granting agreement uniquely within the repository system.'))
                  ->renderRow(); ?>

              <?php echo $form->identifierValue
                  ->help(__('Can be text value or URI (e.g. to Creative Commons, GNU or other online licenses). Used to identify the granting agreement uniquely within the repository system.'))
                  ->renderRow(); ?>

              <?php echo $form->identifierRole
                  ->help(__('Can be text value or URI (e.g. to Creative Commons, GNU or other online licenses). Used to identify the granting agreement uniquely within the repository system.'))
                  ->renderRow(); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="act-granted-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#act-granted-collapse" aria-expanded="false" aria-controls="act-granted-collapse">
            <?php echo __('Act / Granted rights'); ?>
          </button>
        </h2>
        <div id="act-granted-collapse" class="accordion-collapse collapse" aria-labelledby="act-granted-heading">
          <div class="accordion-body">
            <?php foreach ($form['grantedRights'] as $i => $gr) { ?>
              <?php $collapsed = ($i + 1 < sizeof($form['grantedRights']) ? ' collapsed' : ''); ?>
              <fieldset class="collapsible<?php echo $collapsed; ?>">
                <?php
                  // build a title
                  if ($gr['act']->getValue() && null !== $gr['restriction']->getValue()) {
                      $act = $this->context->routing->parse(Qubit::pathInfo($gr['act']->getValue()));
                      $act = $act['_sf_route']->resource;
                      $restriction = QubitGrantedRight::getRestrictionString($gr['restriction']->getValue());

                      $title = "{$act} {$restriction}";
                  } else {
                      $title = __('Granted right ').($i + 1);
                  }
                ?>
                <legend><?php echo $title; ?></legend>
                <?php echo $gr['id']->render(); ?>
                <?php echo $gr['delete']->render(); ?>
                <?php echo $gr['act']
                    ->renderRow(null, null, __('The action which is permitted or restricted.')); ?>
                <?php echo $gr['restriction']
                    ->renderRow(null, null, __('A condition or limitation on the act.')); ?>
                <?php echo $gr['startDate']
                    ->renderRow(null, null, __('The beginning date of the permission granted.')); ?>
                <?php echo $gr['endDate']
                    ->renderRow(null, null, __('The ending date of the permission granted. Omit end date if the ending date is unknown.')); ?>
                <?php echo $gr['notes']
                    ->renderRow(null, null, __('Notes for this granted right.')); ?>
                <a class="c-btn c-btn-delete c-btn-right-align"><?php echo __('Delete'); ?></a><div style="clear:both;"></div>
              </fieldset>
            <?php } ?>

            <fieldset class="collapsible" id="blank">
              <legend><?php echo __('Blank item'); ?></legend>
              <?php echo $form['blank']['id']->render(); ?>
              <?php echo $form['blank']['delete']->render(); ?>
              <?php echo $form['blank']['act']
                  ->renderRow(null, null, __('The action which is permitted or restricted.')); ?>
              <?php echo $form['blank']['restriction']
                  ->renderRow(null, null, __('A condition or limitation on the act.')); ?>
              <?php echo $form['blank']['startDate']
                  ->renderRow(null, null, __('The beginning date of the permission granted.')); ?>
              <?php echo $form['blank']['endDate']
                  ->renderRow(null, null, __('The ending date of the permission granted. Omit end date if the ending date is unknown.')); ?>
              <?php echo $form['blank']['notes']
                  ->renderRow(null, null, __('Notes for this granted right.')); ?>
              <a class="c-btn c-btn-delete c-btn-right-align"><?php echo __('Delete'); ?></a><div style="clear:both;"></div>
            </fieldset>

            <a class="c-btn c-btn-submit newItem"><?php echo __('Add granted right'); ?></a>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
    </ul>

  </form>

  <script type="text/javascript">
    (function(){

      // Basis drop-down.

      let BasisSelect = {
        fieldsets: {
          copyright: jQuery('#wrapper form div[class *= form-row-copyright]'),
          license: jQuery('#wrapper div[class *= form-row-license]'),
          statute: jQuery('#wrapper div[class *= form-row-statute]'),
        },
        onChange: function()
        {
          var selectValue = this.value.match('[^/]*$')[0];
          jQuery.each(BasisSelect.fieldsets, function(value, fields) {
            fields.toggle(selectValue == value);
          });
        }
      }

      jQuery('#right_basis').on('change', BasisSelect.onChange).trigger('change');

      // ...

      $blank = jQuery('#blank');
      $blank.hide();

      var updateGrantedRightsNamesAndIds = function() {
        jQuery('fieldset.grantedRights fieldset').each(function(fsetindex) {
          if(jQuery(this).attr('id') == 'blank') { return true; }
          jQuery(this).find('[name]').each(function(fieldindex) {
            $this = jQuery(this);

            // in case we're working on the blank template
            $this.attr('name', $this.attr('name').replace('[blank]', '[grantedRights]['+fsetindex+']'));
            $this.attr('id', $this.attr('id').replace('_blank_', '_grantedRights_'+fsetindex+'_'));

            $this.attr('name', $this.attr('name').replace(/\[\d+\]/, '[' + fsetindex + ']'));
            $this.attr('id', $this.attr('id').replace(/_\d+_/, '_' + fsetindex + '_'));
          });
        });
      }

      jQuery('#wrapper').on('click', 'a.newItem', function(){
        var added = $blank.clone().insertBefore($blank);

        // fix the added fieldset: name attributes, etc
        added.removeAttr('id');
        added.find('legend').replaceWith('<legend><?php echo __('New granted right'); ?></legend>');

        // yank out the fieldset-wrapper dic that collapse adds
        // because it is about to add another. =(
        var fswrapper = added.find('.fieldset-wrapper');
        html = fswrapper.html()
        fswrapper.replaceWith(html);

        updateGrantedRightsNamesAndIds();
        added.show(400);
      });

      // Granted Rights Delete X
      jQuery('#wrapper').on('click', '.c-btn-delete', function(){
        var fieldset = jQuery(this).parents('fieldset').first()

        // check if this right has been saved / has an id
        var id = fieldset.find('[name*=id]').attr('value');

        if(id === '0') // unsaved granted right
        {
          fieldset.hide(400, function(){
            this.remove();
            updateGrantedRightsNamesAndIds();
          });
        }
        else
        {
          // saved granted right
          fieldset.find('[name*=delete]').attr('value', 'true');
          fieldset.hide(400);
        }
      });

    })();
  </script>

<?php end_slot(); ?>
