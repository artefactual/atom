  <GetRecord>
   <record>
    <header>
      <identifier><?php echo $informationObject->getOaiIdentifier() ?></identifier>
      <datestamp><?php echo QubitOai::getDate($informationObject->getUpdatedAt())?></datestamp>
      <setSpec><?php echo QubitOai::getSetSpec($informationObject->getLft(), $collectionsTable)?></setSpec>
    </header>
    <metadata>
      <?php echo get_component('sfDcPlugin', 'dc', array('resource' => $record)) ?>
    </metadata>
  </record>
 </GetRecord>
