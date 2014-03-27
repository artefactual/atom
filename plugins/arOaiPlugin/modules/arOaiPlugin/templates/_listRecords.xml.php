<?php if($recordsCount == 0):?>
  <error code="noRecordsMatch">The combination of the values of the from, until, set and metadataPrefix arguments results in an empty list.</error>
<?php else:?>
  <ListRecords>
<?php foreach($publishedRecords as $record): ?>
<?php $requestname->setAttribute('informationObject', $record) ?>
   <record>
    <header>
      <identifier><?php echo $record->getOaiIdentifier() ?></identifier>
      <datestamp><?php echo QubitOai::getDate($record->getUpdatedAt())?></datestamp>
      <setSpec><?php echo QubitOai::getSetSpec($record->getLft(), $collectionsTable)?></setSpec>
    </header>
    <metadata>
      <?php echo get_component('sfDcPlugin', 'dc', array('resource' => $record)) ?>
    </metadata>
   </record>
<?php endforeach ?>
  <?php if($remaining > 0):?>
    <resumptionToken><?php echo $resumptionToken?></resumptionToken>
  <?php endif?>
  </ListRecords>
<?php endif?>
