<oai_dc:dc
    xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/
    http://www.openarchives.org/OAI/2.0/oai_dc.xsd">

  <dc:title><?php echo esc_specialchars(strval($resource->title)) ?></dc:title>

  <?php foreach ($resource->getCreators() as $item): ?>
    <dc:creator><?php echo esc_specialchars(strval($item)) ?></dc:creator>
  <?php endforeach; ?>

  <?php foreach ($dc->subject as $item): ?>
    <dc:subject><?php echo esc_specialchars(strval($item)) ?></dc:subject>
  <?php endforeach; ?>

  <dc:description><?php echo esc_specialchars(strval($resource->scopeAndContent)) ?></dc:description>

  <?php foreach ($resource->getPublishers() as $item): ?>
    <dc:publisher><?php echo esc_specialchars(strval($item)) ?></dc:publisher>
  <?php endforeach; ?>

  <?php foreach ($resource->getContributors() as $item): ?>
    <dc:contributor><?php echo esc_specialchars(strval($item)) ?></dc:contributor>
  <?php endforeach; ?>

  <?php foreach ($dc->date as $item): ?>
    <dc:date><?php echo esc_specialchars(strval($item)) ?></dc:date>
  <?php endforeach; ?>

  <?php foreach ($dc->type as $item): ?>
    <dc:type><?php echo esc_specialchars(strval($item)) ?></dc:type>
  <?php endforeach; ?>

  <?php foreach ($dc->format as $item): ?>
    <dc:format><?php echo esc_specialchars(strval($item)) ?></dc:format>
  <?php endforeach; ?>

  <dc:identifier><?php echo esc_specialchars(sfConfig::get('app_siteBaseUrl') .'/'.$resource->slug) ?></dc:identifier>

  <dc:identifier><?php echo esc_specialchars(strval($resource->identifier)) ?></dc:identifier>

  <dc:source><?php echo esc_specialchars(strval($resource->locationOfOriginals)) ?></dc:source>

  <?php foreach ($resource->language as $code): ?>
    <dc:language xsi:type="dcterms:ISO639-3"><?php echo esc_specialchars(strval(strtolower($iso639convertor->getID3($code)))) ?></dc:language>
  <?php endforeach; ?>

  <?php if (isset($resource->repository)): ?>
    <dc:relation><?php echo esc_specialchars(url_for(array($resource->repository, 'module' => 'repository'), true)) ?></dc:relation>
    <dc:relation><?php echo esc_specialchars(strval($resource->repository->authorizedFormOfName)) ?></dc:relation>
  <?php endif; ?>

  <?php foreach ($dc->coverage as $item): ?>
    <dc:coverage><?php echo esc_specialchars(strval($item)) ?></dc:coverage>
  <?php endforeach; ?>

  <dc:rights><?php echo esc_specialchars(strval($resource->accessConditions)) ?></dc:rights>

</oai_dc:dc>
