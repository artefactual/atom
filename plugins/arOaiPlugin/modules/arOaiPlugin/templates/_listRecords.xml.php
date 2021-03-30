<?php if (0 == $recordsCount) { ?>
  <error code="noRecordsMatch">The combination of the values of the from, until, set and metadataPrefix arguments results in an empty list.</error>
<?php } else { ?>
  <ListRecords>
  <?php foreach ($publishedRecords as $record) { ?>
    <?php if (QubitAcl::check($record, 'read') && false === array_search($record->getOaiIdentifier(), $identifiersWithMissingCacheFiles)) { ?>
      <record>
        <header>
          <identifier><?php echo $record->getOaiIdentifier(); ?></identifier>
          <datestamp><?php echo QubitOai::getDate($record->getUpdatedAt()); ?></datestamp>
          <setSpec><?php echo $record->getCollectionRoot()->getOaiIdentifier(); ?></setSpec>
        </header>
        <metadata>
          <?php if ('oai_dc' == $metadataPrefix && !arOaiPluginComponent::checkDisplayCachedMetadata($record, $metadataPrefix)) { ?>
            <?php echo get_component('sfDcPlugin', 'dc', ['resource' => $record]); ?>
          <?php } else { ?>
            <?php arOaiPluginComponent::includeCachedMetadata($record, $metadataPrefix); ?>
          <?php } ?>
        </metadata>
        <?php include '_about.xml.php'; ?>
      </record>
    <?php } ?>
  <?php } ?>
  <?php foreach ($identifiersWithMissingCacheFiles as $identifier) { ?>
    <error code="cannotDisseminateFormat">The metadata format identified by the value given for the metadataPrefix argument is available for item <?php echo $identifier; ?>.</error>
  <?php } ?>
  <?php if ($remaining > 0) { ?>
    <resumptionToken><?php echo $resumptionToken; ?></resumptionToken>
  <?php } ?>
  </ListRecords>
<?php } ?>
