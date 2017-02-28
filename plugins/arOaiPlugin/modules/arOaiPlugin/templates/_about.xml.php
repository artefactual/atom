      <?php if (count($record->digitalObjects)): ?>
        <about>
          <feed xmlns="http://www.w3.org/2005/Atom">
            <?php foreach ($record->digitalObjects as $digitalObject): ?>
              <?php if ($digitalObject->usageId == QubitTerm::OFFLINE_ID): ?>
                <?php continue; ?>
              <?php endif; ?>
              <?php if ($digitalObject->usageId == QubitTerm::MASTER_ID && QubitAcl::check($record, 'readMaster')): ?>
                <?php $digitalObjectUrl = (string)QubitSetting::getByName('siteBaseUrl') . $digitalObject->path . $digitalObject->name ?>
                <?php echo include_partial('getRecordAtomFeedEntry', array('object' => $digitalObject, 'url' => $digitalObjectUrl, 'usage' => 'master')) ?>
              <?php elseif ($digitalObject->usageId == QubitTerm::EXTERNAL_URI_ID): ?>
                <?php echo include_partial('getRecordAtomFeedEntry', array('object' => $digitalObject, 'url' => $digitalObject->path, 'usage' => 'external')) ?>
              <?php endif; ?>

              <?php $thumbnail = $digitalObject->getChildByUsageId(QubitTerm::THUMBNAIL_ID) ?>
              <?php $thumbnailUrl = (string)QubitSetting::getByName('siteBaseUrl') . $thumbnail->path . $thumbnail->name ?>
              <?php echo include_partial('getRecordAtomFeedEntry', array('object' => $thumbnail, 'url' => $thumbnailUrl, 'usage' => 'thumbnail')) ?>
            <?php endforeach; ?>
          </feed>
        </about>
      <?php endif; ?>
