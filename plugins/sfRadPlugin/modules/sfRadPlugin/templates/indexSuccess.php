<div class="row">

  <div id="left-column">

    <?php echo get_component('informationobject', 'contextMenu') ?>

  </div>

  <!--

  <?php echo get_partial('informationobject/printPreviewBar', array('resource' => $resource)) ?>

  <h1 class="label printable">
    <?php echo link_to_if(QubitAcl::check($resource, 'update'), render_title($rad), array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('title' => __('Edit archival description'))) ?>
    <?php echo get_partial('informationobject/actionIcons') ?>
  </h1>

  <?php if (QubitInformationObject::ROOT_ID != $resource->parentId): ?>
  <h1 class="part-of"><?php echo __('Part of %1%', array('%1%' => $resource->getCollectionRoot()->__toString())) ?></h1>
  <?php endif; ?>


  -->

  <div id="main-column" class="span9 offset3">

    <h1><?php echo render_title($rad) ?> <strong><?php echo $resource->levelOfDescription ?></strong></h1>

    <!--
    <?php if (isset($errorSchema)): ?>
      <div class="messages error">
        <ul>
          <?php foreach ($errorSchema as $error): ?>
            <li><?php echo $error ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    -->

    <?php if (QubitInformationObject::ROOT_ID != $resource->parentId): ?>
      <?php echo include_partial('default/breadcrumb', array('resource' => $resource, 'objects' => $resource->getAncestors()->andSelf()->orderBy('lft'))) ?>
    <?php endif; ?>

    <div class="row">

      <div class="span7" id="content">

        <?php if (0 < count($resource->digitalObjects)): ?>
          <?php echo get_component('digitalobject', 'show', array('link' => $digitalObjectLink, 'resource' => $resource->digitalObjects[0], 'usageType' => QubitTerm::REFERENCE_ID)) ?>
        <?php endif; ?>

        <div class="section" id="titleAndStatementOfResponsibilityArea">

          <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Title and statement of responsibility area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'titleAndStatementOfResponsibilityArea', 'title' => __('Edit title and statement of responsibility area'))) ?>

          <?php echo render_show(__('Title proper'), render_value($resource->getTitle(array('cultureFallback' => true)))) ?>

          <div class="field">
            <h3><?php echo __('General material designation') ?></h3>
            <div>
              <ul>
                <?php foreach ($resource->getMaterialTypes() as $materialType): ?>
                  <li><?php echo $materialType->term ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>

          <?php echo render_show(__('Parallel title'), render_value($resource->getAlternateTitle(array('cultureFallback' => true)))) ?>

          <?php echo render_show(__('Other title information'), render_value($rad->otherTitleInformation)) ?>

          <?php echo render_show(__('Title statements of responsibility'), render_value($rad->titleStatementOfResponsibility)) ?>

          <div class="field">
            <h3><?php echo __('Title notes') ?></h3>
            <div>
              <ul>
                <?php foreach ($resource->getNotesByTaxonomy(array('taxonomyId' => QubitTaxonomy::RAD_TITLE_NOTE_ID)) as $item): ?>
                  <li><?php echo $item->type ?>: <?php echo $item->getContent(array('cultureFallback' => true)) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>

          <?php echo render_show(__('Level of description'), render_value($resource->levelOfDescription)) ?>

          <?php echo render_show_repository(__('Repository'), $resource) ?>

          <?php echo render_show(__('Reference code'), render_value($rad->referenceCode)) ?>

        </div> <!-- /.section#titleAndStatementOfResponsibilityArea -->

        <div class="section" id="editionArea">

          <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Edition area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'editionArea', 'title' => __('Edit edition area'))) ?>

          <?php echo render_show(__('Edition statement'), render_value($resource->getEdition(array('cultureFallback' => true)))) ?>

          <?php echo render_show(__('Edition statement of responsibility'), render_value($rad->editionStatementOfResponsibility)) ?>

        </div> <!-- /.section#editionArea -->

        <div class="section" id="classOfMaterialSpecificDetailsArea">

          <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Class of material specific details area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'classOfMaterialSpecificDetailsArea', 'title' => __('Edit class of material specific details area'))) ?>

          <?php echo render_show(__('Statement of scale (cartographic)'), render_value($rad->statementOfScaleCartographic)) ?>

          <?php echo render_show(__('Statement of projection (cartographic)'), render_value($rad->statementOfProjection)) ?>

          <?php echo render_show(__('Statement of coordinates (cartographic)'), render_value($rad->statementOfCoordinates)) ?>

          <?php echo render_show(__('Statement of scale (architectural)'), render_value($rad->statementOfScaleArchitectural)) ?>

          <?php echo render_show(__('Issuing jurisdiction and denomination (philatelic)'), render_value($rad->issuingJurisdictionAndDenomination)) ?>

        </div> <!-- /.section#classOfMaterialSpecificDetailsArea -->

        <div class="section" id="datesOfCreationArea">

          <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Dates of creation area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'datesOfCreationArea', 'title' => __('Edit dates of creation area'))) ?>

          <?php echo get_partial('informationobject/dates', array('resource' => $resource)) ?>

        </div> <!-- /.section#datesOfCreationArea -->

        <div class="section" id="physicalDescriptionArea">

          <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Physical description area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'physicalDescriptionArea', 'title' => __('Edit physical description area'))) ?>

          <?php echo render_show(__('Physical description'), render_value($resource->getExtentAndMedium(array('cultureFallback' => true)))) ?>

        </div> <!-- /.section#physicalDescriptionArea -->

        <div class="section" id="publishersSeriesArea">

          <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Publisher\'s series area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'publishersSeriesArea', 'title' => __('Edit publisher\'s series area'))) ?>

          <?php echo render_show(__('Title proper of publisher\'s series'), render_value($rad->titleProperOfPublishersSeries)) ?>

          <?php echo render_show(__('Parallel titles of publisher\'s series'), render_value($rad->parallelTitlesOfPublishersSeries)) ?>

          <?php echo render_show(__('Other title information of publisher\'s series'), render_value($rad->otherTitleInformationOfPublishersSeries)) ?>

          <?php echo render_show(__('Statement of responsibility relating to publisher\'s series'), render_value($rad->statementOfResponsibilityRelatingToPublishersSeries)) ?>

          <?php echo render_show(__('Numbering within publisher\'s series'), render_value($rad->numberingWithinPublishersSeries)) ?>

          <?php echo render_show(__('Note on publisher\'s series'), render_value($rad->noteOnPublishersSeries)) ?>

        </div> <!-- /.section#publishersSeriesArea -->

        <div class="section" id="archivalDescriptionArea">

          <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Archival description area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'archivalDescriptionArea', 'title' => __('Edit archival description area'))) ?>

          <?php echo get_component('informationobject', 'creatorDetail', array('resource' => $resource)) ?>

          <?php echo render_show(__('Custodial history'), render_value($resource->getArchivalHistory(array('cultureFallback' => true)))) ?>

          <?php echo render_show(__('Scope and content'), render_value($resource->getScopeAndContent(array('cultureFallback' => true)))) ?>

        </div> <!-- /.section#archivalDescriptionArea -->

        <div class="section" id="notesArea">

          <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Notes area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'notesArea', 'title' => __('Edit notes area'))) ?>

          <?php if (check_field_visibility('app_element_visibility_rad_physical_condition')): ?>
            <?php echo render_show(__('Physical condition'), render_value($resource->getPhysicalCharacteristics(array('cultureFallback' => true)))) ?>
          <?php endif; ?>

          <?php if (check_field_visibility('app_element_visibility_rad_immediate_source')): ?>
            <?php echo render_show(__('Immediate source of acquisition'), render_value($resource->getAcquisition(array('cultureFallback' => true)))) ?>
          <?php endif; ?>

          <?php echo render_show(__('Arrangement'), render_value($resource->getArrangement(array('cultureFallback' => true)))) ?>

          <div class="field">
            <h3><?php echo __('Language of material') ?></h3>
            <div>
              <ul>
                <?php foreach ($resource->language as $code): ?>
                  <li><?php echo format_language($code) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>

          <div class="field">
            <h3><?php echo __('Script of material') ?></h3>
            <div>
              <ul>
                <?php foreach ($resource->script as $code): ?>
                  <li><?php echo format_script($code) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>

          <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::LANGUAGE_NOTE_ID)) as $item): ?>
            <?php echo render_show(__('Language and script note'), render_value($item->getContent(array('cultureFallback' => true)))) ?>
          <?php endforeach; ?>

          <?php echo render_show(__('Location of originals'), render_value($resource->getLocationOfOriginals(array('cultureFallback' => true)))) ?>

          <?php echo render_show(__('Availability of other formats'), render_value($resource->getLocationOfCopies(array('cultureFallback' => true)))) ?>

          <?php echo render_show(__('Restrictions on access'), render_value($resource->getAccessConditions(array('cultureFallback' => true)))) ?>

          <?php echo render_show(__('Terms governing use, reproduction, and publication'), render_value($resource->getReproductionConditions(array('cultureFallback' => true)))) ?>

          <?php echo render_show(__('Finding aids'), render_value($resource->getFindingAids(array('cultureFallback' => true)))) ?>

          <?php echo render_show(__('Associated / related material'), render_value($resource->getRelatedUnitsOfDescription(array('cultureFallback' => true)))) ?>

          <?php echo render_show(__('Accruals'), render_value($resource->getAccruals(array('cultureFallback' => true)))) ?>

          <div class="field">
            <h3><?php echo __('Other notes') ?></h3>
            <div>
              <ul>
                <?php foreach ($resource->getNotesByTaxonomy(array('taxonomyId' => QubitTaxonomy::RAD_NOTE_ID)) as $item): ?>

                  <?php $type = $item->getType(array('sourceCulture' => true)) ?>

                  <?php if ('General note' == $type && !check_field_visibility('app_element_visibility_rad_general_notes')): ?>
                    <?php continue; ?>
                  <?php endif; ?>

                  <?php if ('Conservation' == $type && !check_field_visibility('app_element_visibility_rad_conservation_notes')): ?>
                    <?php continue; ?>
                  <?php endif; ?>

                  <li><?php echo $item->type ?>: <?php echo render_value($item->getContent(array('cultureFallback' => true))) ?></li>

                <?php endforeach; ?>
              </ul>
            </div>
          </div>

        </div> <!-- /.section#notesArea -->

        <div class="section" id="standardNumberArea">

          <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Standard number area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'standardNumberArea', 'title' => __('Edit standard number area'))) ?>

          <?php echo render_show(__('Standard number'), render_value($rad->standardNumber)) ?>

        </div> <!-- /.section#standardNumberArea -->

        <div class="section" id="accessPointsArea">

          <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Access points').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'accessPointsArea', 'title' => __('Edit access points'))) ?>

          <?php echo get_partial('informationobject/subjectAccessPoints', array('resource' => $resource)) ?>

          <?php echo get_partial('informationobject/placeAccessPoints', array('resource' => $resource)) ?>

          <?php echo get_partial('informationobject/nameAccessPoints', array('resource' => $resource)) ?>

        </div> <!-- /.section -->

        <div class="section" id="descriptionControlArea">

          <?php echo link_to_if(SecurityPriviliges::editCredentials($sf_user, 'informationObject'), '<h2>'.__('Control area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'descriptionControlArea', 'title' => __('Edit control area'))) ?>

          <?php if (check_field_visibility('app_element_visibility_rad_control_description_identifier')): ?>
            <?php echo render_show(__('Description record identifier'), render_value($resource->descriptionIdentifier)) ?>
          <?php endif; ?>

          <?php if (check_field_visibility('app_element_visibility_rad_control_institution_identifier')): ?>
            <?php echo render_show(__('Institution identifier'), render_value($resource->getInstitutionResponsibleIdentifier(array('cultureFallback' => true)))) ?>
          <?php endif; ?>

          <?php if (check_field_visibility('app_element_visibility_rad_control_rules_conventions')): ?>
            <?php echo render_show(__('Rules or conventions'), render_value($resource->getRules(array('cultureFallback' => true)))) ?>
          <?php endif; ?>

          <?php if (check_field_visibility('app_element_visibility_rad_control_status')): ?>
            <?php echo render_show(__('Status'), render_value($resource->descriptionStatus)) ?>
          <?php endif; ?>

          <?php if (check_field_visibility('app_element_visibility_rad_control_level_of_detail')): ?>
            <?php echo render_show(__('Level of detail'), render_value($resource->descriptionDetail)) ?>
          <?php endif; ?>

          <?php if (check_field_visibility('app_element_visibility_rad_control_dates')): ?>
            <?php echo render_show(__('Dates of creation, revision and deletion'), render_value($resource->getRevisionHistory(array('cultureFallback' => true)))) ?>
          <?php endif; ?>

          <?php if (check_field_visibility('app_element_visibility_rad_control_language')): ?>
            <div class="field">
              <h3><?php echo __('Language of description') ?></h3>
              <div>
                <ul>
                  <?php foreach ($resource->languageOfDescription as $code): ?>
                    <li><?php echo format_language($code) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          <?php endif; ?>

          <?php if (check_field_visibility('app_element_visibility_rad_control_script')): ?>
            <div class="field">
              <h3><?php echo __('Script of description') ?></h3>
              <div>
                <ul>
                  <?php foreach ($resource->scriptOfDescription as $code): ?>
                    <li><?php echo format_script($code) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          <?php endif; ?>

          <?php if (check_field_visibility('app_element_visibility_rad_control_sources')): ?>
            <?php echo render_show(__('Sources'), render_value($resource->getSources(array('cultureFallback' => true)))) ?>
          <?php endif; ?>

        </div> <!-- /.section -->

        <?php if ($sf_user->isAuthenticated()): ?>

          <div class="section" id="rightsArea">

            <?php echo link_to_if(QubitAcl::check($resource, 'update'), '<h2>'.__('Rights area').'</h2>', array($resource, 'module' => 'informationobject', 'action' => 'edit'), array('anchor' => 'rightsArea', 'title' => __('Edit rights area'))) ?>

            <?php echo get_component('right', 'relatedRights', array('resource' => $resource)) ?>

          </div> <!-- /.section#rightsArea -->

        <?php endif; ?>

        <?php if (0 < count($resource->digitalObjects)): ?>

          <?php echo get_partial('digitalobject/metadata', array('resource' => $resource->digitalObjects[0])) ?>

          <?php echo get_partial('digitalobject/rights', array('resource' => $resource->digitalObjects[0])) ?>

        <?php endif; ?>

        <div class="section" id="accessionArea">

          <h2><?php echo __('Accession area') ?></h2>

          <?php echo get_component('informationobject', 'accessions', array('resource' => $resource)) ?>

        </div> <!-- /.section#accessionArea -->

        <?php echo get_partial('informationobject/actions', array('resource' => $resource)) ?>

      </div>

      <div class="span2" id="right-column">

        <section>
          <h3>How to access this content?</h3>
          <div><a href="#">Contact the archivist at Vancouver Maritime Museum Library</a></div>
          <div><a href="#" class="widebtn">Send this page</a></div>
        </section>

        <section>
          <h3>Related Subjects</h3>
          <ul class="unstyled">
            <li><a href="#">Agriculture</a></li>
            <li><a href="#">Arts &amp; Culture</a></li>
            <li><a href="#">Business</a></li>
          </ul>
        </section>

        <section>
          <h3>Related People &amp; Organizations</h3>
          <ul class="unstyled">
            <li><a href="#">Commodore J.T. Kirk</a></li>
            <li><a href="#">Mike Michaels</a></li>
          </ul>
        </section>

        <section>
          <h3>Related Places</h3>
          <ul class="unstyled">
            <li><a href="#">Comox, BC</a></li>
            <li><a href="#">Windsor, ON</a></li>
          </ul>
        </section>

        <section>
          <?php if (false): ?>
          <div>
            <h4>
              <?php if (isset($resource->getCollectionRoot()->levelOfDescription)): ?>
                <?php echo $resource->getCollectionRoot()->levelOfDescription ?>
              <?php else: ?>
                <?php echo sfConfig::get('app_ui_label_collection') ?>
              <?php endif; ?>
            </h4>
          </div>
          <?php endif; ?>
        </section>

        <section>
          <h3><?php echo __('Import') ?></h3>
          <div class="content">
            <ul>
              <li><?php echo link_to(__('XML'), array($resource, 'module' => 'object', 'action' => 'importSelect', 'type' => 'xml')) ?></li>
              <li><?php echo link_to(__('CSV'), array($resource, 'module' => 'object', 'action' => 'importSelect', 'type' => 'csv')) ?></li>
            </ul>
          </div>
        </section>

        <?php echo get_partial('informationobject/format', array('resource' => $resource)) ?>

        <?php if (check_field_visibility('app_element_visibility_physical_storage')): ?>
          <?php echo get_component('physicalobject', 'contextMenu', array('resource' => $resource)) ?>
        <?php endif; ?>

      </div>

    </div>

  </div>

</div>
