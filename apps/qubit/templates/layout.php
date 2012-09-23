<!DOCTYPE html>
<html lang="<?php echo $sf_user->getCulture() ?>" dir="<?php echo sfCultureInfo::getInstance($sf_user->getCulture())->direction ?>">
  <head>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="<?php echo public_path('favicon.ico') ?>"/>
    <?php include_stylesheets() ?>
    <?php if ($sf_context->getConfiguration()->isDebug()): ?>
      <script type="text/javascript" charset="utf-8">
        less = { env: 'development', optimize: 0 };
      </script>
    <?php endif; ?>
    <?php include_javascripts() ?>
  </head>
  <body class="<?php echo $sf_context->getModuleName() ?> <?php echo $sf_context->getActionName() ?> user-<?php echo $sf_user->isAuthenticated() ? 'logged' : 'anonymous' ?>">

    <?php echo get_partial('header') ?>

    <div id="wrapper" class="container">

      <?php echo $sf_content ?>

    </div>

    <?php echo get_partial('footer') ?>

    <div id="update-check">
      <?php echo get_component('default', 'updateCheck') ?>
    </div>

    <div id="print-date">
      <?php echo __('Printed: %d%', array('%d%' => date('Y-m-d'))) ?>
    </div>

  </body>
</html>
