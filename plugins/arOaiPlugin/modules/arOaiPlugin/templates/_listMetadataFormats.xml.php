  <ListMetadataFormats>
<?php
  foreach(QubitOai::getMetadataFormats() as $metadataFormat)
  {
    echo "    <metadataFormat>\n";
    echo "      <metadataPrefix>".$metadataFormat['prefix']."</metadataPrefix>\n";
    echo "      <schema>".$metadataFormat['schema']."</schema>\n";
    echo "      <metadataNamespace>".$metadataFormat['namespace']."</metadataNamespace>\n";
    echo "    </metadataFormat>\n";
  }
?>
  </ListMetadataFormats>
