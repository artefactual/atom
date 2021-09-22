<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AtoM</title>
    <% for (var css in htmlWebpackPlugin.files.css) { %>
    <link href="<%= htmlWebpackPlugin.files.css[css] %>" rel="stylesheet">
    <% } %>
  </head>
  <body class="admin unavailable">
    <div id="wrapper" class="container-xxl pt-3 text-center" role="main">
      <div id="content" class="d-inline-block mt-5 text-start" role="alert">
        <h1 class="h2 mb-0 p-3 border-bottom d-flex align-items-center">
          <i class="fas fa-fw fa-lg fa-tools me-3" aria-hidden="true"></i>
          Website Temporarily Unavailable
        </h1>

        <div class="p-3">
          <p>
            Please try again in a few seconds...
          </p>

          <p class="mb-0">
            <a href="javascript:window.location.reload()">Try again: Reload Page.</a>
          </p>
        </div>
      </div>
    </div>
  </body>
</html>
