<?php echo '<?xml version="1.0" encoding="'.sfConfig::get('sf_charset', 'UTF-8').'" ?>'; ?>
<service xmlns="http://www.w3.org/2007/app"
         xmlns:atom="http://www.w3.org/2005/Atom"
         xmlns:sword="http://purl.org/net/sword/"
         xmlns:dcterms="http://purl.org/dc/terms/">

  <sword:version><?php echo $version; ?></sword:version>
  <sword:verbose><?php echo $verbose; ?></sword:verbose>
  <sword:noOp><?php echo $noOp; ?></sword:noOp>
  <sword:maxUploadSize><?php echo $maxUploadSize; ?></sword:maxUploadSize>

  <workspace>
    <atom:title type="text"><?php echo $title; ?></atom:title>

    <?php foreach ($workspaces as $item) { ?>

      <collection href="<?php echo url_for([$item, 'module' => 'qtSwordPlugin', 'action' => 'deposit'], true); ?>">

        <atom:title type="text"><?php echo render_title($item); ?></atom:title>

        <?php foreach (qtSwordPluginConfiguration::$mediaRanges as $mediaRange) { ?>
          <accept><?php echo $mediaRange; ?></accept>
        <?php } ?>

        <sword:mediation><?php echo $mediation; ?></sword:mediation>

        <?php foreach (qtSwordPluginConfiguration::$packaging as $key => $value) { ?>
          <sword:acceptPackaging q="<?php echo $key; ?>"><?php echo $value; ?></sword:acceptPackaging>
        <?php } ?>

        <?php if (0 < count($item->getChildren())) { ?>
          <sword:service><?php echo url_for([$item, 'module' => 'qtSwordPlugin', 'action' => 'servicedocument'], true); ?></sword:service>
        <?php } ?>

      </collection>

    <?php } ?>

  </workspace>

</service>
