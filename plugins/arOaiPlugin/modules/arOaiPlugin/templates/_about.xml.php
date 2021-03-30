      <?php if (count($record->digitalObjectsRelatedByobjectId)) { ?>
        <about>
          <feed xmlns="http://www.w3.org/2005/Atom">
            <?php foreach ($record->digitalObjectsRelatedByobjectId as $digitalObject) { ?>
              <?php if (QubitTerm::OFFLINE_ID == $digitalObject->usageId) { ?>
                <?php continue; ?>
              <?php } ?>
              <?php if (QubitTerm::MASTER_ID == $digitalObject->usageId && QubitAcl::check($record, 'readMaster')) { ?>
                <?php $digitalObjectUrl = (string) QubitSetting::getByName('siteBaseUrl').$digitalObject->path.$digitalObject->name; ?>
                <?php echo include_partial('getRecordAtomFeedEntry', ['object' => $digitalObject, 'url' => $digitalObjectUrl, 'usage' => 'master']); ?>
              <?php } elseif (QubitTerm::EXTERNAL_URI_ID == $digitalObject->usageId) { ?>
                <?php echo include_partial('getRecordAtomFeedEntry', ['object' => $digitalObject, 'url' => $digitalObject->path, 'usage' => 'external']); ?>
              <?php } ?>

              <?php $thumbnail = $digitalObject->getChildByUsageId(QubitTerm::THUMBNAIL_ID); ?>
              <?php $thumbnailUrl = (string) QubitSetting::getByName('siteBaseUrl').$thumbnail->path.$thumbnail->name; ?>
              <?php echo include_partial('getRecordAtomFeedEntry', ['object' => $thumbnail, 'url' => $thumbnailUrl, 'usage' => 'thumbnail']); ?>
            <?php } ?>
          </feed>
        </about>
      <?php } ?>
