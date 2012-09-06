  <GetRecord>
   <record>
    <header>
      <identifier><?php echo $informationObject->getOaiIdentifier() ?></identifier>
      <datestamp><?php echo QubitOai::getDate($informationObject->getUpdatedAt())?></datestamp>
      <setSpec><?php echo QubitOai::getSetSpec($informationObject->getLft(), $collectionsTable)?></setSpec>
    </header>
    <metadata>
      <oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
          <?php echo get_component('informationobject', 'dublinCoreElements') ?>
      </oai_dc:dc>
    </metadata>
  </record>
 </GetRecord>
