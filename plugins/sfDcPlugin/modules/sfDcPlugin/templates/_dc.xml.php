<oai_dc:dc xmlns="http://purl.org/dc/elements/1.1/"
    xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">

  <title><?php echo $resource->title ?></title>

  <?php foreach ($resource->getCreators() as $item): ?>
    <creator><?php echo $item ?></creator>
  <?php endforeach; ?>

  <?php foreach ($dc->subject as $item): ?>
    <subject><?php echo $item ?></subject>
  <?php endforeach; ?>

  <description><?php echo $resource->scopeAndContent ?></description>

  <?php foreach ($resource->getPublishers() as $item): ?>
    <publisher><?php echo $item ?></publisher>
  <?php endforeach; ?>

  <?php foreach ($resource->getContributors() as $item): ?>
    <contributor><?php echo $item ?></contributor>
  <?php endforeach; ?>

  <?php foreach ($dc->date as $item): ?>
    <date><?php echo $item ?></date>
  <?php endforeach; ?>

  <?php foreach ($dc->type as $item): ?>
    <type><?php echo $item ?></type>
  <?php endforeach; ?>

  <?php foreach ($dc->format as $item): ?>
    <format><?php echo $item ?></format>
  <?php endforeach; ?>

  <identifier><?php echo url_for(array($resource, 'module' => 'informationobject'), true) ?></identifier>

  <identifier><?php echo $resource->identifier ?></identifier>

  <source><?php echo $resource->locationOfOriginals ?></source>

  <?php foreach ($resource->language as $code): ?>
    <language xsi:type="dcterms:ISO639-3"><?php echo strtolower($iso639convertor->getID3($code)) ?></language>
  <?php endforeach; ?>

  <?php if (isset($resource->repository)): ?>
    <relation><?php echo url_for(array($resource->repository, 'module' => 'repository'), true) ?></relation>
    <relation><?php echo $resource->repository->authorizedFormOfName ?></relation>
  <?php endif; ?>

  <?php foreach ($dc->coverage as $item): ?>
    <coverage><?php echo $item ?></coverage>
  <?php endforeach; ?>

  <rights><?php echo $resource->accessConditions ?></rights>

</oai_dc:dc>
