<?php if ($recordsCount == 0): ?>
  <error code="noRecordsMatch">The combination of the values of the from, until, set and metadataPrefix arguments results in an empty list.</error>
<?php else: ?>
  <ListRecords>
  <?php foreach ($publishedRecords as $record): ?>
    <?php if (QubitAcl::check($record, 'read')): ?>
      <record>
        <header>
          <identifier><?php echo $record->getOaiIdentifier() ?></identifier>
          <datestamp><?php echo QubitOai::getDate($record->getUpdatedAt())?></datestamp>
          <setSpec><?php echo $record->getCollectionRoot()->getOaiIdentifier()?></setSpec>
        </header>
        <metadata>
          <?php arOaiPluginComponent::includeCachedMetadata($record->id, $metadataPrefix) ?>
        </metadata>
        <?php include('_about.xml.php') ?>
      </record>
    <?php endif; ?>
  <?php endforeach; ?>
  <?php if ($remaining > 0): ?>
    <resumptionToken><?php echo $resumptionToken ?></resumptionToken>
  <?php endif; ?>
  </ListRecords>
<?php endif; ?>
