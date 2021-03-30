<oai_dc:dc
    xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/
    http://www.openarchives.org/OAI/2.0/oai_dc.xsd">

  <?php if (!empty($resource->title)) { ?>
    <dc:title><?php echo esc_specialchars(strval($resource->title)); ?></dc:title>
  <?php } ?>

  <?php foreach ($dc->creators as $item) { ?>
    <dc:creator><?php echo esc_specialchars(strval($item)); ?></dc:creator>
  <?php } ?>

  <?php foreach ($dc->subject as $item) { ?>
    <dc:subject><?php echo esc_specialchars(strval($item)); ?></dc:subject>
  <?php } ?>

  <?php if (!empty($resource->scopeAndContent)) { ?>
    <dc:description><?php echo esc_specialchars(strval($resource->scopeAndContent)); ?></dc:description>
  <?php } ?>

  <?php foreach ($resource->getPublishers() as $item) { ?>
    <dc:publisher><?php echo esc_specialchars(strval($item)); ?></dc:publisher>
  <?php } ?>

  <?php foreach ($resource->getContributors() as $item) { ?>
    <dc:contributor><?php echo esc_specialchars(strval($item)); ?></dc:contributor>
  <?php } ?>

  <?php foreach ($dc->date as $item) { ?>
    <dc:date><?php echo esc_specialchars(strval($item)); ?></dc:date>
  <?php } ?>

  <?php foreach ($dc->type as $item) { ?>
    <dc:type><?php echo esc_specialchars(strval($item)); ?></dc:type>
  <?php } ?>

  <?php foreach ($dc->format as $item) { ?>
    <dc:format><?php echo esc_specialchars(strval($item)); ?></dc:format>
  <?php } ?>

  <dc:identifier><?php echo esc_specialchars(sfConfig::get('app_siteBaseUrl').'/'.$resource->slug); ?></dc:identifier>

  <?php if (!empty($resource->identifier)) { ?>
    <?php if (sfConfig::get('app_inherit_code_dc_xml', false)) { ?>
      <dc:identifier><?php echo esc_specialchars(strval($resource->getInheritedReferenceCode())); ?></dc:identifier>
    <?php } else { ?>
      <dc:identifier><?php echo esc_specialchars(strval($resource->identifier)); ?></dc:identifier>
    <?php } ?>
  <?php } ?>

  <?php if (!empty($resource->locationOfOriginals)) { ?>
    <dc:source><?php echo esc_specialchars(strval($resource->locationOfOriginals)); ?></dc:source>
  <?php } ?>

  <?php foreach ($resource->language as $code) { ?>
    <dc:language xsi:type="dcterms:ISO639-3"><?php echo esc_specialchars(strval(strtolower($iso639convertor->getID3($code)))); ?></dc:language>
  <?php } ?>

  <?php if (isset($resource->repository)) { ?>
    <dc:relation><?php echo esc_specialchars(sfConfig::get('app_siteBaseUrl').'/'.$resource->repository->slug); ?></dc:relation>
    <dc:relation><?php echo esc_specialchars(strval($resource->repository->authorizedFormOfName)); ?></dc:relation>
  <?php } ?>

  <?php foreach ($dc->coverage as $item) { ?>
    <dc:coverage><?php echo esc_specialchars(strval($item)); ?></dc:coverage>
  <?php } ?>

  <?php if (!empty($resource->accessConditions)) { ?>
    <dc:rights><?php echo esc_specialchars(strval($resource->accessConditions)); ?></dc:rights>
  <?php } ?>

</oai_dc:dc>
