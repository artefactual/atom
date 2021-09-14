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
      <div id="content" class="d-inline-block mt-5 p-5 text-start">
        <?php $icons = [
            'bug',
            'dumpster-fire',
            'meh-rolling-eyes',
            'bomb',
            'exclamation-circle',
            'skull-crossbones',
            'book-dead',
            'heart-broken',
            'dizzy',
            'robot'
        ]; ?>
        <h2 class="mb-4">
          <i class="fas fa-lg fa-<?php echo $icons[array_rand($icons)]; ?> me-2" aria-hidden="true"></i>
          Oops! An Error Occurred
        </h2>

        <p>
          Sorry, something went wrong.<br>
          The server returned a <code class="ms-1"><?php echo $code; ?> <?php echo $text; ?></code>.
        </p>

        <p class="mb-0">
          Try again a little later or ask in the
          <a href="https://groups.google.com/g/ica-atom-users">
            discussion group.
          </a><br>
          <a href="javascript:history.go(-1)">Back to previous page.</a>
        </p>
      </div>
    </div>
  </body>
</html>
