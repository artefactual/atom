<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php $path = sfConfig::get('sf_relative_url_root', preg_replace('#/[^/]+\.php5?$#', '', isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : (isset($_SERVER['ORIG_SCRIPT_NAME']) ? $_SERVER['ORIG_SCRIPT_NAME'] : ''))) ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo sfConfig::get('sf_charset', 'utf-8') ?>"/>
<meta name="title" content="Qubit"/>
<meta name="robots" content="index, follow"/>
<meta name="language" content="en"/>
<title>Qubit</title>

<link rel="shortcut icon" href="/favicon.ico"/>
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $path ?>/sf/sf_default/css/screen.css"/>
<!--[if lt IE 7.]>
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $path ?>/sf/sf_default/css/ie.css"/>
<![endif]-->

<link rel="stylesheet" type="text/css" media="all" href="<?php echo $path ?>/plugins/sfDrupalPlugin/vendor/drupal/modules/system/defaults.css"/>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $path ?>/plugins/sfDrupalPlugin/vendor/drupal/modules/system/system.css"/>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $path ?>/plugins/sfDrupalPlugin/vendor/drupal/themes/garland/style.css"/>
<link rel="stylesheet" type="text/css" media="all" href="<?php echo $path ?>/plugins/sfDrupalPlugin/vendor/drupal/themes/garland/minnelli/minnelli.css"/>
<link rel="stylesheet" type="text/css" media="print" href="<?php echo $path ?>/plugins/sfDrupalPlugin/vendor/drupal/themes/garland/print.css"/>
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $path ?>/css/main.css"/>

</head>
<body>
<div class="sfTContainer" id="wrapper">
  <div class="clear-block" id="container">

    <div id="header">
      <div id="logo-floater">
        <h1><img alt="" id="logo" src="<?php echo $path ?>/images/logo.png"/><div>Qubit</div></h1>
      </div>
    </div>

    <div id="center">
      <div id="squeeze">
        <div class="right-corner">
          <div class="left-corner">

            <div class="sfTMessageContainer sfTAlert">
              <img alt="page not found" class="sfTMessageIcon" src="<?php echo $path ?>/sf/sf_default/images/icons/tools48.png" height="48" width="48"/>
              <div class="sfTMessageWrap">
                <h1>Website Temporarily Unavailable</h1>
                <h5>Please try again in a few seconds...</h5>
              </div>
            </div>

            <dl class="sfTMessageInfo">
              <dt>What's next</dt>
              <dd>
                <ul class="sfTIconList">
                  <li class="sfTReloadMessage"><a href="javascript:window.location.reload()">Try again: Reload Page</a></li>
                </ul>
              </dd>
            </dl>

          </div>
        </div>
      </div>
    </div>

  </div>
</div>
</body>
</html>
