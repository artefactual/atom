<?php decorate_with('layout_1col.php') ?>
<?php use_helper('Date') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo __('Edit %1% - ISDIAH', array('%1%' => sfConfig::get('app_ui_label_repository'))) ?>
    <span class="sub"><?php echo render_title($resource->getLabel()) ?></span>
  </h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderGlobalErrors() ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
    <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'repository', 'action' => 'edit'))) ?>
  <?php else: ?>
    <?php echo $form->renderFormTag(url_for(array('module' => 'repository', 'action' => 'add'))) ?>
  <?php endif; ?>

    <?php echo $form->renderHiddenFields() ?>

    <section id="content">

      <fieldset class="collapsible collapsed" id="identityArea">

        <legend><?php echo __('Identity area') ?></legend>

        <?php echo $form->identifier
          ->help(__('"Record the numeric or alpha-numeric code identifying the institution in accordance with the relevant international and national standards." (ISDIAH 5.1.1)'))
          ->label(__('Identifier').' <span class="form-required" title="'.__('This is a mandatory element.').'">*</span>')
          ->renderRow() ?>

        <?php echo render_field($form->authorizedFormOfName
          ->help(__('"Record the standardised form of name of the institution, adding appropriate qualifiers (for instance dates, place, etc.), if necessary. Specify separately in the Rules and/or conventions used element (5.6.3) which set of rules has been applied for this element." (ISDIAH 5.1.2)'))
          ->label(__('Authorized form of name').' <span class="form-required" title="'.__('This is a mandatory element').'">*</span>'), $resource) ?>

        <?php echo $form->parallelName
          ->help(__('"Purpose: To indicate the various forms in which the authorised form of name of an institution occurs in other languages or script form(s). Rule: Record the parallel form(s) of name of the institution in accordance with any relevant national or international conventions or rules applied by the agency that created the description, including any necessary sub elements and/or qualifiers required by those conventions or rules. Specify in the Rules and/or conventions used element (5.6.3) which rules have been applied." (ISDIAH 5.1.3)'))
          ->label('Parallel form(s) of name')
          ->renderRow() ?>

        <?php echo $form->otherName
          ->help(__('"Record any other name(s) by which the institution may be known. This could include other forms of the same name, acronyms, other institutional names, or changes of name over time, including, if possible, relevant dates." (ISDIAH 5.1.4)'))
          ->label('Other form(s) of name')
          ->renderRow() ?>

        <?php echo $form->type
          ->renderLabel() ?>
        <?php echo $form->type
          ->help(__('Record the type of the institution. (ISDIAH 5.1.5) Select as many types as desired from the drop-down menu; these values are drawn from the Repository Types taxonomy.'))
          ->render(array('class' => 'form-autocomplete')) ?>

        <?php $repoTypeTaxonomyId = QubitTaxonomy::REPOSITORY_TYPE_ID ?>
        <?php if (QubitAcl::check(QubitTaxonomy::getById($repoTypeTaxonomyId), 'createTerm')): ?>
          <input class="add" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array(QubitTaxonomy::getById($repoTypeTaxonomyId), 'module' => 'taxonomy')))) ?> #name"/>
        <?php endif; ?>

      </fieldset>

      <fieldset class="collapsible collapsed" id="contactArea">

        <legend><?php echo __('Contact area') ?></legend>

        <?php echo get_partial('contactinformation/edit', $sf_data->getRaw('contactInformationEditComponent')->getVarHolder()->getAll()) ?>

      </fieldset>

      <fieldset class="collapsible collapsed" id="descriptionArea">

        <legend><?php echo __('Description area') ?></legend>

        <?php echo render_field($form->history
          ->help(__('"Record any relevant information about the history of the institution. This element may include information on dates of establishment, changes of names, changes of legislative mandates, or of any other sources of authority for the institution." (ISDIAH 5.3.1)')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->geoculturalContext
          ->help(__('"Identify the geographical area the institution belongs to. Record any other relevant information about the cultural context of the institution." (ISDIAH 5.3.2)'))
          ->label(__('Geographical and cultural context')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->mandates
          ->help(__('"Record any document, law, directive or charter which acts as a source of authority for the powers, functions and responsibilities of the institution, together with information on the jurisdiction(s) and covering dates when the mandate(s) applied or were changed." (ISDIAH 5.3.3)'))
          ->label(__('Mandates/Sources of authority')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->internalStructures
          ->help(__('"Describe, in narrative form or using organisational charts, the current administrative structure of the institution." (ISDIAH 5.3.4)'))
          ->label(__('Administrative structure')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->collectingPolicies
          ->help(__('"Record information about the records management and collecting policies of the institution. Define the scope and nature of material which the institution accessions. Indicate whether the repository seeks to acquire archival materials by transfer, gift, purchase and/or loan. If the policy includes active survey and/or rescue work, this might be spelt out." (ISDIAH 5.3.5)'))
          ->label(__('Records management and collecting policies')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->buildings
          ->help(__('"Record information on the building(s) of the institution (general and architectural characteristics of the building, capacity of storage areas, etc). Where possible, provide information which can be used for generating statistics." (ISDIAH 5.3.6)')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->holdings
          ->help(__('"Record a short description of the holdings of the institution, describing how and when they were formed. Provide information on volume of holdings, media formats, thematic coverage, etc." (ISDIAH 5.3.7)'))
          ->label(__('Archival and other holdings')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->findingAids
          ->help(__('"Record the title and other pertinent details of the published and/or unpublished finding aids and guides prepared by the institution and of any other relevant publications. Use ISO 690 Information and documentation – Bibliographic references and other national or international cataloguing rules." (ISDIAH 5.3.8)'))
          ->label(__('Finding aids, guides and publications')), $resource, array('class' => 'resizable')) ?>

      </fieldset>

      <fieldset class="collapsible collapsed" id="accessArea">

        <legend><?php echo __('Access area') ?></legend>

        <?php echo render_field($form->openingTimes
          ->help(__('"Record the opening hours of the institution and annual, seasonal and public holidays, and any other planned closures. Record times associated with the availability and/or delivery of services (for example, exhibition spaces, reference services, etc.)." (ISDIAH 5.4.1)')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->accessConditions
          ->help(__('"Describe access policies, including any restrictions and/or regulations for the use of materials and facilities. Record information about registration, appointments, readers’ tickets, letters of introduction, admission fees, etc. Where appropriate, make reference to the relevant legislation." (ISDIAH 5.4.2)'))
          ->label(__('Conditions and requirements')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->disabledAccess
          ->help(__('"Record information about travelling to the institution and details for users with disabilities, including building features, specialised equipment or tools, parking or lifts." (ISDIAH 5.4.3)'))
          ->label(__('Accessibility')), $resource, array('class' => 'resizable')) ?>

      </fieldset>

      <fieldset class="collapsible collapsed" id="servicesArea">

        <legend><?php echo __('Services area') ?></legend>

        <?php echo render_field($form->researchServices
          ->help(__('"Record information about the onsite services provided by the institution such as languages spoken by staff, research and consultation rooms, enquiry services, internal libraries, map, microfiches, audio-visual, computer rooms, etc. Record as well any relevant information about research services, such as research undertaken by the institution, and the fee charge if applicable." (ISDIAH 5.5.1)')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->reproductionServices
          ->help(__('"Record information about reproduction services available to the public (microfilms, photocopies, photographs, digitised copies). Specify general conditions and restrictions to the services, including applicable fees and publication rules." (ISDIAH 5.5.2)')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->publicFacilities
          ->help(__('"Record information about spaces available for public use (permanent or temporary exhibitions, free or charged internet connection, cash machines, cafeterias, restaurants, shops, etc.)." (ISDIAH 5.5.3)'))
          ->label(__('Public areas')), $resource, array('class' => 'resizable')) ?>

      </fieldset>

      <fieldset class="collapsible collapsed" id="descriptionControlArea">

        <legend><?php echo __('Control area') ?></legend>

        <?php echo render_field($form->descIdentifier
          ->help(__('"Record a unique description identifier in accordance with local and/or national conventions. If the description is to be used internationally, record the code of the country in which the description was created in accordance with the latest version of ISO 3166 - Codes for the representation of names of countries. Where the creator of the description is an international organisation, give the organisational identifier in place of the country code." (ISIAH 5.6.1)'))
          ->label(__('Description identifier')), $resource) ?>

        <?php echo render_field($form->descInstitutionIdentifier
          ->help(__('"Record the full authorised form of name(s) of the agency(ies) responsible for creating, modifying or disseminating the description or, alternatively, record a code for the agency in accordance with the national or international agency code standard." (ISDIAH 5.6.2)'))
          ->label(__('Institution identifier')), $resource) ?>

        <?php echo render_field($form->descRules
          ->help(__('"Record the names, and, where useful, the editions or publication dates of the conventions or rules applied. Specify, separately, which rules have been applied for creating the Authorised form(s) of name. Include reference to any system(s) of dating used to identify dates in this description (e.g. ISO 8601)." (ISDIAH 5.6.3)'))
          ->label(__('Rules and/or conventions used')), $resource, array('class' => 'resizable')) ?>

        <?php echo $form->descStatus
          ->help(__('The purpose of this field is "[t]o indicate the drafting status of the description so that users can understand the current status of the description." (ISDIAH 5.6.4). Select Final, Revised or Draft from the drop-down menu.'))
          ->label('Status')
          ->renderRow() ?>

        <?php echo $form->descDetail
          ->help(__('Select Full, Partial or Minimal from the drop-down menu. "In the absence of national guidelines or rules, minimal descriptions are those that consist only of the three essential elements of an ISIAH compliant description (see 4.7), while full records are those that convey information for all relevant ISDIAH elements of description." (ISDIAH 5.6.5)'))
          ->label('Level of detail')
          ->renderRow() ?>

        <?php echo render_field($form->descRevisionHistory
          ->help(__('"Record the date the description was created and the dates of any revisions to the description." (ISDIAH 5.6.6)'))
          ->label(__('Dates of creation, revision and deletion')), $resource, array('class' => 'resizable')) ?>

        <?php if (isset($resource->updatedAt)): ?>
          <div class="field">
            <h3><?php echo __('Last updated') ?></h3>
            <div>
              <?php echo format_date($resource->updatedAt, 'f') ?>
            </div>
          </div>
        <?php endif; ?>

        <?php echo $form->language
          ->help(__('Select the language(s) of this record from the drop-down menu; enter the first few letters to narrow the choices. (ISDIAH 5.6.7)'))
          ->renderRow(array('class' => 'form-autocomplete')) ?>

        <?php echo $form->script
          ->help(__('Select the script(s) of this record from the drop-down menu; enter the first few letters to narrow the choices. (ISDIAH 5.6.7)'))
          ->renderRow(array('class' => 'form-autocomplete')) ?>

        <?php echo render_field($form->descSources
          ->help(__('"Record the sources consulted in establishing the description of the institution." (ISDIAH 5.6.8)'))
          ->label(__('Sources')), $resource, array('class' => 'resizable')) ?>

        <?php echo render_field($form->maintenanceNotes
          ->help(__('"Record notes pertinent to the creation and maintenance of the description." (ISDIAH 5.6.9)')), $isdiah, array('class' => 'resizable')) ?>

      </fieldset>

      <fieldset class="collapsible collapsed" id="accessPointsArea">

        <legend><?php echo __('Access points') ?></legend>

        <div class="form-item">
          <?php echo $form->thematicArea
            ->label(__('Thematic area'))
            ->renderLabel() ?>
          <?php echo $form->thematicArea->render(array('class' => 'form-autocomplete')) ?>
          <?php if (QubitAcl::check(QubitTaxonomy::getById(QubitTaxonomy::THEMATIC_AREA_ID), 'createTerm')): ?>
            <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::THEMATIC_AREA_ID), 'module' => 'taxonomy')))) ?> #name"/>
          <?php endif; ?>
          <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::THEMATIC_AREA_ID), 'module' => 'taxonomy')))) ?>"/>
          <?php echo $form->thematicArea
            ->help(__("Search for an existing term in the Thematic Areas taxonomy by typing the first few characters of the term name. This should be used to identify major collecting areas."))
            ->renderHelp() ?>
        </div>

        <div class="form-item">
          <?php echo $form->geographicSubregion
            ->label(__('Geographic subregion'))
            ->renderLabel() ?>
          <?php echo $form->geographicSubregion->render(array('class' => 'form-autocomplete')) ?>
          <?php if (QubitAcl::check(QubitTaxonomy::getById(QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID), 'createTerm')): ?>
            <input class="add" type="hidden" data-link-existing="true" value="<?php echo url_for(array('module' => 'term', 'action' => 'add', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID), 'module' => 'taxonomy')))) ?> #name"/>
          <?php endif; ?>
          <input class="list" type="hidden" value="<?php echo url_for(array('module' => 'term', 'action' => 'autocomplete', 'taxonomy' => url_for(array(QubitTaxonomy::getById(QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID), 'module' => 'taxonomy')))) ?>"/>
          <?php echo $form->geographicSubregion
            ->help(__("Search for an existing term in the Geographic Subregion taxonomy by typing the first few characters of the term name."))
            ->renderHelp() ?>
        </div>

      </fieldset>

    </section>

    <section class="actions">
      <ul>
        <?php if (isset($sf_request->getAttribute('sf_route')->resource)): ?>
          <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'repository'), array('class' => 'c-btn', 'title' => __('Cancel'))) ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
        <?php else: ?>
          <li><?php echo link_to(__('Cancel'), array('module' => 'repository', 'action' => 'browse'), array('class' => 'c-btn', 'title' => __('Cancel'))) ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Create') ?>"/></li>
        <?php endif; ?>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
