  <Identify>
    <repositoryName><?php echo $title ?></repositoryName>
    <baseURL><?php echo QubitOai::getBaseUrl() ?></baseURL>
    <protocolVersion><?php echo $protocolVersion ?></protocolVersion>
    <?php foreach ($adminEmails as $email): ?>
      <adminEmail><?php echo trim($email) ?></adminEmail>
    <?php endforeach; ?>
    <earliestDatestamp><?php echo $earliestDatestamp ?></earliestDatestamp>
    <deletedRecord><?php echo $deletedRecord ?></deletedRecord>
    <granularity><?php echo $granularity ?></granularity>
    <compression><?php echo $compression?></compression>
    <description>
      <oai-identifier xmlns="http://www.openarchives.org/OAI/2.0/oai-identifier" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai-identifier http://www.openarchives.org/OAI/2.0/oai-identifier.xsd">
        <scheme>oai</scheme>
        <repositoryIdentifier><?php echo QubitOai::getOaiNamespaceIdentifier() ?></repositoryIdentifier>
        <delimiter>:</delimiter>
        <sampleIdentifier><?php echo QubitOai::getOaiSampleIdentifier() ?></sampleIdentifier>
      </oai-identifier>
    </description>
  </Identify>
