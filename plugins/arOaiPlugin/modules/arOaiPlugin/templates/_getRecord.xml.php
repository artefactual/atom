<?php if (QubitAcl::check($informationObject, 'read')): ?>
  <GetRecord>
    <record>
      <header>
        <identifier><?php echo $informationObject->getOaiIdentifier() ?></identifier>
        <datestamp><?php echo QubitOai::getDate($informationObject->getUpdatedAt())?></datestamp>
        <setSpec><?php echo QubitOai::getSetSpec($informationObject, $oaiSets)?></setSpec>
      </header>
      <metadata>
        <?php echo get_component('sfDcPlugin', 'dc', array('resource' => $informationObject)) ?>
      </metadata>
      <?php if (count($informationObject->digitalObjects)): ?>
        <?php $record = $informationObject ?>
        <?php include('_about.xml.php') ?>  
      <?php endif; ?>
    </record>
  </GetRecord>
<?php else: ?>
  <error code="noRecordsMatch">The combination of the values of the from, until, set and metadataPrefix arguments results in an empty list.</error>
<?php endif; ?>
