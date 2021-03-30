<?php if ($errorCode) { ?>
  <error code="<?php echo $errorCode; ?>"><?php echo $errorMsg; ?></error>
<?php } else { ?>
  <GetRecord>
    <record>
      <header>
        <identifier><?php echo $record->getOaiIdentifier(); ?></identifier>
        <datestamp><?php echo QubitOai::getDate($record->getUpdatedAt()); ?></datestamp>
        <setSpec><?php echo $record->getCollectionRoot()->getOaiIdentifier(); ?></setSpec>
      </header>
      <metadata>
        <?php if ('oai_dc' == $metadataPrefix && !arOaiPluginComponent::checkDisplayCachedMetadata($record, $metadataPrefix)) { ?>
          <?php echo get_component('sfDcPlugin', 'dc', ['resource' => $record]); ?>
        <?php } else { ?>
          <?php arOaiPluginComponent::includeCachedMetadata($record, $metadataPrefix); ?>
        <?php } ?>
      </metadata>
      <?php if (count($record->digitalObjectsRelatedByobjectId)) { ?>
        <?php include '_about.xml.php'; ?>
      <?php } ?>
    </record>
  </GetRecord>
<?php } ?>
