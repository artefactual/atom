<ead>
<eadheader langencoding="iso639-2b" countryencoding="iso3166-1" dateencoding="iso8601" repositoryencoding="iso15511" scriptencoding="iso15924" relatedencoding="DC">
  <?php echo $ead->renderEadId(); ?>
  <filedesc>
    <titlestmt>
      <?php if (0 < strlen($value = $resource->getTitle(['cultureFallback' => true]))) { ?>
        <titleproper encodinganalog="<?php echo $ead->getMetadataParameter('titleproper'); ?>"><?php echo escape_dc(esc_specialchars($value)); ?></titleproper>
      <?php } ?>
    </titlestmt>
    <?php
      // TODO: find out if we need this element as it's not imported by our EAD importer
      if (0 < strlen($value = $resource->getEdition(['cultureFallback' => true]))) { ?>
      <editionstmt>
        <edition><?php echo escape_dc(esc_specialchars($value)); ?></edition>
      </editionstmt>
    <?php } ?>
    <?php if ($value = $resource->getRepository(['inherit' => true])) { ?>
      <publicationstmt>
        <publisher encodinganalog="<?php echo $ead->getMetadataParameter('publisher'); ?>"><?php echo escape_dc(esc_specialchars($value->__toString())); ?></publisher>
        <?php if ($address = $value->getPrimaryContact()) { ?>
          <address>
            <?php if (0 < strlen($addressline = $address->getStreetAddress())) { ?>
              <addressline><?php echo escape_dc(esc_specialchars($addressline)); ?></addressline>
            <?php } ?>
            <?php if (0 < strlen($addressline = $address->getCity())) { ?>
              <addressline><?php echo escape_dc(esc_specialchars($addressline)); ?></addressline>
            <?php } ?>
            <?php if (0 < strlen($addressline = $address->getRegion())) { ?>
              <addressline><?php echo escape_dc(esc_specialchars($addressline)); ?></addressline>
            <?php } ?>
            <?php if (0 < strlen($addressline = $resource->getRepositoryCountry())) { ?>
              <addressline><?php echo escape_dc(esc_specialchars($addressline)); ?></addressline>
            <?php } ?>
            <?php if (0 < strlen($addressline = $address->getPostalCode())) { ?>
              <addressline><?php echo escape_dc(esc_specialchars($addressline)); ?></addressline>
            <?php } ?>
            <?php if (0 < strlen($addressline = $address->getTelephone())) { ?>
              <addressline><?php echo __('Telephone: ').escape_dc(esc_specialchars($addressline)); ?></addressline>
            <?php } ?>
            <?php if (0 < strlen($addressline = $address->getFax())) { ?>
              <addressline><?php echo __('Fax: ').escape_dc(esc_specialchars($addressline)); ?></addressline>
            <?php } ?>
            <?php if (0 < strlen($addressline = $address->getEmail())) { ?>
              <addressline><?php echo __('Email: ').escape_dc(esc_specialchars($addressline)); ?></addressline>
            <?php } ?>
            <?php if (0 < strlen($addressline = $address->getWebsite())) { ?>
              <addressline><?php echo escape_dc(esc_specialchars($addressline)); ?></addressline>
            <?php } ?>
          </address>
        <?php } ?>
        <date normal="<?php echo $ead->getPublicationDate(); ?>" encodinganalog="<?php echo $ead->getMetadataParameter('date'); ?>"><?php echo escape_dc(esc_specialchars($ead->getPublicationDate())); ?></date>
      </publicationstmt>
    <?php } ?>
  </filedesc>
  <profiledesc>
    <creation>
      <?php echo __('Generated by %1%', ['%1%' => $ead->version]); ?>
      <date normal="<?php echo gmdate('o-m-d'); ?>"><?php echo gmdate('o-m-d H:i e'); ?></date>
    </creation>
    <langusage>
      <language langcode="<?php echo strtolower($iso639convertor->getID2($exportLanguage)); ?>"><?php echo format_language($exportLanguage); ?></language>
      <?php if (0 < strlen($languageOfDescription = $resource->getPropertyByName('languageOfDescription')->__toString())) { ?>
        <?php $langsOfDesc = unserialize($languageOfDescription); ?>
        <?php if (is_array($langsOfDesc)) { ?>
          <?php foreach ($langsOfDesc as $langcode) { ?>
            <?php if ($langcode != $exportLanguage) { ?>
              <language langcode="<?php echo strtolower($iso639convertor->getID2($langcode)); ?>"><?php echo format_language($langcode); ?></language>
            <?php } ?>
          <?php } ?>
        <?php } ?>
      <?php } ?>
      <?php foreach ($resource->scriptOfDescription as $code) { ?>
        <language scriptcode="<?php echo $code; ?>"><?php echo format_script($code); ?></language>
      <?php } ?>
    </langusage>
    <?php if (0 < strlen($rules = $resource->getRules(['cultureFallback' => true]))) { ?>
      <descrules <?php if (0 < strlen($encoding = $ead->getMetadataParameter('descrules'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?>><?php echo escape_dc(esc_specialchars($rules)); ?></descrules>
    <?php } ?>
  </profiledesc>
</eadheader>

<archdesc <?php echo $ead->renderLOD($resource, $eadLevels); ?> relatedencoding="<?php echo $ead->getMetadataParameter('relatedencoding'); ?>">
  <?php
    $resourceVar = 'resource';
    $creators = ${$resourceVar}->getCreators();
    $events = ${$resourceVar}->getActorEvents(['eventTypeId' => QubitTerm::CREATION_ID]);

    $topLevelDid = true;

    include 'indexSuccessBodyDidElement.xml.php';

    include 'indexSuccessBodyBioghistElement.xml.php';

    $topLevelDid = false;
  ?>
  <?php if ($resource->getPublicationStatus()) { ?>
    <odd type="publicationStatus"><p><?php echo escape_dc(esc_specialchars($resource->getPublicationStatus())); ?></p></odd>
  <?php } ?>
  <?php if ($resource->descriptionDetailId) { ?>
    <odd type="levelOfDetail"><p><?php echo escape_dc(esc_specialchars((string) QubitTerm::getById($resource->descriptionDetailId))); ?></p></odd>
  <?php } ?>
  <?php $descriptionStatus = ($resource->descriptionStatusId) ? QubitTerm::getById($resource->descriptionStatusId) : ''; ?>
  <?php if ($descriptionStatus) { ?>
    <odd type="statusDescription"><p><?php echo escape_dc(esc_specialchars((string) $descriptionStatus)); ?></p></odd>
  <?php } ?>
  <?php if ($resource->descriptionIdentifier) { ?>
    <odd type="descriptionIdentifier"><p><?php echo escape_dc(esc_specialchars($resource->descriptionIdentifier)); ?></p></odd>
  <?php } ?>
  <?php if ($resource->institutionResponsibleIdentifier) { ?>
    <odd type="institutionIdentifier"><p><?php echo escape_dc(esc_specialchars($resource->institutionResponsibleIdentifier)); ?></p></odd>
  <?php } ?>

  <?php
  // Load taxonomies into variables to avoid use of magic numbers
  $termData = QubitFlatfileImport::loadTermsFromTaxonomies([
      QubitTaxonomy::RAD_NOTE_ID => 'radNoteTypes',
      QubitTaxonomy::RAD_TITLE_NOTE_ID => 'titleNoteTypes',
      QubitTaxonomy::DACS_NOTE_ID => 'dacsSpecializedNoteTypes',
  ]);

  $radTitleNotes = [
      'Variations in title' => 'titleVariation',
      'Attributions and conjectures' => 'titleAttributions',
      'Continuation of title' => 'titleContinuation',
      'Statements of responsibility' => 'titleStatRep',
      'Parallel titles and other title information' => 'titleParallel',
      'Source of title proper' => 'titleSource',
  ];

  foreach ($radTitleNotes as $name => $xmlType) {
      $noteTypeId = array_search($name, $termData['titleNoteTypes']['en']);

      if (0 < count($notes = $resource->getNotesByType(['noteTypeId' => $noteTypeId]))) {
          foreach ($notes as $note) { ?>
        <odd type="<?php echo $xmlType; ?>" <?php if (0 < strlen($encoding = $ead->getMetadataParameter($xmlType))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?>><p><?php echo escape_dc(esc_specialchars($note->getContent(['cultureFallback' => true]))); ?></p></odd>
      <?php }
      }
  }

  $radNotes = [
      'Edition' => 'edition',
      'Physical description' => 'physDesc',
      'Conservation' => 'conservation',
      'Accompanying material' => 'material',
      'Alpha-numeric designations' => 'alphanumericDesignation',
      "Publisher's series" => 'bibSeries',
      'Rights' => 'rights',
  ];

  foreach ($radNotes as $name => $xmlType) {
      $noteTypeId = array_search($name, $termData['radNoteTypes']['en']);

      if (0 < count($notes = $resource->getNotesByType(['noteTypeId' => $noteTypeId]))) {
          foreach ($notes as $note) { ?>
        <odd type="<?php echo $xmlType; ?>" <?php if (0 < strlen($encoding = $ead->getMetadataParameter($xmlType))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?>><p><?php echo escape_dc(esc_specialchars($note->getContent(['cultureFallback' => true]))); ?></p></odd>
      <?php }
      }
  }

  $dacsSpecializedNotes = [
      'Conservation' => 'dacsConservation',
      'Citation' => 'dacsCitation',
      'Alphanumeric designations' => 'dacsAlphanumericDesignation',
      'Variant title information' => 'dacsVariantTitleInformation',
      'Processing information' => 'dacsProcessingInformation',
  ];

  foreach ($dacsSpecializedNotes as $name => $xmlType) {
      $noteTypeId = array_search($name, $termData['dacsSpecializedNoteTypes']['en']);

      if (0 < count($notes = $resource->getNotesByType(['noteTypeId' => $noteTypeId]))) {
          foreach ($notes as $note) { ?>
        <odd type="<?php echo $xmlType; ?>" <?php if (0 < strlen($encoding = $ead->getMetadataParameter($xmlType))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?>><p><?php echo escape_dc(esc_specialchars($note->getContent(['cultureFallback' => true]))); ?></p></odd>
      <?php }
      }
  } ?>

  <?php if (0 < strlen($value = $resource->getPropertyByName('noteOnPublishersSeries')->__toString())) { ?>
    <odd type='bibSeries'><p><?php echo escape_dc(esc_specialchars($value)); ?></p></odd>
  <?php } ?>
  <?php if (0 < strlen($value = $resource->getScopeAndContent(['cultureFallback' => true]))) { ?>
    <scopecontent encodinganalog="<?php echo $ead->getMetadataParameter('scopecontent'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></scopecontent><?php } ?>
  <?php if (0 < strlen($value = $resource->getArrangement(['cultureFallback' => true]))) { ?>
    <arrangement encodinganalog="<?php echo $ead->getMetadataParameter('arrangement'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></arrangement><?php } ?>

  <?php if ($ead->getControlAccessFields($resource, $materialTypes, $genres, $subjects, $names, $places, $placeEvents)) { ?>
    <controlaccess>
      <?php foreach ($resource->getActorEvents() as $event) { ?>
        <?php if ('Creator' != $event->getType()->getRole()) { ?>

        <?php if (QubitTerm::PERSON_ID == $event->getActor()->getEntityTypeId()) { ?>
          <persname role="<?php echo $event->getType()->getRole(); ?>" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('name'.$event->getType()->getRole()))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } elseif (0 < strlen($encoding = $ead->getMetadataParameter('nameDefault'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?> id="atom_<?php echo $event->id; ?>_actor"><?php echo escape_dc(esc_specialchars(render_title($event->getActor()))); ?></persname>
        <?php } elseif (QubitTerm::FAMILY_ID == $event->getActor()->getEntityTypeId()) { ?>
          <famname role="<?php echo $event->getType()->getRole(); ?>" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('name'.$event->getType()->getRole()))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } elseif (0 < strlen($encoding = $ead->getMetadataParameter('nameDefault'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?> id="atom_<?php echo $event->id; ?>_actor"><?php echo escape_dc(esc_specialchars(render_title($event->getActor()))); ?></famname>
        <?php } elseif (QubitTerm::CORPORATE_BODY_ID == $event->getActor()->getEntityTypeId()) { ?>
          <corpname role="<?php echo $event->getType()->getRole(); ?>" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('name'.$event->getType()->getRole()))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } elseif (0 < strlen($encoding = $ead->getMetadataParameter('nameDefault'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?> id="atom_<?php echo $event->id; ?>_actor"><?php echo escape_dc(esc_specialchars(render_title($event->getActor()))); ?></corpname>
        <?php } else { ?>
          <name role="<?php echo $event->getType()->getRole(); ?>" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('name'.$event->getType()->getRole()))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } elseif (0 < strlen($encoding = $ead->getMetadataParameter('nameDefault'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?> id="atom_<?php echo $event->id; ?>_actor"><?php echo escape_dc(esc_specialchars(render_title($event->getActor()))); ?></name>
        <?php } ?>

        <?php } ?>
      <?php } ?>
      <?php foreach ($names as $name) { ?>
        <?php if ('QubitTerm' === get_class($name->getObject())) { ?>
          <name role="subject"><?php echo escape_dc(esc_specialchars((string) $name->getObject())); ?></name>
        <?php } else { ?>
          <?php if (QubitTerm::PERSON_ID == $name->getObject()->getEntityTypeId()) { ?>
            <persname role="subject"><?php echo escape_dc(esc_specialchars((string) $name->getObject())); ?></persname>
          <?php } elseif (QubitTerm::FAMILY_ID == $name->getObject()->getEntityTypeId()) { ?>
            <famname role="subject"><?php echo escape_dc(esc_specialchars((string) $name->getObject())); ?></famname>
          <?php } elseif (QubitTerm::CORPORATE_BODY_ID == $name->getObject()->getEntityTypeId()) { ?>
            <corpname role="subject"><?php echo escape_dc(esc_specialchars((string) $name->getObject())); ?></corpname>
          <?php } else { ?>
            <name role="subject"><?php echo escape_dc(esc_specialchars((string) $name->getObject())); ?></name>
          <?php } ?>
        <?php } ?>
      <?php } ?>
      <?php foreach ($materialTypes as $materialtype) { ?>
        <genreform source="rad" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('materialType'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?>><?php echo escape_dc(escape_dc(esc_specialchars((string) $materialtype->getTerm()))); ?></genreform>
      <?php } ?>
      <?php foreach ($genres as $genre) { ?>
        <genreform><?php echo escape_dc(esc_specialchars((string) $genre->getTerm())); ?></genreform>
      <?php } ?>
      <?php foreach ($subjects as $subject) { ?>
        <subject<?php if ($subject->getTerm()->code) { ?> authfilenumber="<?php echo $subject->getTerm()->code; ?>"<?php } ?>><?php echo escape_dc(esc_specialchars((string) $subject->getTerm())); ?></subject>
      <?php } ?>
      <?php foreach ($places as $place) { ?>
        <geogname><?php echo escape_dc(esc_specialchars((string) $place->getTerm())); ?></geogname>
      <?php } ?>
      <?php foreach ($placeEvents as $place) { ?>
        <geogname role="<?php echo $place->getObject()->getType()->getRole(); ?>" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('geog'.$place->getObject()->getType()->getRole()))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } elseif (0 < strlen($encoding = $ead->getMetadataParameter('geogDefault'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?> id="atom_<?php echo $place->objectId; ?>_place"><?php echo escape_dc(esc_specialchars((string) $place->getTerm())); ?></geogname>
      <?php } ?>
    </controlaccess>
  <?php } ?>
  <?php if (0 < strlen($value = $resource->getPhysicalCharacteristics(['cultureFallback' => true]))) { ?>
    <phystech encodinganalog="<?php echo $ead->getMetadataParameter('phystech'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></phystech>
  <?php } ?>
  <?php if (0 < strlen($value = $resource->getAppraisal(['cultureFallback' => true]))) { ?>
    <appraisal <?php if (0 < strlen($encoding = $ead->getMetadataParameter('appraisal'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?>><p><?php echo escape_dc(esc_specialchars($value)); ?></p></appraisal>
  <?php } ?>
  <?php if (0 < strlen($value = $resource->getAcquisition(['cultureFallback' => true]))) { ?>
    <acqinfo encodinganalog="<?php echo $ead->getMetadataParameter('acqinfo'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></acqinfo>
  <?php } ?>
  <?php if (0 < strlen($value = $resource->getAccruals(['cultureFallback' => true]))) { ?>
    <accruals encodinganalog="<?php echo $ead->getMetadataParameter('accruals'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></accruals>
  <?php } ?>
  <?php if (0 < strlen($value = $resource->getArchivalHistory(['cultureFallback' => true]))) { ?>
    <custodhist encodinganalog="<?php echo $ead->getMetadataParameter('custodhist'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></custodhist>
  <?php } ?>

  <?php $archivistsNotes = $resource->getNotesByType(['noteTypeId' => QubitTerm::ARCHIVIST_NOTE_ID]); ?>
  <?php if (0 < strlen($value = $resource->getRevisionHistory(['cultureFallback' => true])) || 0 < count($archivistsNotes)) { ?>

    <processinfo>
      <?php if ($value) { ?>
        <p><date><?php echo escape_dc(esc_specialchars($value)); ?></date></p>
      <?php } ?>

      <?php if (0 < count($archivistsNotes)) { ?>
        <?php foreach ($archivistsNotes as $note) { ?>
          <p><?php echo escape_dc(esc_specialchars($note->getContent(['cultureFallback' => true]))); ?></p>
        <?php } ?>
      <?php } ?>
    </processinfo>
  <?php } ?>

  <?php if (0 < strlen($value = $resource->getLocationOfOriginals(['cultureFallback' => true]))) { ?>
    <originalsloc encodinganalog="<?php echo $ead->getMetadataParameter('originalsloc'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></originalsloc>
  <?php } ?>
  <?php if (0 < strlen($value = $resource->getLocationOfCopies(['cultureFallback' => true]))) { ?>
    <altformavail encodinganalog="<?php echo $ead->getMetadataParameter('altformavail'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></altformavail>
  <?php } ?>
  <?php if (0 < strlen($value = $resource->getRelatedUnitsOfDescription(['cultureFallback' => true]))) { ?>
    <relatedmaterial encodinganalog="<?php echo $ead->getMetadataParameter('relatedmaterial'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></relatedmaterial>
  <?php } ?>
  <?php if (0 < strlen($value = $resource->getAccessConditions(['cultureFallback' => true]))) { ?>
    <accessrestrict encodinganalog="<?php echo $ead->getMetadataParameter('accessrestrict'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></accessrestrict>
  <?php } ?>
  <?php if (0 < strlen($value = $resource->getReproductionConditions(['cultureFallback' => true]))) { ?>
    <userestrict encodinganalog="<?php echo $ead->getMetadataParameter('userestrict'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></userestrict>
  <?php } ?>
  <?php if (0 < strlen($value = $resource->getFindingAids(['cultureFallback' => true]))) { ?>
    <otherfindaid encodinganalog="<?php echo $ead->getMetadataParameter('otherfindaid'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></otherfindaid>
  <?php } ?>
  <?php if (0 < count($publicationNotes = $resource->getNotesByType(['noteTypeId' => QubitTerm::PUBLICATION_NOTE_ID]))) { ?>
    <?php foreach ($publicationNotes as $note) { ?>
      <bibliography <?php if (0 < strlen($encoding = $ead->getMetadataParameter('bibliography'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?>><p><?php echo escape_dc(esc_specialchars($note->getContent(['cultureFallback' => true]))); ?></p></bibliography>
    <?php } ?>
  <?php } ?>

  <?php if (!array_key_exists('current-level-only', $options) || !$options['current-level-only']) { ?>
  <dsc type="combined">

    <?php $nestedRgt = []; ?>
    <?php foreach ($resource->getDescendantsForExport($options) as $descendant) { ?>

      <?php $rgt = $descendant->rgt; ?>
      <?php while (count($nestedRgt) > 0 && $rgt > $nestedRgt[count($nestedRgt) - 1]) { ?>
        <?php array_pop($nestedRgt); ?>
        </c>
      <?php } ?>

      <c <?php echo $ead->renderLOD($descendant, $eadLevels); ?>>

      <?php
        $resourceVar = 'descendant';
        $creators = ${$resourceVar}->getCreators();
        $events = ${$resourceVar}->getActorEvents(['eventTypeId' => QubitTerm::CREATION_ID]);

        include 'indexSuccessBodyDidElement.xml.php';

        include 'indexSuccessBodyBioghistElement.xml.php';
      ?>

      <?php if ($descendant->getPublicationStatus()) { ?>
        <odd type="publicationStatus"><p><?php echo escape_dc(esc_specialchars($descendant->getPublicationStatus())); ?></p></odd>
      <?php } ?>

      <?php if (0 < strlen($value = $descendant->getScopeAndContent(['cultureFallback' => true]))) { ?>
        <scopecontent encodinganalog="<?php echo $ead->getMetadataParameter('scopecontent'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></scopecontent>
      <?php } ?>

      <?php if (0 < strlen($value = $descendant->getArrangement(['cultureFallback' => true]))) { ?>
        <arrangement encodinganalog="<?php echo $ead->getMetadataParameter('arrangement'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></arrangement>
      <?php } ?>

      <?php if ($ead->getControlAccessFields($descendant, $materialTypes, $genres, $subjects, $names, $places, $placeEvents)) { ?>

        <controlaccess>

          <?php foreach ($descendant->getActorEvents() as $event) { ?>
            <?php if ('Creator' != $event->getType()->getRole()) { ?>

              <?php if (QubitTerm::PERSON_ID == $event->getActor()->getEntityTypeId()) { ?>
                <persname role="<?php echo $event->getType()->getRole(); ?>" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('name'.$event->getType()->getRole()))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } elseif (0 < strlen($encoding = $ead->getMetadataParameter('nameDefault'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?> id="atom_<?php echo $event->id; ?>_actor"><?php echo escape_dc(esc_specialchars(render_title($event->getActor(['cultureFallback' => true])))); ?> </persname>
              <?php } elseif (QubitTerm::FAMILY_ID == $event->getActor()->getEntityTypeId()) { ?>
                <famname role="<?php echo $event->getType()->getRole(); ?>" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('name'.$event->getType()->getRole()))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } elseif (0 < strlen($encoding = $ead->getMetadataParameter('nameDefault'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?> id="atom_<?php echo $event->id; ?>_actor"><?php echo escape_dc(esc_specialchars(render_title($event->getActor(['cultureFallback' => true])))); ?> </famname>
              <?php } elseif (QubitTerm::CORPORATE_BODY_ID == $event->getActor()->getEntityTypeId()) { ?>
                <corpname role="<?php echo $event->getType()->getRole(); ?>" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('name'.$event->getType()->getRole()))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } elseif (0 < strlen($encoding = $ead->getMetadataParameter('nameDefault'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?> id="atom_<?php echo $event->id; ?>_actor"><?php echo escape_dc(esc_specialchars(render_title($event->getActor(['cultureFallback' => true])))); ?> </corpname>
              <?php } else { ?>
                <name role="<?php echo $event->getType()->getRole(); ?>" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('name'.$event->getType()->getRole()))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } elseif (0 < strlen($encoding = $ead->getMetadataParameter('nameDefault'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?> id="atom_<?php echo $event->id; ?>_actor"><?php echo escape_dc(esc_specialchars(render_title($event->getActor(['cultureFallback' => true])))); ?> </name>
              <?php } ?>

            <?php } ?>
          <?php } ?>

          <?php foreach ($names as $name) { ?>
            <?php if (QubitTerm::PERSON_ID == $name->getObject()->getEntityTypeId()) { ?>
              <persname role="subject"><?php echo escape_dc(esc_specialchars((string) $name->getObject())); ?></persname>
            <?php } elseif (QubitTerm::FAMILY_ID == $name->getObject()->getEntityTypeId()) { ?>
              <famname role="subject"><?php echo escape_dc(esc_specialchars((string) $name->getObject())); ?></famname>
            <?php } elseif (QubitTerm::CORPORATE_BODY_ID == $name->getObject()->getEntityTypeId()) { ?>
              <corpname role="subject"><?php echo escape_dc(esc_specialchars((string) $name->getObject())); ?></corpname>
            <?php } else { ?>
              <name role="subject"><?php echo escape_dc(esc_specialchars((string) $name->getObject())); ?></name>
            <?php } ?>
          <?php } ?>

          <?php foreach ($materialTypes as $materialtype) { ?>
            <genreform source="rad" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('materialType'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?>><?php echo escape_dc(esc_specialchars((string) $materialtype->getTerm())); ?></genreform>
          <?php } ?>

          <?php foreach ($genres as $genre) { ?>
            <genreform <?php if (0 < strlen($encoding = $ead->getMetadataParameter('genreform'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?>><?php echo escape_dc(escape_dc(esc_specialchars((string) $genre->getTerm()))); ?></genreform>
          <?php } ?>

          <?php foreach ($subjects as $subject) { ?>
            <subject><?php echo escape_dc(esc_specialchars((string) $subject->getTerm())); ?></subject>
          <?php } ?>

          <?php foreach ($places as $place) { ?>
            <geogname><?php echo escape_dc(esc_specialchars((string) $place->getTerm())); ?></geogname>
          <?php } ?>

          <?php foreach ($placeEvents as $place) { ?>
            <geogname role="<?php echo $place->getObject()->getType()->getRole(); ?>" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('geog'.$place->getObject()->getType()->getRole()))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } elseif (0 < strlen($encoding = $ead->getMetadataParameter('geogDefault'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?> id="atom_<?php echo $place->objectId; ?>_place"><?php echo escape_dc(esc_specialchars((string) $place->getTerm())); ?></geogname>
          <?php } ?>

        </controlaccess>

      <?php } ?>

      <?php if (0 < strlen($value = $descendant->getPhysicalCharacteristics(['cultureFallback' => true]))) { ?>
        <phystech encodinganalog="<?php echo $ead->getMetadataParameter('phystech'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></phystech>
      <?php } ?>

      <?php if (0 < strlen($value = $descendant->getAppraisal(['cultureFallback' => true]))) { ?>
        <appraisal <?php if (0 < strlen($encoding = $ead->getMetadataParameter('appraisal'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?>><p><?php echo escape_dc(esc_specialchars($value)); ?></p></appraisal>
      <?php } ?>

      <?php if (0 < strlen($value = $descendant->getAcquisition(['cultureFallback' => true]))) { ?>
        <acqinfo encodinganalog="<?php echo $ead->getMetadataParameter('acqinfo'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></acqinfo>
      <?php } ?>

      <?php if (0 < strlen($value = $descendant->getAccruals(['cultureFallback' => true]))) { ?>
        <accruals encodinganalog="<?php echo $ead->getMetadataParameter('accruals'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></accruals>
      <?php } ?>

      <?php if (0 < strlen($value = $descendant->getArchivalHistory(['cultureFallback' => true]))) { ?>
        <custodhist encodinganalog="<?php echo $ead->getMetadataParameter('custodhist'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></custodhist>
      <?php } ?>

      <?php if (0 < strlen($value = $descendant->getRevisionHistory(['cultureFallback' => true]))) { ?>
        <processinfo><p><date><?php echo escape_dc(esc_specialchars($value)); ?></date></p></processinfo>
      <?php } ?>

      <?php if (0 < count($archivistsNotes = $descendant->getNotesByType(['noteTypeId' => QubitTerm::ARCHIVIST_NOTE_ID]))) { ?>
        <?php foreach ($archivistsNotes as $note) { ?>
          <processinfo><p><?php echo escape_dc(esc_specialchars($note->getContent(['cultureFallback' => true]))); ?></p></processinfo>
        <?php } ?>
      <?php } ?>

      <?php if (0 < strlen($value = $descendant->getLocationOfOriginals(['cultureFallback' => true]))) { ?>
        <originalsloc encodinganalog="<?php echo $ead->getMetadataParameter('originalsloc'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></originalsloc>
      <?php } ?>

      <?php if (0 < strlen($value = $descendant->getLocationOfCopies(['cultureFallback' => true]))) { ?>
        <altformavail encodinganalog="<?php echo $ead->getMetadataParameter('altformavail'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></altformavail>
      <?php } ?>

      <?php if (0 < strlen($value = $descendant->getRelatedUnitsOfDescription(['cultureFallback' => true]))) { ?>
        <relatedmaterial encodinganalog="<?php echo $ead->getMetadataParameter('relatedmaterial'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></relatedmaterial>
      <?php } ?>

      <?php if (0 < strlen($value = $descendant->getAccessConditions(['cultureFallback' => true]))) { ?>
        <accessrestrict encodinganalog="<?php echo $ead->getMetadataParameter('accessrestrict'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></accessrestrict>
      <?php } ?>

      <?php if (0 < strlen($value = $descendant->getReproductionConditions(['cultureFallback' => true]))) { ?>
        <userestrict encodinganalog="<?php echo $ead->getMetadataParameter('userestrict'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></userestrict>
      <?php } ?>

      <?php if (0 < strlen($value = $descendant->getFindingAids(['cultureFallback' => true]))) { ?>
        <otherfindaid encodinganalog="<?php echo $ead->getMetadataParameter('otherfindaid'); ?>"><p><?php echo escape_dc(esc_specialchars($value)); ?></p></otherfindaid>
      <?php } ?>

      <?php if (0 < count($publicationNotes = $descendant->getNotesByType(['noteTypeId' => QubitTerm::PUBLICATION_NOTE_ID]))) { ?>
        <?php foreach ($publicationNotes as $note) { ?>
          <bibliography <?php if (0 < strlen($encoding = $ead->getMetadataParameter('bibliography'))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?>><p><?php echo escape_dc(esc_specialchars($note->getContent(['cultureFallback' => true]))); ?></p></bibliography>
        <?php } ?>
      <?php } ?>

      <?php foreach ($radNotes as $name => $xmlType) { ?>
          <?php $noteTypeId = array_search($name, $termData['radNoteTypes']['en']); ?>

          <?php if (0 < count($notes = $descendant->getNotesByType(['noteTypeId' => $noteTypeId]))) { ?>
            <?php foreach ($notes as $note) { ?>
              <odd type="<?php echo $xmlType; ?>" <?php if (0 < strlen($encoding = $ead->getMetadataParameter($xmlType))) { ?>encodinganalog="<?php echo $encoding; ?>"<?php } ?>><p><?php echo escape_dc(esc_specialchars($note->getContent(['cultureFallback' => true]))); ?></p></odd>
            <?php } ?>
          <?php } ?>
      <?php } ?>

      <?php if ($descendant->rgt == $descendant->lft + 1) { ?>
        </c>
      <?php } else { ?>
        <?php array_push($nestedRgt, $descendant->rgt); ?>
      <?php } ?>

    <?php } ?>

    <?php while (count($nestedRgt) > 0) { ?>
      <?php array_pop($nestedRgt); ?>
      </c>
    <?php } ?>

  </dsc>
  <?php } ?>
</archdesc>
</ead>
