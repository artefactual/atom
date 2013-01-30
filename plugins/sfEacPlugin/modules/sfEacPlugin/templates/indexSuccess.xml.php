<?php echo '<?xml version="1.0" encoding="'.sfConfig::get('sf_charset', 'UTF-8')."\" ?>\n" ?>
<eac-cpf xmlns="urn:isbn:1-931666-33-4" xmlns:xlink="http://www.w3.org/1999/xlink">

  <control>

    <recordId><?php echo esc_specialchars($resource->descriptionIdentifier) ?></recordId>

    <maintenanceStatus><?php echo $eac->maintenanceStatus ?></maintenanceStatus>

    <publicationStatus><?php echo $eac->publicationStatus ?></publicationStatus>

    <maintenanceAgency>

      <agencyName><?php echo esc_specialchars($resource->institutionResponsibleIdentifier) ?></agencyName>

    </maintenanceAgency>

    <languageDeclaration>

      <?php foreach ($resource->language as $code): ?>
        <language languageCode="<?php echo sfEacPlugin::to6392($code) ?>"><?php echo format_language($code) ?></language>
      <?php endforeach; ?>

      <?php foreach ($resource->script as $code): ?>
        <script scriptCode="<?php echo $code ?>"><?php echo format_script($code) ?></script>
      <?php endforeach; ?>

    </languageDeclaration>

    <conventionDeclaration>

      <abbreviation>conventionDeclaration</abbreviation>

      <citation><?php echo esc_specialchars($resource->rules) ?></citation>

    </conventionDeclaration>

    <localTypeDeclaration>

      <abbreviation>detailLevel</abbreviation>

      <citation>http://ica-atom.org/doc/RS-2#5.4</citation>

    </localTypeDeclaration>

    <localControl localType="detailLevel">

      <term><?php echo esc_specialchars($resource->descriptionDetail) ?></term>

    </localControl>

    <maintenanceHistory><?php echo $eac->maintenanceHistory ?></maintenanceHistory>

    <sources>

      <source>

        <sourceEntry><?php echo esc_specialchars($resource->sources) ?></sourceEntry>

      </source>

    </sources>

  </control>

  <cpfDescription>

    <identity>

      <entityId><?php echo esc_specialchars($resource->corporateBodyIdentifiers) ?></entityId>

      <entityType><?php echo $eac->entityType ?></entityType>

      <nameEntry>

        <part><?php echo esc_specialchars($resource->authorizedFormOfName) ?></part>

        <authorizedForm>conventionDeclaration</authorizedForm>

      </nameEntry>

      <?php foreach ($resource->otherNames as $item): ?>
        <nameEntry>

          <part><?php echo esc_specialchars($item->name) ?></part>

          <alternativeForm>conventionDeclaration</alternativeForm>

        </nameEntry>
      <?php endforeach; ?>

    </identity>

    <description>

      <existDates><?php echo $eac->existDates ?></existDates>

      <place>

        <placeEntry><?php echo esc_specialchars($resource->places) ?></placeEntry>

      </place>

      <legalStatus>

        <term><?php echo esc_specialchars($resource->legalStatus) ?></term>

      </legalStatus>

      <function>

        <descriptiveNote><?php echo esc_specialchars($resource->functions) ?></descriptiveNote>

      </function>

      <occupation>

        <descriptiveNote><?php echo esc_specialchars($resource->functions) ?></descriptiveNote>

      </occupation>

      <mandate>

        <term><?php echo esc_specialchars($resource->mandates) ?></term>

      </mandate>

      <structureOrGenealogy><?php echo $eac->structureOrGenealogy ?></structureOrGenealogy>

      <generalContext><?php echo $eac->generalContext ?></generalContext>

      <biogHist><?php echo $eac->biogHist ?></biogHist>

    </description>

    <relations>

      <?php foreach ($resource->getActorRelations() as $item): ?>
        <cpfRelation cpfRelationType="<?php echo sfEacPlugin::toCpfRelationType($item->type->id) ?>" xlink:href="<?php echo url_for(array($item->getOpposedObject($resource), 'module' => 'actor'), true) ?>" xlink:type="simple">
          <relationEntry><?php echo render_title($item->getOpposedObject($resource)) ?></relationEntry>
          <?php echo sfEacPlugin::renderDates($item) ?>
<?php if (isset($item->description)): ?>
          <descriptiveNote>
            <?php echo '<p>'.render_value($item->description).'</p>' ?>
          </descriptiveNote>
<?php endif; ?>
        </cpfRelation>
      <?php endforeach; ?>

      <?php foreach ($eac->resourceRelation as $item): ?>
        <resourceRelation resourceRelationType="<?php echo sfEacPlugin::toResourceRelationType($item->type->id) ?>" xlink:href="<?php echo url_for(array($item->informationObject, 'module' => 'informationobject'), true) ?>" xlink:type="simple">
          <relationEntry><?php echo render_title($item->informationObject) ?></relationEntry>
          <?php echo sfEacPlugin::renderDates($item) ?>
<?php if (isset($item->date)): ?>
          <descriptiveNote>
            <?php echo render_value('<p>'.$item->date).'</p>' ?>
          </descriptiveNote>
<?php endif; ?>
        </resourceRelation>
      <?php endforeach; ?>

      <?php foreach ($eac->functionRelation as $item): ?>
        <functionRelation xlink:href="<?php echo url_for(array($item, 'module' => 'function'), true) ?>" xlink:type="simple">
          <relationEntry><?php echo render_title($item->subject) ?></relationEntry>
          <?php echo sfEacPlugin::renderDates($item) ?>
<?php if (0 < count($date = $item->getNotesByType(array('noteTypeId' => QubitTerm::RELATION_NOTE_DATE_ID)))): ?>
          <descriptiveNote>
            <?php echo render_value('<p>'.$date[0]).'</p>' ?>
          </descriptiveNote>
<?php endif; ?>
        </functionRelation>
      <?php endforeach; ?>

    </relations>

  </cpfDescription>

</eac-cpf>
