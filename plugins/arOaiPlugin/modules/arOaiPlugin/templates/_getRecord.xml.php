<?php if ($errorCode): ?>
  <error code="<?php echo $errorCode ?>"><?php echo $errorMsg ?></error>
<?php else: ?>
  <?php if (QubitAcl::check($record, 'read')): ?>
    <GetRecord>
      <record>
        <header>
          <identifier><?php echo $record->getOaiIdentifier() ?></identifier>
          <datestamp><?php echo QubitOai::getDate($record->getUpdatedAt())?></datestamp>
          <setSpec><?php echo $record->getCollectionRoot()->getOaiIdentifier()?></setSpec>
        </header>
        <metadata>
          <?php if ($metadataPrefix == 'oai_dc' && !arOaiPluginComponent::cachedMetadataExists($record, $metadataPrefix)): ?>
            <?php echo get_component('sfDcPlugin', 'dc', array('resource' => $record)) ?>
          <?php else: ?>
            <?php arOaiPluginComponent::includeCachedMetadata($record, $metadataPrefix) ?>
          <?php endif; ?>
        </metadata>
        <?php if (count($record->digitalObjects)): ?>
          <?php include('_about.xml.php') ?>
        <?php endif; ?>
      </record>
    </GetRecord>
  <?php endif; ?>
<?php endif; ?>
