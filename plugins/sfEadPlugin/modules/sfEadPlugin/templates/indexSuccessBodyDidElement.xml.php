<did>

  <?php if (check_field_visibility('app_element_visibility_physical_storage', $options)): ?>
    <?php $objects = $$resourceVar->getPhysicalObjects() ?>
    <?php foreach ($objects as $object): ?>
      <?php if (0 < strlen($object->location)): ?>
        <physloc id="<?php echo 'physloc'.str_pad(++$$counterVar, 4, '0', STR_PAD_LEFT) ?>"><?php echo escape_dc(esc_specialchars($object->location)) ?></physloc>
      <?php endif; ?>
      <container <?php echo $ead->getEadContainerAttributes($object) ?><?php if (0 < strlen($object->location)): ?> parent="<?php echo 'physloc'.str_pad($$counterVar, 4, '0', STR_PAD_LEFT) ?>"<?php endif; ?>>
        <?php if (0 < strlen($object->name)): ?>
          <?php echo escape_dc(esc_specialchars($object->name)) ?>
        <?php endif; ?>
      </container>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (0 < strlen($$resourceVar->getPropertyByName('titleProperOfPublishersSeries')->__toString())
    || 0 < strlen($$resourceVar->getPropertyByName('parallelTitleOfPublishersSeries')->__toString())
      || 0 < strlen($$resourceVar->getPropertyByName('otherTitleInformationOfPublishersSeries')->__toString())
        || 0 < strlen($$resourceVar->getPropertyByName('statementOfResponsibilityRelatingToPublishersSeries')->__toString())
          || 0 < strlen($$resourceVar->getPropertyByName('numberingWithinPublishersSeries')->__toString())): ?>

    <unittitle>
      <bibseries>

        <?php if (0 < strlen($value = $$resourceVar->getPropertyByName('titleProperOfPublishersSeries')->__toString())): ?>
          <title <?php if (0 < strlen($encoding = $ead->getMetadataParameter('titleProperOfPublishersSeries'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><?php echo escape_dc(esc_specialchars($value)) ?></title>
        <?php endif; ?>
        <?php if (0 < strlen($value = $$resourceVar->getPropertyByName('parallelTitleOfPublishersSeries')->__toString())): ?>
          <title type="parallel" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('parallelTitleOfPublishersSeries'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><?php echo escape_dc(esc_specialchars($value)) ?></title>
        <?php endif; ?>
        <?php if (0 < strlen($value = $$resourceVar->getPropertyByName('otherTitleInformationOfPublishersSeries')->__toString())): ?>
          <title type="otherInfo" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('otherTitleInformationOfPublishersSeries'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><?php echo escape_dc(esc_specialchars($value)) ?></title>
        <?php endif; ?>
        <?php if (0 < strlen($value = $$resourceVar->getPropertyByName('statementOfResponsibilityRelatingToPublishersSeries')->__toString())): ?>
          <title type="statRep" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('statementOfResponsibilityRelatingToPublishersSeries'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><?php echo escape_dc(esc_specialchars($value)) ?></title>
        <?php endif; ?>
        <?php if (0 < strlen($value = $$resourceVar->getPropertyByName('numberingWithinPublishersSeries')->__toString())): ?>
          <num <?php if (0 < strlen($encoding = $ead->getMetadataParameter('numberingWithinPublishersSeries'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><?php echo escape_dc(esc_specialchars($value)) ?></num>
        <?php endif; ?>

      </bibseries>
    </unittitle>

  <?php endif; ?>

  <?php if (0 < strlen($value = $$resourceVar->getTitle(array('cultureFallback' => true)))): ?>
    <unittitle encodinganalog="<?php echo $ead->getMetadataParameter('unittitle') ?>"><?php echo escape_dc(esc_specialchars($value)) ?></unittitle>
  <?php endif; ?>

  <?php if (0 < strlen($value = $$resourceVar->alternateTitle)): ?>
    <unittitle type="parallel" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('parallel'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><?php echo escape_dc(esc_specialchars($value)) ?></unittitle>
  <?php endif; ?>

  <?php if (0 < strlen($value = $$resourceVar->getPropertyByName('otherTitleInformation')->__toString())): ?>
    <unittitle type="otherInfo" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('otherinfo'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><?php echo escape_dc(esc_specialchars($value)) ?></unittitle>
  <?php endif; ?>

  <?php if (0 < strlen($value = $$resourceVar->getPropertyByName('titleStatementOfResponsibility')->__toString())): ?>
    <unittitle type="statRep" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('statrep'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><?php echo escape_dc(esc_specialchars($value)) ?></unittitle>
  <?php endif; ?>

  <?php if (0 < strlen($value = $$resourceVar->getEdition(array('cultureFallback' => true)))): ?>
    <unittitle type="editionStat" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('editionstatement'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><edition><?php echo escape_dc(esc_specialchars($value)) ?></edition></unittitle>
  <?php endif; ?>

  <?php if (0 < strlen($value = $$resourceVar->getPropertyByName('editionStatementOfResponsibility')->__toString())): ?>
    <unittitle type="statRep" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('statementofresp'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><edition><?php echo escape_dc(esc_specialchars($value)) ?></edition></unittitle>
  <?php endif; ?>

  <?php $repository = null; ?>
  <?php if (0 < strlen($$resourceVar->getIdentifier())): ?>
    <?php foreach ($$resourceVar->ancestors->andSelf()->orderBy('rgt') as $item): ?>
      <?php if (isset($item->repository)): ?>
        <?php $repository = $item->repository; ?>
        <?php break; ?>
      <?php endif; ?>
    <?php endforeach; ?>
    <unitid encodinganalog="<?php echo $ead->getMetadataParameter('unitid') ?>" <?php if (isset($repository)): ?><?php if ($countrycode = $repository->getCountryCode()): ?><?php echo 'countrycode="'.$countrycode.'" ' ?><?php endif;?><?php if ($repocode = $repository->getIdentifier()): ?><?php echo 'repositorycode="'.escape_dc(esc_specialchars($repocode)).'" ' ?><?php endif; ?><?php endif; ?>><?php echo escape_dc(esc_specialchars(sfEadPlugin::getUnitidValue($$resourceVar))) ?></unitid>
  <?php endif; ?>

  <?php foreach ($$resourceVar->getProperties(null, 'alternativeIdentifiers') as $item): ?>
    <unitid type="alternative" label="<?php echo escape_dc(esc_specialchars($item->name)) ?>"><?php echo escape_dc(esc_specialchars($item->getValue(array('sourceCulture' => true)))) ?></unitid>
  <?php endforeach; ?>

  <?php if (0 < strlen($value = $$resourceVar->getPropertyByName('standardNumber')->__toString())): ?>
    <unitid type="standard" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('standardNumber'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><?php echo escape_dc(esc_specialchars($value)) ?></unitid>
  <?php endif; ?>

  <?php foreach ($$resourceVar->getDates() as $date): ?>
    <unitdate <?php if ($date->getActor() !== null || $date->getPlace() !== null): ?> <?php echo 'id="atom_'.$date->id.'_event"' ?> <?php endif; ?> <?php if ($date->typeId != QubitTerm::CREATION_ID): ?><?php if ($type = $date->getType()->__toString()): ?><?php echo 'datechar="'.strtolower($type).'" ' ?><?php endif; ?><?php else: ?><?php $type = null; ?><?php endif; ?><?php if ($startdate = $date->getStartDate()): ?><?php echo 'normal="'?><?php echo Qubit::renderDate($startdate) ?><?php if (0 < strlen($enddate = $date->getEndDate())): ?><?php echo '/'?><?php echo Qubit::renderDate($enddate) ?><?php endif; ?><?php echo '"' ?><?php endif; ?> <?php if (0 < strlen($encoding = $ead->getMetadataParameter('unitdate'.strtolower($type)))): ?>encodinganalog="<?php echo $encoding ?>"<?php elseif (0 < strlen($encoding = $ead->getMetadataParameter('unitdateDefault'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><?php echo escape_dc(esc_specialchars(Qubit::renderDateStartEnd($date->getDate(array('cultureFallback' => true)), $date->startDate, $date->endDate))) ?></unitdate>
  <?php endforeach; // dates ?>

  <?php if (0 < strlen($value = $$resourceVar->getExtentAndMedium(array('cultureFallback' => true)))): ?>
    <physdesc <?php if (0 < strlen($encoding = $ead->getMetadataParameter('extent'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>>
        <?php echo escape_dc(esc_specialchars($value)) ?>
    </physdesc>
  <?php endif; ?>

  <?php if ($value = $$resourceVar->getRepository(array('inherit' => $topLevelDid))): ?>
    <repository>
      <corpname><?php echo escape_dc(esc_specialchars($value->__toString())) ?></corpname>
      <?php if ($address = $value->getPrimaryContact()): ?>
        <address>
          <?php if (0 < strlen($addressline = $address->getStreetAddress())): ?>
            <addressline><?php echo escape_dc(esc_specialchars($addressline)) ?></addressline>
          <?php endif; ?>
          <?php if (0 < strlen($addressline = $address->getCity())): ?>
            <addressline><?php echo escape_dc(esc_specialchars($addressline)) ?></addressline>
          <?php endif; ?>
          <?php if (0 < strlen($addressline = $address->getRegion())): ?>
            <addressline><?php echo escape_dc(esc_specialchars($addressline)) ?></addressline>
          <?php endif; ?>
          <?php if (0 < strlen($addressline = $$resourceVar->getRepositoryCountry())): ?>
            <addressline><?php echo escape_dc(esc_specialchars($addressline)) ?></addressline>
          <?php endif; ?>
          <?php if (0 < strlen($addressline = $address->getPostalCode())): ?>
            <addressline><?php echo escape_dc(esc_specialchars($addressline)) ?></addressline>
          <?php endif; ?>
          <?php if (0 < strlen($addressline = $address->getTelephone())): ?>
            <addressline><?php echo __('Telephone: ').escape_dc(esc_specialchars($addressline)) ?></addressline>
          <?php endif; ?>
          <?php if (0 < strlen($addressline = $address->getFax())): ?>
            <addressline><?php echo __('Fax: ').escape_dc(esc_specialchars($addressline)) ?></addressline>
          <?php endif; ?>
          <?php if (0 < strlen($addressline = $address->getEmail())): ?>
            <addressline><?php echo __('Email: ').escape_dc(esc_specialchars($addressline)) ?></addressline>
          <?php endif; ?>
          <?php if (0 < strlen($addressline = $address->getWebsite())): ?>
            <addressline><?php echo escape_dc(esc_specialchars($addressline)) ?></addressline>
          <?php endif; ?>
        </address>
      <?php endif; ?>
    </repository>
  <?php endif; ?>

  <?php if (0 < count($$resourceVar->language) || 0 < count($$resourceVar->script) || 0 < count($$resourceVar->getNotesByType(array('noteTypeId' => QubitTerm::LANGUAGE_NOTE_ID))->offsetGet(0))): ?>
    <langmaterial encodinganalog="<?php echo $ead->getMetadataParameter('langmaterial') ?>">
    <?php foreach ($$resourceVar->language as $languageCode): ?>
      <language langcode="<?php echo strtolower($iso639convertor->getID2($languageCode)) ?>"><?php echo format_language($languageCode) ?></language>
    <?php endforeach; ?>
    <?php foreach ($$resourceVar->script as $scriptCode): ?>
      <language scriptcode="<?php echo $scriptCode ?>"><?php echo format_script($scriptCode) ?></language>
    <?php endforeach; ?>
    <?php if (0 < count($notes = $$resourceVar->getNotesByType(array('noteTypeId' => QubitTerm::LANGUAGE_NOTE_ID)))): ?>
      <?php echo escape_dc(esc_specialchars($notes[0]->getContent(array('cultureFallback' => true)))) ?>
    <?php endif; ?>
    </langmaterial>
  <?php endif; ?>

  <?php if ($$resourceVar->sources): ?>
    <note type="sourcesDescription"><p><?php echo escape_dc(esc_specialchars($$resourceVar->sources)) ?></p></note>
  <?php endif; ?>

  <?php if (0 < count($notes = $$resourceVar->getNotesByType(array('noteTypeId' => QubitTerm::GENERAL_NOTE_ID)))): ?>
    <?php foreach ($notes as $note): ?>
      <note type="generalNote" <?php if (0 < strlen($encoding = $ead->getMetadataParameter('generalNote'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>>
        <p><?php echo escape_dc(esc_specialchars($note->getContent(array('cultureFallback' => true)))) ?></p>
      </note>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (0 < strlen($value = $$resourceVar->getPropertyByName('statementOfScaleCartographic')->__toString())): ?>
    <materialspec type='cartographic'  <?php if (0 < strlen($encoding = $ead->getMetadataParameter('cartographic'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><?php echo escape_dc(esc_specialchars($value)) ?></materialspec>
  <?php endif; ?>

  <?php if (0 < strlen($value = $$resourceVar->getPropertyByName('statementOfProjection')->__toString())): ?>
     <materialspec type='projection' <?php if (0 < strlen($encoding = $ead->getMetadataParameter('projection'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><?php echo escape_dc(esc_specialchars($value)) ?></materialspec>
  <?php endif; ?>

  <?php if (0 < strlen($value = $$resourceVar->getPropertyByName('statementOfCoordinates')->__toString())): ?>
    <materialspec type='coordinates' <?php if (0 < strlen($encoding = $ead->getMetadataParameter('coordinates'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><?php echo escape_dc(esc_specialchars($value)) ?></materialspec>
  <?php endif; ?>

  <?php if (0 < strlen($value = $$resourceVar->getPropertyByName('statementOfScaleArchitectural')->__toString())): ?>
    <materialspec type='architectural' <?php if (0 < strlen($encoding = $ead->getMetadataParameter('architectural'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><?php echo escape_dc(esc_specialchars($value)) ?></materialspec>
  <?php endif; ?>

  <?php if (0 < strlen($value = $$resourceVar->getPropertyByName('issuingJurisdictionAndDenomination')->__toString())): ?>
    <materialspec type='philatelic' <?php if (0 < strlen($encoding = $ead->getMetadataParameter('philatelic'))): ?>encodinganalog="<?php echo $encoding ?>"<?php endif; ?>><?php echo escape_dc(esc_specialchars($value)) ?></materialspec>
  <?php endif; ?>

  <?php if (null !== $digitalObject = $$resourceVar->digitalObjects[0]): ?>
    <?php if (QubitTerm::OFFLINE_ID != $digitalObject->usageId): ?>
      <?php if (QubitAcl::check($$resourceVar, 'readMaster') && 0 < strlen($url = QubitTerm::EXTERNAL_URI_ID == $digitalObject->usageId ? $digitalObject->getPath() : $ead->getAssetPath($digitalObject))): ?>
        <dao linktype="simple" href="<?php echo $url ?>" role="master" actuate="onrequest" show="embed"/>
      <?php elseif (null !== $digitalObject->reference && QubitAcl::check($$resourceVar, 'readReference') && 0 < strlen($url = $ead->getAssetPath($digitalObject, true))): ?>
        <dao linktype="simple" href="<?php echo $url ?>" role="reference" actuate="onrequest" show="embed"/>
      <?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>

  <?php if (0 < count($events)): ?>
    <origination encodinganalog="<?php echo $ead->getMetadataParameter('origination') ?>">
      <?php foreach($events as $date): ?>
        <?php if ($type = $date->actor->getEntityTypeId()): ?>
          <?php if (QubitTerm::PERSON_ID == $type): ?>
            <persname <?php echo 'id="atom_'.$date->id.'_actor"' ?>><?php echo escape_dc(esc_specialchars($date->actor->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?></persname>
          <?php endif; ?>
          <?php if (QubitTerm::FAMILY_ID == $type): ?>
            <famname <?php echo 'id="atom_'.$date->id.'_actor"' ?>><?php echo escape_dc(esc_specialchars($date->actor->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?></famname>
          <?php endif; ?>
          <?php if (QubitTerm::CORPORATE_BODY_ID == $type): ?>
            <corpname <?php echo 'id="atom_'.$date->id.'_actor"' ?>><?php echo escape_dc(esc_specialchars($date->actor->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?></corpname>
          <?php endif; ?>
        <?php else: ?>
          <name <?php echo 'id="atom_'.$date->id.'_actor"' ?>><?php echo escape_dc(esc_specialchars($date->actor->getAuthorizedFormOfName(array('cultureFallback' => true)))) ?></name>
        <?php endif; ?>
      <?php endforeach; ?>
    </origination>
  <?php endif; ?>

</did>
