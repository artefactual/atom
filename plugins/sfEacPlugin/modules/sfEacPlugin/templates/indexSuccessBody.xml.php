<?php echo '<?xml version="1.0" encoding="'.sfConfig::get('sf_charset', 'UTF-8')."\" ?>\n" ?>
<eac-cpf xmlns="urn:isbn:1-931666-33-4" xmlns:xlink="http://www.w3.org/1999/xlink">

  <control>
    <?php if (!empty($resource->descriptionIdentifier)): ?>
      <recordId><?php echo esc_specialchars($resource->descriptionIdentifier) ?></recordId>
    <?php endif; ?>

    <?php if (!empty($eac->maintenanceStatus)): ?>
      <maintenanceStatus><?php echo $eac->maintenanceStatus ?></maintenanceStatus>
    <?php endif; ?>

    <?php if (!empty($eac->publicationStatus)): ?>
      <publicationStatus><?php echo $eac->publicationStatus ?></publicationStatus>
    <?php endif; ?>

    <?php if (!empty($resource->institutionResponsibleIdentifier)): ?>
      <maintenanceAgency>
        <agencyName><?php echo esc_specialchars($resource->institutionResponsibleIdentifier) ?></agencyName>
      </maintenanceAgency>
    <?php endif; ?>

    <?php if ($resource->language || $resource->script): ?>
      <languageDeclaration>
        <?php foreach ($resource->language as $code): ?>
          <language languageCode="<?php echo sfEacPlugin::to6392($code) ?>"><?php echo format_language($code) ?></language>
        <?php endforeach; ?>

        <?php foreach ($resource->script as $code): ?>
          <script scriptCode="<?php echo $code ?>"><?php echo format_script($code) ?></script>
        <?php endforeach; ?>
      </languageDeclaration>
    <?php endif; ?>

    <conventionDeclaration>
      <abbreviation>conventionDeclaration</abbreviation>

      <?php if (!empty($resource->rules)): ?>
        <citation><?php echo esc_specialchars($resource->rules) ?></citation>
      <?php endif; ?>
    </conventionDeclaration>

    <localTypeDeclaration>
      <abbreviation>detailLevel</abbreviation>
      <citation>http://ica-atom.org/doc/RS-2#5.4</citation>
    </localTypeDeclaration>

    <?php if(!empty($resource->descriptionDetail)): ?>
      <localControl localType="detailLevel">
        <term><?php echo esc_specialchars($resource->descriptionDetail) ?></term>
      </localControl>
    <?php endif; ?>

    <?php if (!empty($eac->maintenanceHistory)): ?>
      <maintenanceHistory><?php echo $eac->maintenanceHistory ?></maintenanceHistory>
    <?php endif; ?>

    <?php if (!empty($resource->sources)): ?>
      <sources>
        <source>
          <sourceEntry><?php echo esc_specialchars($resource->sources) ?></sourceEntry>
        </source>
      </sources>
    <?php endif; ?>
  </control>

  <cpfDescription>
    <identity>
      <?php if (!empty($resource->corporateBodyIdentifiers)): ?>
        <entityId><?php echo esc_specialchars($resource->corporateBodyIdentifiers) ?></entityId>
      <?php endif; ?>

      <?php if ($eac->entityType): ?>
        <entityType><?php echo $eac->entityType ?></entityType>
      <?php endif; ?>

      <nameEntry>
        <?php if (!empty($resource->authorizedFormOfName)): ?>
          <part><?php echo esc_specialchars($resource->authorizedFormOfName) ?></part>
        <?php endif; ?>

        <authorizedForm>conventionDeclaration</authorizedForm>
      </nameEntry>

      <?php foreach ($resource->getOtherNames(array('typeId' => QubitTerm::STANDARDIZED_FORM_OF_NAME_ID)) as $item): ?>
        <nameEntry localType="standardized">

          <part><?php echo esc_specialchars($item->name) ?></part>

          <alternativeForm>conventionDeclaration</alternativeForm>

        </nameEntry>
      <?php endforeach; ?>

      <?php foreach ($resource->getOtherNames(array('typeId' => QubitTerm::OTHER_FORM_OF_NAME_ID)) as $item): ?>
        <nameEntry>

          <part><?php echo esc_specialchars($item->name) ?></part>

          <alternativeForm>conventionDeclaration</alternativeForm>

        </nameEntry>
      <?php endforeach; ?>

      <?php foreach ($resource->getOtherNames(array('typeId' => QubitTerm::PARALLEL_FORM_OF_NAME_ID)) as $item): ?>
        <nameEntryParallel>
          <?php foreach ($item->otherNameI18ns as $otherName): ?>
            <?php if (sfContext::getInstance()->getUser()->getCulture() == $otherName->culture): ?>
              <nameEntry xml:lang="<?php echo sfEacPlugin::to6392($otherName->culture) ?>" scriptCode="Latn">
                <part><?php echo esc_specialchars($item->name) ?></part>

                <preferredForm>conventionDeclaration</preferredForm>
              </nameEntry>
            <?php else: ?>
              <nameEntry xml:lang="<?php echo sfEacPlugin::to6392($otherName->culture) ?>" scriptCode="Latn">
                <part><?php echo esc_specialchars($otherName->name) ?></part>
              </nameEntry>
            <?php endif; ?>
          <?php endforeach; ?>

          <authorizedForm>conventionDeclaration</authorizedForm>

        </nameEntryParallel>
      <?php endforeach; ?>

    </identity>

    <?php if ($eac->hasDescriptionElements($resource)): ?>
      <description>
        <?php if ($eac->existDates): ?>
          <existDates><?php echo $eac->existDates ?></existDates>
        <?php endif; ?>

        <?php if (!empty($resource->places)): ?>
          <place>
            <placeEntry><?php echo esc_specialchars($resource->places) ?></placeEntry>
          </place>
        <?php endif; ?>

        <?php if (!empty($resource->legalStatus)): ?>
          <legalStatus>
            <term><?php echo esc_specialchars($resource->legalStatus) ?></term>
          </legalStatus>
        <?php endif; ?>

        <?php if (!empty($resource->functions)): ?>
          <function>
            <term><?php echo esc_specialchars($resource->functions) ?></term>
          </function>

          <occupation>
            <descriptiveNote><?php echo esc_specialchars($resource->functions) ?></descriptiveNote>
          </occupation>
        <?php endif; ?>

        <?php if (!empty($resource->mandates)): ?>
          <mandate>
            <term><?php echo esc_specialchars($resource->mandates) ?></term>
          </mandate>
        <?php endif; ?>

        <?php // The following $eac->* properties are magic and will always be set: ?>

        <?php if ($eac->structureOrGenealogy): ?>
          <structureOrGenealogy><?php echo $eac->structureOrGenealogy ?></structureOrGenealogy>
        <?php endif; ?>

        <?php if ($eac->generalContext): ?>
          <generalContext><?php echo $eac->generalContext ?></generalContext>
        <?php endif; ?>

        <?php if ($eac->biogHist): ?>
          <biogHist id="<?php echo 'md5-' . md5(url_for(array($resource, 'module' => 'actor'), true)) ?>"><?php echo $eac->biogHist ?></biogHist>
        <?php endif; ?>

        <?php if (count($occupations = $resource->getOccupations()) > 0): ?>
          <occupations>
            <?php foreach ($occupations as $item): ?>
              <occupation>
                <term><?php echo esc_specialchars($item->term->getName(array('cultureFallback' => true))) ?></term>
                <?php $note = $item->getNotesByType(array('noteTypeId' => QubitTerm::ACTOR_OCCUPATION_NOTE_ID))->offsetGet(0) ?>
                <?php if (isset($note)): ?>
                  <descriptiveNote>
                    <?php echo render_value('<p>'.$note->getContent(array('cultureFallback' => true))).'</p>' ?>
                  </descriptiveNote>
                <?php endif; ?>
              </occupation>
            <?php endforeach; ?>
          </occupations>
        <?php endif; ?>
      </description>
    <?php endif; ?>

    <?php if (count($resource->getActorRelations()) || count($eac->subjectOf) || count($eac->resourceRelation) ||
              count($eac->functionRelation)): ?>

      <relations>
        <?php foreach ($resource->getActorRelations() as $item): ?>
          <cpfRelation cpfRelationType="<?php echo sfEacPlugin::toCpfRelationType($item->type->id) ?>" xlink:href="<?php echo url_for(array($item->getOpposedObject($resource), 'module' => 'actor'), true) ?>" xlink:type="simple">
            <relationEntry><?php echo esc_specialchars(render_title($item->getOpposedObject($resource))) ?></relationEntry>
            <?php echo sfEacPlugin::renderDates($item) ?>
            <?php if (isset($item->description)): ?>
              <descriptiveNote>
                <?php echo render_value('<p>'.$item->description).'</p>' ?>
              </descriptiveNote>
            <?php endif; ?>
          </cpfRelation>
        <?php endforeach; ?>

        <?php foreach ($eac->subjectOf as $item): ?>
          <resourceRelation resourceRelationType="subjectOf" xlink:href="<?php echo url_for(array($item->subject, 'module' => 'informationobject'), true) ?>" xlink:type="simple">
            <relationEntry><?php echo esc_specialchars(render_title($item->subject)) ?></relationEntry>
          </resourceRelation>
        <?php endforeach; ?>

        <?php foreach ($eac->resourceRelation as $item): ?>
          <resourceRelation <?php echo sfEacPlugin::toResourceRelationTypeAndXlinkRole($item->type) ?> xlink:href="<?php echo url_for(array($item->object, 'module' => 'informationobject'), true) ?>" xlink:type="simple">
            <relationEntry><?php echo esc_specialchars(render_title($item->object)) ?></relationEntry>
            <?php echo sfEacPlugin::renderDates($item) ?>
            <?php if (isset($item->date)): ?>
              <descriptiveNote>
                <?php echo '<p>'.render_value($item->date).'</p>' ?>
              </descriptiveNote>
            <?php endif; ?>
          </resourceRelation>
        <?php endforeach; ?>

        <?php foreach ($eac->functionRelation as $item): ?>
          <functionRelation xlink:href="<?php echo url_for(array($item, 'module' => 'function'), true) ?>" xlink:type="simple">
            <relationEntry><?php echo esc_specialchars(render_title($item->subject)) ?></relationEntry>
            <?php echo sfEacPlugin::renderDates($item) ?>
            <?php if (0 < count($date = $item->getNotesByType(array('noteTypeId' => QubitTerm::RELATION_NOTE_DATE_ID)))): ?>
              <descriptiveNote>
                <?php echo render_value('<p>'.$date[0]).'</p>' ?>
              </descriptiveNote>
            <?php endif; ?>
          </functionRelation>
        <?php endforeach; ?>
      </relations>
    <?php endif; ?>
  </cpfDescription>
</eac-cpf>
