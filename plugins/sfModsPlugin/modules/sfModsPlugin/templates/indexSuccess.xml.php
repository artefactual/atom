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
      <name type="<?php echo $item->actor->entityType ?>">
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

  <?php if (0 < count($resource->getDates())): ?>
    <originInfo>
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
    </originInfo>
  <?php endif; ?>

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
      <subject><?php echo esc_specialchars($item->term) ?></subject>
    <?php endforeach; ?>
  <?php endif; ?>

  <identifier><?php echo esc_specialchars($mods->identifier) ?></identifier>

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
