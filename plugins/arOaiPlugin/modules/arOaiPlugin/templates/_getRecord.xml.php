  <GetRecord>
    <record>
      <header>
        <identifier><?php echo $informationObject->getOaiIdentifier() ?></identifier>
        <datestamp><?php echo QubitOai::getDate($informationObject->getUpdatedAt())?></datestamp>
        <setSpec><?php echo QubitOai::getSetSpec($informationObject->getLft(), $collectionsTable)?></setSpec>
      </header>
      <metadata>
        <?php echo get_component('sfDcPlugin', 'dc', array('resource' => $informationObject)) ?>
      </metadata>
      <?php if (count($informationObject->digitalObjects)): ?>
        <about>
          <feed xmlns="http://www.w3.org/2005/Atom">
            <?php foreach($informationObject->digitalObjects as $digitalObject): ?>
              <?php if ($digitalObject->usageId == QubitTerm::MASTER_ID && QubitAcl::check($informationObject, 'readMaster')): ?>
                <?php $digitalObjectUrl = (string)QubitSetting::getByName('siteBaseUrl') . $digitalObject->path . $digitalObject->name ?>
                <?php echo include_partial('getRecordAtomFeedEntry', array('object' => $digitalObject, 'url' => $digitalObjectUrl, 'usage' => 'master')) ?>
              <?php elseif($digitalObject->usageId == QubitTerm::EXTERNAL_URI_ID): ?>
                <?php echo include_partial('getRecordAtomFeedEntry', array('object' => $digitalObject, 'url' => $digitalObject->path, 'usage' => 'external')) ?>
              <?php endif; ?>

              <?php $thumbnail = $digitalObject->getChildByUsageId(QubitTerm::THUMBNAIL_ID) ?>
              <?php $thumbnailUrl = (string)QubitSetting::getByName('siteBaseUrl') . $thumbnail->path . $thumbnail->name ?>
              <?php echo include_partial('getRecordAtomFeedEntry', array('object' => $thumbnail, 'url' => $thumbnailUrl, 'usage' => 'thumbnail')) ?>
            <?php endforeach; ?>
          </feed>
        </about>
      <?php endif; ?>
    </record>
  </GetRecord>
