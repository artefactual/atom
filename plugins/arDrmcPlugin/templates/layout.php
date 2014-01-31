<!DOCTYPE html>
<html lang="<?php echo $sf_user->getCulture() ?>" dir="<?php echo sfCultureInfo::getInstance($sf_user->getCulture())->direction ?>">
  <head>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="<?php echo public_path('favicon.ico') ?>"/>
    <?php include_stylesheets() ?>
    <?php include_javascripts() ?>
  </head>
  <body ng-app="momaApp">

    <?php // echo get_partial('header') ?>

    <div id="wrapper" class="container">

      <?php echo $sf_content ?>

    </div>

    <?php // echo get_partial('footer') ?>

  </body>
</html>
