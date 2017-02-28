<?php if ($recordsCount == 0): ?>
  <error code="noRecordsMatch">The combination of the values of the from, until, set and metadataPrefix arguments results in an empty list.</error>
<?php else: ?>
  <ListRecords>
  <?php foreach ($publishedRecords as $record): ?>
    <?php if (QubitAcl::check($record, 'read') && array_search($record->getOaiIdentifier(), $identifiersWithMissingCacheFiles) === false): ?>
      <record>
        <header>
          <identifier><?php echo $record->getOaiIdentifier() ?></identifier>
          <datestamp><?php echo QubitOai::getDate($record->getUpdatedAt())?></datestamp>
          <setSpec><?php echo $record->getCollectionRoot()->getOaiIdentifier()?></setSpec>
        </header>
        <metadata>
          <?php if ($metadataPrefix == 'oai_dc' && !arOaiPluginComponent::cachedMetadataExists($record, $metadataPrefix)): ?>
            <?php echo get_component('sfDcPlugin', 'dc', array('resource' => $record)) ?>
          <?php else: ?>
            <?php arOaiPluginComponent::includeCachedMetadata($record, $metadataPrefix) ?>
          <?php endif; ?>
        </metadata>
        <?php include('_about.xml.php') ?>
      </record>
    <?php endif; ?>
  <?php endforeach; ?>
  <?php foreach ($identifiersWithMissingCacheFiles as $identifier): ?>
    <error code="cannotDisseminateFormat">The metadata format identified by the value given for the metadataPrefix argument is available for item <?php echo $identifier; ?>.</error>
  <?php endforeach; ?>
  <?php if ($remaining > 0): ?>
    <resumptionToken><?php echo $resumptionToken ?></resumptionToken>
  <?php endif; ?>
  </ListRecords>
<?php endif; ?>
