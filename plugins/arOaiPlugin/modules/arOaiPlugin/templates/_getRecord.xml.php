<?php if (QubitAcl::check($record, 'read')): ?>
  <GetRecord>
    <record>
      <header>
        <identifier><?php echo $record->getOaiIdentifier() ?></identifier>
        <datestamp><?php echo QubitOai::getDate($record->getUpdatedAt())?></datestamp>
        <setSpec><?php echo $record->getCollectionRoot()->getOaiIdentifier()?></setSpec>
      </header>
      <metadata>
        <?php echo get_component('sfDcPlugin', 'dc', array('resource' => $record)) ?>
      </metadata>
      <?php if (count($record->digitalObjects)): ?>
        <?php include('_about.xml.php') ?>  
      <?php endif; ?>
    </record>
  </GetRecord>
<?php endif; ?>
