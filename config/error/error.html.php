<!DOCTYPE html>
<?php $path = sfConfig::get('sf_relative_url_root', preg_replace('#/[^/]+\.php5?$#', '', isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : (isset($_SERVER['ORIG_SCRIPT_NAME']) ? $_SERVER['ORIG_SCRIPT_NAME'] : ''))) ?>
<html>
  <head>
    <title>Error</title>
    <link rel="stylesheet" type="text/css" href="<?php echo $path ?>/plugins/arDominionPlugin/css/main.css"/>
  </head>
  <body class="yui-skin-sam admin error">

    <div id="wrapper" class="container">

      <section class="admin-message" id="error-404">

        <h2>
          <img alt="" src="<?php echo $path ?>/images/logo.png"/>
          Oops! An Error Occurred
        </h2>

        <p>
          Sorry, something went wrong.<br />
          The server returned a <code><?php echo $code ?> <?php echo $text ?></code>.
        </p>

        <div class="tips">
          <p>
            Try again a little later or ask in the <a href="http://groups.google.ca/group/ica-atom-users">discussion group</a>.<br />
            <a href="javascript:history.go(-1)">Back to previous page.</a>
          </p>
        </div>

      </section>

  </body>
</html>
