<?php use_helper('Date') ?>
<?php echo '<?xml version="1.0" encoding="'.sfConfig::get('sf_charset', 'UTF-8').'" ?>' ?>
<entry xmlns="http://www.w3.org/2005/Atom"
       xmlns:sword="http://purl.org/net/sword/">

  <title><?php echo render_title($informationObject, false) ?></title>

  <?php
    // For unknown reasons the type of the createdAt property is not always a DateTime but a string
    // This is a workaround to retrieve the same result if the property returns a string
    $creationDate = $informationObject->createdAt instanceof DateTime ? $informationObject->createdAt->format('c') : format_date($informationObject->createdAt, 's')
  ?>

  <id><?php echo $informationObject->id.' / ' . $creationDate ?></id>

  <updated><?php echo $creationDate ?></updated>
  <author>
    <name><?php echo $user->user ?></name>
  </author>

  <generator uri="<?php echo url_for('@homepage', true) ?>" version="<?php echo qubitConfiguration::VERSION ?>">Qubit <?php echo qubitConfiguration::VERSION ?></generator>

  <content type="text/html" src="<?php echo url_for(array($informationObject, 'module' => 'informationobject'), true) ?>" />

  <sword:noOp>false</sword:noOp>

  <sword:packaging><?php echo $package['format'] ?></sword:packaging>

  <sword:userAgent><?php echo $_SERVER['HTTP_USER_AGENT'] ?></sword:userAgent>

  <?php
    /*
      TODO. See X-On-Behalf-Of [2] and medation
      <contributor><name><?php echo $.. ?></name></contributor>
      <category>...<category>
      <summary type="text">...</summary>
      <sword:treatment>Treatment description</sword:treatment>
      <link rel="edit-media" href="http://www.myrepository.ac.uk/geography/my_deposit.zip"/>
      <link rel="edit" href="http://www.myrepository.ac.uk/geography-collection/atom/my_deposit.atom" />
    */
  ?>

</entry>
