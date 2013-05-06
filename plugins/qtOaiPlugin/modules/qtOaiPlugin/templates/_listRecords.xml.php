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
      <oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
          <?php echo get_partial('sfDcPlugin/dc', array('dc' => $dc, 'resource' => $record)) ?>
      </oai_dc:dc>
    </metadata>
   </record>
<?php endforeach ?>
  </ListRecords>
<?php endif?>
