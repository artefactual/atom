<?php echo '<?xml version="1.0" encoding="'.sfConfig::get('sf_charset', 'UTF-8').'" ?>' ?>
<service xmlns="http://www.w3.org/2007/app"
         xmlns:atom="http://www.w3.org/2005/Atom"
         xmlns:sword="http://purl.org/net/sword/"
         xmlns:dcterms="http://purl.org/dc/terms/">

  <sword:version><?php echo $version ?></sword:version>
  <sword:verbose><?php echo $verbose ?></sword:verbose>
  <sword:noOp><?php echo $noOp ?></sword:noOp>
  <sword:maxUploadSize><?php echo $maxUploadSize ?></sword:maxUploadSize>

  <workspace>
    <atom:title type="text"><?php echo $title ?></atom:title>

    <?php foreach ($workspaces as $item): ?>

      <collection href="<?php echo url_for(array($item, 'module' => 'qtSwordPlugin', 'action' => 'deposit'), true) ?>">

        <atom:title type="text"><?php echo render_title($item) ?></atom:title>

        <?php # Accepted media ranges ?>
        <?php foreach (qtSwordPluginConfiguration::$mediaRanges as $mediaRange): ?>
          <accept><?php echo $mediaRange ?></accept>
        <?php endforeach; ?>

        <?php # MAY be included. Used for a human-readable description of collection policy. Include either a text description or a URI. ?>
        <?php # <sword:collectionPolicy>No guarantee of service, or that deposits will be retained for any length of time.</sword:collectionPolicy> ?>

        <?php # SHOULD be included. Used to indicate if mediated deposit is allowed on the defined collection. ?>
        <sword:mediation><?php echo $mediation ?></sword:mediation>

        <?php # MAY be included. Used for a human-readable statement about what treatment the deposited resource will receive. ?>
        <?php # <sword:treatment>This is a server</sword:treatment> ?>

        <?php # MAY be included. Used to identify the content packaging types supported by this collection. SHOULD be a URI from [SWORD-TYPES]. The q attribute MAY be used to indicate relative preferences between packaging formats (See Part A Section 1.1). ?>
        <?php foreach (qtSwordPluginConfiguration::$packaging as $key => $value): ?>
          <sword:acceptPackaging q="<?php echo $key ?>"><?php echo $value ?></sword:acceptPackaging>
        <?php endforeach; ?>

        <?php # 0 or more MAY be included to direct clients to nested service definitions. If present, the value MUST be a URI that dereferences to another SWORD Service Document. ?>
        <?php if (0 < count($item->getChildren())): ?>
          <sword:service><?php echo url_for(array($item, 'module' => 'qtSwordPlugin', 'action' => 'servicedocument'), true) ?></sword:service>
        <?php endif; ?>

        <?php # The use of a Dublin Core dcterms:abstract element containing a description of the Collection is RECOMMENDED. ?>
        <?php # <dcterms:abstract>Collection description</dcterms:abstract> ?>

      </collection>

    <?php endforeach; ?>

  </workspace>

</service>
