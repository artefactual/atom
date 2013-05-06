  <GetRecord>
   <record>
    <header>
      <identifier><?php echo $informationObject->getOaiIdentifier() ?></identifier>
      <datestamp><?php echo QubitOai::getDate($informationObject->getUpdatedAt())?></datestamp>
      <setSpec><?php echo QubitOai::getSetSpec($informationObject->getLft(), $collectionsTable)?></setSpec>
    </header>
    <metadata>
      <?php $dc = new sfDcPlugin($informationObject) ?>
      <?php echo get_partial('sfDcPlugin/dc', array('dc' => $dc, 'resource' => $informationObject)) ?>
    </metadata>
  </record>
 </GetRecord>
