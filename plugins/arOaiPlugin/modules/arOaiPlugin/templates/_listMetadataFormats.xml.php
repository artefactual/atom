  <ListMetadataFormats>
    <?php foreach (QubitOai::getMetadataFormats() as $metadataFormat): ?>
      <metadataFormat>
        <metadataPrefix><?php echo $metadataFormat['prefix'] ?></metadataPrefix>
        <schema><?php echo $metadataFormat['schema'] ?></schema>
        <metadataNamespace><?php echo $metadataFormat['namespace'] ?></metadataNamespace>
      </metadataFormat>
    <?php endforeach; ?>
  </ListMetadataFormats>
