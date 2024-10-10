<?php echo '<?xml version="1.0" encoding="'.sfConfig::get('sf_charset', 'UTF-8').'" ?>'; ?>
<sword:error href="http://purl.org/net/sword/error/ErrorNotImplemented"
             xmlns="http://www.w3.org/2005/Atom"
             xmlns:sword="http://purl.org/net/sword/">

  <?php if (isset($summary)) { ?>
    <summary type="text"><?php echo $summary; ?></summary>
  <?php } ?>

  <title type="text">ERROR</title>

  <updated><?php $dt = new DateTime();
  echo $dt->format('c'); ?></updated>

  <generator uri="<?php echo url_for('@homepage', true); ?>" version="<?php echo qubitConfiguration::VERSION; ?>">Qubit <?php echo qubitConfiguration::VERSION; ?></generator>

  <sword:userAgent><?php echo $_SERVER['HTTP_USER_AGENT']; ?></sword:userAgent>

  <link rel="alternate" href="http://www.accesstomemory.org/wiki/index.php?title=SWORD" type="text/html"/>

</sword:error>
