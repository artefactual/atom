<?php if($recordsCount == 0):?>
  <error code="noRecordsMatch">The combination of the values of the from, until, set and metadataPrefix arguments results in an empty list.</error>
<?php else:?>
  <ListRecords>
<?php foreach($publishedRecords as $record): ?>
<?php $dc = new sfDcPlugin($record) ?>
<?php $requestname->setAttribute('informationObject', $record) ?>
   <record>
    <header>
      <identifier><?php echo $record->getOaiIdentifier() ?></identifier>
      <datestamp><?php echo QubitOai::getDate($record->getUpdatedAt())?></datestamp>
      <setSpec><?php echo QubitOai::getSetSpec($record->getLft(), $collectionsTable)?></setSpec>
    </header>
    <metadata>
      <?php echo get_partial('sfDcPlugin/dc', array('dc' => $dc, 'resource' => $record)) ?>
    </metadata>
   </record>
<?php endforeach ?>
  </ListRecords>
<?php endif?>
