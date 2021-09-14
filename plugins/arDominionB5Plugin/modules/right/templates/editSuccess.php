<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo render_title($resource); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php echo __('Rights management'); ?>
    </span>
  </div>
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
            <?php echo render_field($form->basis
                ->help(__('Basis for the permissions granted or for the restriction of rights'))
            ); ?>

            <?php echo render_field($form->copyrightStatus
                ->help(__('A coded designation for the copyright status of the object at the time the rights statement is recorded.'))
            ); ?>

            <?php echo render_field($form->copyrightStatusDate
                ->help(__('The date the copyright status applies.'))
            ); ?>

            <?php echo render_field($form->copyrightJurisdiction
                ->help(__('The country whose copyright laws apply.'))
            ); ?>

            <?php echo render_field($form->copyrightNote
                ->help(__('Notes regarding the copyright.'))
            ); ?>

            <?php echo render_field($form->licenseTerms
                ->help(__('Text describing the license or agreement by which permission was granted or link to full-text hosted online. This can contain the actual text of the license or agreement or a paraphrase or summary.'))
            ); ?>

            <?php echo render_field($form->licenseNote
                ->help(__('Additional information about the license, such as contact persons, action dates, or interpretations. The note may also indicated the location of the license, if it is available online or embedded in the object itself.'))
            ); ?>

            <?php echo render_field($form->statuteJurisdiction
                ->help(__('The country or other political body that has enacted the statute.'))
            ); ?>

            <?php
                $taxonomy = QubitTaxonomy::getById(QubitTaxonomy::RIGHTS_STATUTES_ID);
                $taxonomyUrl = url_for([$taxonomy, 'module' => 'taxonomy']);
                $extraInputs = '<input class="list" type="hidden" value="'
                    .url_for(['module' => 'term', 'action' => 'autocomplete', 'taxonomy' => $taxonomyUrl])
                    .'">';
                if (QubitAcl::check($taxonomy, 'createTerm')) {
                    $extraInputs .= '<input class="add" type="hidden" data-link-existing="true" value="'
                        .url_for(['module' => 'term', 'action' => 'add', 'taxonomy' => $taxonomyUrl])
                        .' #name">';
                }
                echo render_field(
                    $form->statuteCitation->help(__(
                        'An identifying designation for the statute. Use standard'
                        .' citation form when applicable, e.g. bibliographic citation.'
                    )),
                    null,
                    ['class' => 'form-autocomplete', 'extraInputs' => $extraInputs]
                );
            ?>

            <?php echo render_field($form->statuteDeterminationDate
                ->help(__('Date that the decision to ascribe the right to this statute was made. As context for any future review/re-interpretation.'))
            ); ?>

            <?php echo render_field($form->statuteNote
                ->help(__('Additional information about the statute.'))
            ); ?>

            <?php echo render_field($form->startDate
                ->help(__('Enter the copyright start date, if known. Acceptable date format: YYYY-MM-DD.'))
            ); ?>

            <?php echo render_field($form->endDate
                ->help(__('Enter the copyright end date, if known. Acceptable date format: YYYY-MM-DD.'))
            ); ?>

            <?php echo render_field(
                $form->rightsHolder->help(__(
                    'Name of the person(s) or organization(s) which has the authority to'
                    .' grant permissions or set rights restrictions.'
                )),
                null,
                [
                    'class' => 'form-autocomplete',
                    'extraInputs' => '<input class="list" type="hidden" value="'
                        .url_for(['module' => 'rightsholder', 'action' => 'autocomplete'])
                        .'"><input class="add" type="hidden" data-link-existing="true" value="'
                        .url_for(['module' => 'rightsholder', 'action' => 'add'])
                        .' #authorizedFormOfName">',
                ]
            ); ?>

            <?php echo render_field($form->rightsNote
                ->help(__('Notes for this Rights Basis.'))
                ->label(__('Rights note(s)'))
            ); ?>

            <h3 class="fs-6 mb-2">
              <?php echo __('Documentation Identifier'); ?>
            </h3>

            <div class="border rounded p-3 mb-2">
              <?php echo render_field($form->identifierType
                  ->help(__('Can be text value or URI (e.g. to Creative Commons, GNU or other online licenses). Used to identify the granting agreement uniquely within the repository system.'))
              ); ?>

              <?php echo render_field($form->identifierValue
                  ->help(__('Can be text value or URI (e.g. to Creative Commons, GNU or other online licenses). Used to identify the granting agreement uniquely within the repository system.'))
              ); ?>

              <?php echo render_field($form->identifierRole
                  ->help(__('Can be text value or URI (e.g. to Creative Commons, GNU or other online licenses). Used to identify the granting agreement uniquely within the repository system.'))
              ); ?>
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
              <fieldset class="mb-2">
                <?php
                  // Build a title
                  if ($gr['act']->getValue() && null !== $gr['restriction']->getValue()) {
                      $act = $this->context->routing->parse(Qubit::pathInfo($gr['act']->getValue()));
                      $act = $act['_sf_route']->resource;
                      $restriction = QubitGrantedRight::getRestrictionString($gr['restriction']->getValue());

                      $title = "{$act} {$restriction}";
                  } else {
                      $title = __('Granted right ').($i + 1);
                  }
                ?>

                <legend>
                  <?php echo $title; ?>
                </legend>

                <?php echo $gr['id']->render(); ?>
                <?php echo $gr['delete']->render(); ?>

                <?php echo render_field($gr['act']
                    ->help(__('The action which is permitted or restricted.'))
                ); ?>
                <?php echo render_field($gr['restriction']
                    ->help(__('A condition or limitation on the act.'))
                ); ?>
                <?php echo render_field($gr['startDate']
                    ->help(__('The beginning date of the permission granted.'))
                ); ?>
                <?php echo render_field($gr['endDate']
                    ->help(__('The ending date of the permission granted. Omit end date if the ending date is unknown.'))
                ); ?>
                <?php echo render_field($gr['notes']
                    ->help(__('Notes for this granted right.'))
                ); ?>

                <button type="button" class="btn atom-btn-white">
                  <i class="fas fa-times me-1" aria-hidden="true"></i>
                  <?php echo __('Delete'); ?>
                </button>
              </fieldset>
            <?php } ?>

            <fieldset class="mb-2 d-none" id="blank">
              <legend>
                <?php echo __('Blank item'); ?>
              </legend>

              <?php echo $form['blank']['id']->render(); ?>
              <?php echo $form['blank']['delete']->render(); ?>

              <?php echo render_field($form['blank']['act']
                  ->help(__('The action which is permitted or restricted.'))
              ); ?>
              <?php echo render_field($form['blank']['restriction']
                  ->help(__('A condition or limitation on the act.'))
              ); ?>
              <?php echo render_field($form['blank']['startDate']
                  ->help(__('The beginning date of the permission granted.'))
              ); ?>
              <?php echo render_field($form['blank']['endDate']
                  ->help(__('The ending date of the permission granted. Omit end date if the ending date is unknown.'))
              ); ?>
              <?php echo render_field($form['blank']['notes']
                  ->help(__('Notes for this granted right.'))
              ); ?>

              <button type="button" class="btn atom-btn-white">
                <i class="fas fa-times me-1" aria-hidden="true"></i>
                <?php echo __('Delete'); ?>
              </button>
            </fieldset>

            <button type="button" class="btn atom-btn-white newItem">
              <i class="fas fa-plus me-1" aria-hidden="true"></i>
              <?php echo __('Add granted right'); ?>
            </button>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
