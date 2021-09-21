<!DOCTYPE html>
<?php $path = sfConfig::get('sf_relative_url_root', preg_replace('#/[^/]+\.php5?$#', '', isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : (isset($_SERVER['ORIG_SCRIPT_NAME']) ? $_SERVER['ORIG_SCRIPT_NAME'] : ''))); ?>
<html>
  <head>
    <title>AtoM</title>
    <link rel="stylesheet" type="text/css" href="<?php echo $path; ?>/plugins/arDominionPlugin/css/main.css"/>
  </head>
  <body class="yui-skin-sam admin unavailable">

    <div id="wrapper" class="container">

      <section class="admin-message" id="error-404">

        <h2>
          <img alt="" src="<?php echo $path; ?>/images/logo.png"/>
          Website Temporarily Unavailable
        </h2>

        <p>
          Please try again in a few seconds...
        </p>

        <div class="tips">
          <p>
            <a href="javascript:window.location.reload()">Try again: Reload Page</a>
          </p>
        </div>

      </section>

    </div>
    
  </body>
</html>
