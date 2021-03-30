<?php echo '<?xml version="1.0" encoding="'.sfConfig::get('sf_charset', 'UTF-8')."\" ?>\n"; ?>

<mods version="3.5"
    xmlns="http://www.loc.gov/mods/v3"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.loc.gov/standards/mods/v3/mods-3-5.xsd">

  <titleInfo>
    <title><?php echo esc_specialchars($resource->title); ?></title>
  </titleInfo>

  <?php if (0 < count($mods->name)) { ?>
    <?php foreach ($mods->name as $item) { ?>
      <name<?php if ($item->actor->entityType) { ?> type="<?php echo $item->actor->entityType; ?>"<?php } ?>>
        <namePart><?php echo esc_specialchars($item->actor); ?></namePart>
        <role><roleTerm><?php echo $item->type->getRole(); ?></roleTerm></role>
      </name>
    <?php } ?>
  <?php } ?>

  <?php if (0 < count($mods->typeOfResourceForXml)) { ?>
    <?php foreach ($mods->typeOfResourceForXml as $item) { ?>
      <typeOfResource><?php echo esc_specialchars($item); ?></typeOfResource>
    <?php } ?>
  <?php } ?>

  <?php if (0 < count($genres = $mods->genres)) { ?>
    <?php foreach ($genres as $genre) { ?>
      <genre><?php echo esc_specialchars($genre); ?></genre>
    <?php } ?>
  <?php } ?>

  <?php if (0 < count($resource->getDates())) { ?>
    <originInfo>
      <?php foreach ($resource->getDates() as $item) { ?>

        <place><placeTerm><?php echo $item->getPlace(); ?></placeTerm></place>

        <?php $dateTagName = $mods->getDateTagNameForEventType($item->typeId); ?>
        <<?php echo $dateTagName; ?><?php if ('dateOther' == $dateTagName) { ?> type="Broadcasting"<?php } ?>><?php echo $item->getDate(['cultureFallback' => true]); ?></<?php echo $dateTagName; ?>>
        <?php if (!empty($item->startDate)) { ?>
          <<?php echo $dateTagName; ?><?php if ('dateOther' == $dateTagName) { ?> type="Broadcasting"<?php } ?> point="start"><?php echo $item->startDate; ?></<?php echo $dateTagName; ?>>
        <?php } ?>
        <?php if (!empty($item->endDate)) { ?>
          <<?php echo $dateTagName; ?><?php if ('dateOther' == $dateTagName) { ?> type="Broadcasting"<?php } ?> point="end"><?php echo $item->endDate; ?></<?php echo $dateTagName; ?>>
        <?php } ?>

      <?php } ?>
    </originInfo>
  <?php } ?>

  <?php if (0 < count($resource->language)) { ?>
    <?php foreach ($resource->language as $code) { ?>
      <language><?php echo format_language($code); ?></language>
    <?php } ?>
  <?php } ?>

  <?php if (isset($resource->digitalObjectsRelatedByobjectId[0]->mimeType)) { ?>
    <physicalDescription>
      <internetMediaType><?php echo $resource->digitalObjectsRelatedByobjectId[0]->mimeType; ?></internetMediaType>
    </physicalDescription>
  <?php } ?>

  <identifier type="local"><?php echo esc_specialchars($mods->identifier); ?></identifier>
  <identifier type="uri"><?php echo esc_specialchars($mods->uri); ?></identifier>

  <?php if ($extentAndMedium = $resource->getCleanExtentAndMedium(['cultureFallback' => true])) { ?>
    <physicalDescription><extent><?php echo esc_specialchars($extentAndMedium); ?></extent></physicalDescription>
  <?php } ?>

  <?php if ($scopeAndContent = $resource->getScopeAndContent(['cultureFallback' => true])) { ?>
    <abstract type="description"><?php echo esc_specialchars($scopeAndContent); ?></abstract>
  <?php } ?>

  <?php if ($locationOfOriginals = $resource->getLocationOfOriginals(['cultureFallback' => true])) { ?>
    <note type="originalLocation"><?php echo esc_specialchars($locationOfOriginals); ?></note>
  <?php } ?>

  <?php if ($otherFormats = $resource->getLocationOfCopies(['cultureFallback' => true])) { ?>
    <note type="otherFormats"><?php echo esc_specialchars($otherFormats); ?></note>
  <?php } ?>

  <?php if (count($generalNotes = $mods->generalNotes)) { ?>
    <?php foreach ($generalNotes as $generalNote) { ?>
      <note><?php echo esc_specialchars($generalNote); ?></note>
    <?php } ?>
  <?php } ?>

  <?php if (count($generalNotes = $mods->radGeneralNotes)) { ?>
    <?php foreach ($generalNotes as $generalNote) { ?>
      <note type="genNote"><?php echo esc_specialchars($generalNote); ?></note>
    <?php } ?>
  <?php } ?>

  <?php if (count($alphanumericNotes = $mods->alphanumericNotes)) { ?>
    <?php foreach ($alphanumericNotes as $alphanumericNote) { ?>
      <note type="numbering"><?php echo esc_specialchars($alphanumericNote); ?></note>
    <?php } ?>
  <?php } ?>

  <?php if (count($languageNotes = $mods->languageNotes)) { ?>
    <?php foreach ($languageNotes as $languageNote) { ?>
      <note type="language"><?php echo esc_specialchars($languageNote); ?></note>
    <?php } ?>
  <?php } ?>

  <?php if ($mods->hasRightsAccess) { ?>
    <accessCondition type="restriction on access"></accessCondition>
  <?php } ?>

  <?php if ($mods->hasRightsReplicate) { ?>
    <accessCondition type="use and reproduction"></accessCondition>
  <?php } ?>

  <?php if (isset($resource->digitalObjectsRelatedByobjectId[0])) { ?>
    <location>
      <holdingSimple>
        <copyInformation>
          <electronicLocator><?php echo esc_specialchars($resource->getDigitalObjectPublicUrl()); ?></electronicLocator>
        </copyInformation>
      </holdingSimple>
    </location>
  <?php } ?>

  <?php if (isset($resource->repository) && $resource->repository->authorizedFormOfName) { ?>
    <location>
      <physicalLocation><?php echo esc_specialchars($resource->repository->authorizedFormOfName); ?></physicalLocation>
    </location>
  <?php } ?>

  <?php if (0 < count($resource->getSubjectAccessPoints())) { ?>
    <?php foreach ($resource->getSubjectAccessPoints() as $item) { ?>
      <subject><topic><?php echo esc_specialchars($item->term); ?></topic></subject>
    <?php } ?>
  <?php } ?>

  <?php if (0 < count($resource->getPlaceAccessPoints())) { ?>
    <?php foreach ($resource->getPlaceAccessPoints() as $item) { ?>
      <subject><geographic><?php echo escape_dc(esc_specialchars($item->getTerm())); ?></geographic></subject>
    <?php } ?>
  <?php } ?>

  <?php foreach ($resource->relationsRelatedBysubjectId as $item) { ?>
    <?php if (isset($item->type) && QubitTerm::NAME_ACCESS_POINT_ID == $item->type->id) { ?>
      <subject>
        <?php if (QubitTerm::PERSON_ID == $item->object->entityTypeId) { ?>
          <name type="personal"><?php echo escape_dc(esc_specialchars($item->object)); ?></name>
        <?php } elseif (QubitTerm::FAMILY_ID == $item->object->entityTypeId) { ?>
          <name type="family"><?php echo escape_dc(esc_specialchars($item->object)); ?></name>
        <?php } elseif (QubitTerm::CORPORATE_BODY_ID == $item->object->entityTypeId) { ?>
          <name type="corporate"><?php echo escape_dc(esc_specialchars($item->object)); ?></name>
        <?php } else { ?>
          <name><?php echo escape_dc(esc_specialchars($item->object)); ?></name>
        <?php } ?>
      </subject>
    <?php } ?>
  <?php } ?>

  <?php if (QubitInformationObject::ROOT_ID != $resource->parentId) { ?>
    <?php $parent = QubitInformationObject::getById($resource->parentId); ?>
    <relatedItem ID="<?php echo $parent->identifier; ?>" type="host"><titleInfo><title><?php echo esc_specialchars($parent->title); ?></title></titleInfo></relatedItem>
  <?php } ?>

  <?php if (0 < count($resource->getChildren())) { ?>
    <?php foreach ($resource->getChildren() as $child) { ?>
      <relatedItem ID="<?php echo $child->identifier; ?>" type="constituent"><titleInfo><title><?php echo esc_specialchars($child->title); ?></title></titleInfo></relatedItem>
    <?php } ?>
  <?php } ?>

  <accessCondition type="restriction on access"><?php echo esc_specialchars($resource->getAccessConditions(['cultureFallback' => true])); ?></accessCondition>
  <accessCondition><?php echo esc_specialchars($resource->getReproductionConditions(['cultureFallback' => true])); ?></accessCondition>

  <recordInfo>
    <recordCreationDate><?php echo $resource->createdAt; ?></recordCreationDate>
    <recordChangeDate><?php echo $resource->updatedAt; ?></recordChangeDate>
  </recordInfo>

</mods>
