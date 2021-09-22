<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error</title>
    <% for (var css in htmlWebpackPlugin.files.css) { %>
    <link href="<%= htmlWebpackPlugin.files.css[css] %>" rel="stylesheet">
    <% } %>
  </head>
  <body class="admin error">
    <div id="wrapper" class="container-xxl pt-3 text-center" role="main">
      <div id="content" class="d-inline-block mt-5 text-start" role="alert">
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
            'robot',
        ]; ?>
        <h1 class="h2 mb-0 p-3 border-bottom d-flex align-items-center">
          <i class="fas fa-fw fa-lg fa-<?php echo $icons[array_rand($icons)]; ?> me-3" aria-hidden="true"></i>
          Oops! An Error Occurred
        </h1>

        <div class="p-3">
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
    </div>
  </body>
</html>
