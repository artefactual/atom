<?php echo '<?xml version="1.0" encoding="'.sfConfig::get('sf_charset', 'UTF-8')."\" ?>\n" ?>

<mods xmlns="http://www.loc.gov/mods/v3"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.loc.gov/standards/mods/v3/mods-3-3.xsd">

  <titleInfo>
    <title><?php echo esc_specialchars($resource->title) ?></title>
  </titleInfo>

  <?php if (0 < count($mods->name)): ?>
    <?php foreach ($mods->name as $item): ?>
      <name<?php if ($item->actor->entityType): ?> type="<?php echo $item->actor->entityType ?>"<?php endif; ?>>
        <namePart><?php echo esc_specialchars($item->actor) ?></namePart>
        <role><?php echo $item->type->getRole() ?></role>
      </name>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (0 < count($mods->typeOfResource)): ?>
    <?php foreach ($mods->typeOfResource as $item): ?>
      <typeOfResource><?php echo esc_specialchars($item->term) ?></typeOfResource>
    <?php endforeach; ?>
  <?php endif; ?>

  <originInfo>
    <?php if (0 < count($resource->getDates())): ?>
      <?php foreach ($resource->getDates() as $item): ?>

        <place><?php echo $item->getPlace() ?></place>

        <?php switch ($item->typeId): case QubitTerm::CREATION_ID: ?>
            <dateCreated><?php echo $item->getDate(array('cultureFallback' => true)) ?></dateCreated>
            <?php break ?>
          <?php case QubitTerm::PUBLICATION_ID: ?>
            <dateIssued><?php echo $item->getDate(array('cultureFallback' => true)) ?></dateIssued>
            <?php break ?>
          <?php default: ?>
            <dateOther><?php echo $item->getDate(array('cultureFallback' => true)) ?> (<?php echo $item->type ?>)</dateOther>
        <?php endswitch; ?>

      <?php endforeach; ?>
    <?php endif; ?>
  </originInfo>

  <?php if (0 < count($resource->language)): ?>
    <?php foreach ($resource->language as $code): ?>
      <language><?php echo format_language($code) ?></language>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (isset($reosurce->digitalObjects[0]->mimeType)): ?>
    <physicalDescription>
      <internetMediaType><?php echo $resource->digitalObjects[0]->mimeType ?></internetMediaType>
    </physicalDescription>
  <?php endif; ?>

  <?php if (0 < count($resource->getSubjectAccessPoints())): ?>
    <?php foreach ($resource->getSubjectAccessPoints() as $item): ?>
      <subject><topic><?php echo esc_specialchars($item->term) ?></topic></subject>
    <?php endforeach; ?>
  <?php endif; ?>

  <identifier type="local"><?php echo esc_specialchars($mods->identifier) ?></identifier>

  <?php if ($extentAndMedium = $resource->getExtentAndMedium(array('cultureFallback' => true))): ?>
    <physicalDescription><extent><?php echo esc_specialchars($extentAndMedium) ?></extent></physicalDescription>
  <?php endif; ?>

  <?php if ($scopeAndContent = $resource->getScopeAndContent(array('cultureFallback' => true))): ?>
  <abstract><?php echo esc_specialchars($scopeAndContent) ?></abstract>
  <?php endif; ?>

  <?php if (0 < count($materialTypes = $mods->materialTypes)): ?>
    <?php foreach ($materialTypes as $materialType): ?>
      <note type="gmd"><?php echo esc_specialchars($materialType) ?></note>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if ($locationOfOriginals = $resource->getLocationOfOriginals(array('cultureFallback' => true))): ?>
    <note type="originalLocation"><?php echo esc_specialchars($locationOfOriginals) ?></note>
  <?php endif; ?>

  <?php if ($otherFormats = $resource->getLocationOfCopies(array('cultureFallback' => true))): ?>
    <note type="otherFormats"><?php echo esc_specialchars($otherFormats) ?></note>
  <?php endif; ?>

  <?php if (count($generalNotes = $mods->generalNotes)): ?>
    <?php foreach ($generalNotes as $generalNote): ?>
      <note><?php echo esc_specialchars($generalNote) ?></note>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (count($languageNotes = $mods->languageNotes)): ?>
    <?php foreach ($languageNotes as $languageNote): ?>
      <note type="language"><?php echo esc_specialchars($languageNote) ?></note>
    <?php endforeach; ?>
  <?php endif; ?>

  <location>

    <?php if (isset($resource->digitalObjects[0])): ?>
      <url usage="primary display">http://<?php echo $sf_request->getHost().$sf_request->getRelativeUrlRoot().$resource->digitalObjects[0]->getFullPath() ?></url>
    <?php endif; ?>

    <?php if (0 < count($mods->physicalLocation)): ?>
      <?php foreach ($mods->physicalLocation as $item): ?>
        <physicalLocation><?php echo esc_specialchars($item) ?></physicalLocation>
      <?php endforeach; ?>
    <?php endif; ?>

  </location>

  <?php $places = $resource->getPlaceAccessPoints(); ?>

  <?php if (count($places)): ?>
    <?php foreach ($places as $place): ?>
      <subject><geographic><?php echo escape_dc(esc_specialchars($place->getTerm())) ?></geographic></subject>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (QubitInformationObject::ROOT_ID != $resource->parentId): ?>
    <?php $parent = QubitInformationObject::getById($resource->parentId); ?>
    <relatedItem key id="<?php echo $parent->identifier ?>" type="constituent"><?php $parentMods = new sfModsPlugin($parent); echo esc_specialchars($parentMods) ?></relatedItem>
  <?php endif; ?>

  <?php if (0 < count($resource->getChildren())): ?>
    <?php foreach ($resource->getChildren() as $item): ?>
      <relatedItem id="<?php echo $item->identifier ?>" type="constituent"><?php $mods = new sfModsPlugin($item); echo esc_specialchars($mods) ?></relatedItem>
    <?php endforeach; ?>
  <?php endif; ?>

  <accessCondition><?php echo esc_specialchars($resource->getAccessConditions(array('cultureFallback' => true))) ?></accessCondition>

  <recordInfo>
    <recordCreationDate><?php echo $resource->createdAt ?></recordCreationDate>
    <recordChangeDate><?php echo $resource->updatedAt ?></recordChangeDate>
  </recordInfo>

</mods>
