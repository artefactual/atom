<?php if (0 == $recordsCount) { ?>
  <error code="noRecordsMatch">The combination of the values of the from, until, set and metadataPrefix arguments results in an empty list.</error>
<?php } else { ?>
  <ListIdentifiers>
<?php foreach ($publishedRecords as $record) { ?>
    <header>
      <identifier><?php echo $record->getOaiIdentifier(); ?></identifier>
      <datestamp><?php echo QubitOai::getDate($record->getUpdatedAt()); ?></datestamp>
      <setSpec><?php echo $record->getCollectionRoot()->getOaiIdentifier(); ?></setSpec>
    </header>
<?php } ?>
  <?php if ($remaining > 0) { ?>
    <resumptionToken><?php echo $resumptionToken; ?></resumptionToken>
  <?php }?>
  </ListIdentifiers>
<?php }?>
