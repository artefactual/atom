<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error</title>
    <link rel="stylesheet" href="/plugins/arDominionB5Plugin/build/css/min.css">
  </head>
  <body class="admin error">
    <div id="wrapper" class="container text-center">
      <div id="content" class="d-inline-block mt-5 p-5">
        <h2>Oops! An Error Occurred</h2>

        <p>
          Sorry, something went wrong.<br>
          The server returned a <code class="ms-1"><?php echo $code; ?> <?php echo $text; ?></code>.
        </p>

        <p class="mb-0">
          Try again a little later or ask in the
          <a href="http://groups.google.ca/group/ica-atom-users">
            discussion group.
          </a><br>
          <a href="javascript:history.go(-1)">Back to previous page.</a>
        </p>
      </div>
    </div>
  </body>
</html>
