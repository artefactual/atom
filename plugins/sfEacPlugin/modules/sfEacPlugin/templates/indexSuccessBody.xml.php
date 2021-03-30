<?php echo '<?xml version="1.0" encoding="'.sfConfig::get('sf_charset', 'UTF-8')."\" ?>\n"; ?>
<eac-cpf xmlns="urn:isbn:1-931666-33-4" xmlns:xlink="http://www.w3.org/1999/xlink">

  <control>
    <?php if (!empty($resource->descriptionIdentifier)) { ?>
      <recordId><?php echo esc_specialchars($resource->descriptionIdentifier); ?></recordId>
    <?php } ?>

    <?php if (!empty($eac->maintenanceStatus)) { ?>
      <maintenanceStatus><?php echo $eac->maintenanceStatus; ?></maintenanceStatus>
    <?php } ?>

    <?php if (!empty($eac->publicationStatus)) { ?>
      <publicationStatus><?php echo $eac->publicationStatus; ?></publicationStatus>
    <?php } ?>

    <?php if (!empty($resource->institutionResponsibleIdentifier)) { ?>
      <maintenanceAgency>
        <agencyName><?php echo esc_specialchars($resource->institutionResponsibleIdentifier); ?></agencyName>
      </maintenanceAgency>
    <?php } ?>

    <?php if ($resource->language || $resource->script) { ?>
      <languageDeclaration>
        <?php foreach ($resource->language as $code) { ?>
          <language languageCode="<?php echo sfEacPlugin::to6392($code); ?>"><?php echo format_language($code); ?></language>
        <?php } ?>

        <?php foreach ($resource->script as $code) { ?>
          <script scriptCode="<?php echo $code; ?>"><?php echo format_script($code); ?></script>
        <?php } ?>
      </languageDeclaration>
    <?php } ?>

    <conventionDeclaration>
      <abbreviation>conventionDeclaration</abbreviation>

      <?php if (!empty($resource->rules)) { ?>
        <citation><?php echo esc_specialchars($resource->rules); ?></citation>
      <?php } ?>
    </conventionDeclaration>

    <localTypeDeclaration>
      <abbreviation>detailLevel</abbreviation>
      <citation>http://ica-atom.org/doc/RS-2#5.4</citation>
    </localTypeDeclaration>

    <?php if (!empty($resource->descriptionDetail)) { ?>
      <localControl localType="detailLevel">
        <term><?php echo esc_specialchars($resource->descriptionDetail); ?></term>
      </localControl>
    <?php } ?>

    <?php if (count($subjects = $resource->getSubjectAccessPoints()) > 0) { ?>
      <?php foreach ($subjects as $item) { ?>
        <localControl localType="subjectAccessPoint">
          <term vocalularySource="<?php echo url_for([$item->term, 'module' => 'term'], true); ?>"><?php echo esc_specialchars($item->term->getName(['cultureFallback' => true])); ?></term>
        </localControl>
      <?php } ?>
    <?php } ?>

    <?php if (!empty($eac->maintenanceHistory)) { ?>
      <maintenanceHistory><?php echo $eac->maintenanceHistory; ?></maintenanceHistory>
    <?php } ?>

    <?php if (!empty($resource->sources)) { ?>
      <sources>
        <source>
          <sourceEntry><?php echo esc_specialchars($resource->sources); ?></sourceEntry>
        </source>
      </sources>
    <?php } ?>
  </control>

  <cpfDescription>
    <identity>
      <?php if (!empty($resource->corporateBodyIdentifiers)) { ?>
        <entityId><?php echo esc_specialchars($resource->corporateBodyIdentifiers); ?></entityId>
      <?php } ?>

      <?php if ($eac->entityType) { ?>
        <entityType><?php echo $eac->entityType; ?></entityType>
      <?php } ?>

      <nameEntry>
        <?php if (!empty($resource->authorizedFormOfName)) { ?>
          <part><?php echo esc_specialchars($resource->authorizedFormOfName); ?></part>
        <?php } ?>

        <authorizedForm>conventionDeclaration</authorizedForm>
      </nameEntry>

      <?php foreach ($resource->getOtherNames(['typeId' => QubitTerm::STANDARDIZED_FORM_OF_NAME_ID]) as $item) { ?>
        <nameEntry localType="standardized">

          <part><?php echo esc_specialchars($item->name); ?></part>

          <alternativeForm>conventionDeclaration</alternativeForm>

        </nameEntry>
      <?php } ?>

      <?php foreach ($resource->getOtherNames(['typeId' => QubitTerm::OTHER_FORM_OF_NAME_ID]) as $item) { ?>
        <nameEntry>

          <part><?php echo esc_specialchars($item->name); ?></part>

          <alternativeForm>conventionDeclaration</alternativeForm>

        </nameEntry>
      <?php } ?>

      <?php foreach ($resource->getOtherNames(['typeId' => QubitTerm::PARALLEL_FORM_OF_NAME_ID]) as $item) { ?>
        <nameEntryParallel>
          <?php foreach ($item->otherNameI18ns as $otherName) { ?>
            <?php if (sfContext::getInstance()->getUser()->getCulture() == $otherName->culture) { ?>
              <nameEntry xml:lang="<?php echo sfEacPlugin::to6392($otherName->culture); ?>" scriptCode="Latn">
                <part><?php echo esc_specialchars($item->name); ?></part>

                <preferredForm>conventionDeclaration</preferredForm>
              </nameEntry>
            <?php } else { ?>
              <nameEntry xml:lang="<?php echo sfEacPlugin::to6392($otherName->culture); ?>" scriptCode="Latn">
                <part><?php echo esc_specialchars($otherName->name); ?></part>
              </nameEntry>
            <?php } ?>
          <?php } ?>

          <authorizedForm>conventionDeclaration</authorizedForm>

        </nameEntryParallel>
      <?php } ?>

    </identity>

    <?php if ($eac->hasDescriptionElements($resource)) { ?>
      <description>
        <?php if ($eac->existDates) { ?>
          <existDates><?php echo $eac->existDates; ?></existDates>
        <?php } ?>

        <?php if (!empty($resource->places)) { ?>
          <place localType="isaar-5.2.3">
            <placeEntry><?php echo esc_specialchars($resource->places); ?></placeEntry>
          </place>
        <?php } ?>

        <?php if (!empty($resource->legalStatus)) { ?>
          <legalStatus>
            <term><?php echo esc_specialchars($resource->legalStatus); ?></term>
          </legalStatus>
        <?php } ?>

        <?php if (!empty($resource->functions)) { ?>
          <function>
            <term><?php echo esc_specialchars($resource->functions); ?></term>
          </function>

          <occupation>
            <descriptiveNote><?php echo esc_specialchars($resource->functions); ?></descriptiveNote>
          </occupation>
        <?php } ?>

        <?php if (!empty($resource->mandates)) { ?>
          <mandate>
            <term><?php echo esc_specialchars($resource->mandates); ?></term>
          </mandate>
        <?php } ?>

        <?php if ($eac->structureOrGenealogy) { ?>
          <structureOrGenealogy><?php echo $eac->structureOrGenealogy; ?></structureOrGenealogy>
        <?php } ?>

        <?php if ($eac->generalContext) { ?>
          <generalContext><?php echo $eac->generalContext; ?></generalContext>
        <?php } ?>

        <?php if ($eac->biogHist) { ?>
          <biogHist id="<?php echo 'md5-'.md5(url_for([$resource, 'module' => 'actor'], true)); ?>"><?php echo $eac->biogHist; ?></biogHist>
        <?php } ?>

        <?php if (count($occupations = $resource->getOccupations()) > 0) { ?>
          <occupations>
            <?php foreach ($occupations as $item) { ?>
              <occupation>
                <term><?php echo esc_specialchars($item->term->getName(['cultureFallback' => true])); ?></term>
                <?php $note = $item->getNotesByType(['noteTypeId' => QubitTerm::ACTOR_OCCUPATION_NOTE_ID])->offsetGet(0); ?>
                <?php if (isset($note)) { ?>
                  <descriptiveNote>
                    <?php echo render_value($note->getContent(['cultureFallback' => true])); ?>
                  </descriptiveNote>
                <?php } ?>
              </occupation>
            <?php } ?>
          </occupations>
        <?php } ?>

        <?php if (count($places = $resource->getPlaceAccessPoints()) > 0) { ?>
          <?php foreach ($places as $item) { ?>
            <place localType="placeAccessPoint">
              <placeEntry vocabularySource="<?php echo url_for([$item->term, 'module' => 'term'], true); ?>"><?php echo esc_specialchars($item->term->getName(['cultureFallback' => true])); ?></placeEntry>
            </place>
          <?php } ?>
        <?php } ?>

      </description>
    <?php } ?>

    <?php if (count($resource->getActorRelations()) || count($eac->subjectOf) || count($eac->resourceRelation)
              || count($eac->functionRelation)) { ?>

      <relations>
        <?php foreach ($resource->getActorRelations() as $item) { ?>
          <cpfRelation cpfRelationType="<?php echo sfEacPlugin::toCpfRelationType($item->type->id); ?>" xlink:href="<?php echo url_for([$item->getOpposedObject($resource), 'module' => 'actor'], true); ?>" xlink:type="simple">
            <relationEntry><?php echo esc_specialchars(render_title($item->getOpposedObject($resource))); ?></relationEntry>
            <?php echo sfEacPlugin::renderDates($item); ?>
            <?php if (isset($item->description)) { ?>
              <descriptiveNote>
                <?php echo render_value($item->description); ?>
              </descriptiveNote>
            <?php } ?>
          </cpfRelation>
        <?php } ?>

        <?php foreach ($eac->subjectOf as $item) { ?>
          <resourceRelation resourceRelationType="subjectOf" xlink:href="<?php echo url_for([$item->subject, 'module' => 'informationobject'], true); ?>" xlink:type="simple">
            <relationEntry><?php echo esc_specialchars(render_title($item->subject)); ?></relationEntry>
          </resourceRelation>
        <?php } ?>

        <?php foreach ($eac->resourceRelation as $item) { ?>
          <resourceRelation <?php echo sfEacPlugin::toResourceRelationTypeAndXlinkRole($item->type); ?> xlink:href="<?php echo url_for([$item->object, 'module' => 'informationobject'], true); ?>" xlink:type="simple">
            <relationEntry><?php echo esc_specialchars(render_title($item->object)); ?></relationEntry>
            <?php echo sfEacPlugin::renderDates($item); ?>
            <?php if (isset($item->date)) { ?>
              <descriptiveNote>
                <?php echo render_value($item->date); ?>
              </descriptiveNote>
            <?php } ?>
          </resourceRelation>
        <?php } ?>

        <?php foreach ($eac->functionRelation as $item) { ?>
          <functionRelation xlink:href="<?php echo url_for([$item, 'module' => 'function'], true); ?>" xlink:type="simple">
            <relationEntry><?php echo esc_specialchars(render_title($item->subject)); ?></relationEntry>
            <?php echo sfEacPlugin::renderDates($item); ?>
            <?php if (0 < count($date = $item->getNotesByType(['noteTypeId' => QubitTerm::RELATION_NOTE_DATE_ID]))) { ?>
              <descriptiveNote>
                <?php echo render_value($date[0]); ?>
              </descriptiveNote>
            <?php } ?>
          </functionRelation>
        <?php } ?>
      </relations>
    <?php } ?>
  </cpfDescription>
</eac-cpf>
