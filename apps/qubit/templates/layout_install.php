<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $sf_user->getCulture() ?>"<?php if ('rtl' == sfCultureInfo::getInstance($sf_user->getCulture())->direction): ?> dir="rtl"<?php endif; ?>>
  <head>
    <?php include_http_metas() ?>
    <?php include_metas() ?>
    <?php include_title() ?>
    <link rel="shortcut icon" href="<?php echo public_path('favicon.ico') ?>"/>
    <?php include_stylesheets() ?>
    <?php include_javascripts() ?>
  </head>
  <body class="yui-skin-sam <?php echo $sf_context->getModuleName() ?> <?php echo $sf_context->getActionName() ?>">

    <div id="wrapper" class="container" role="main">

      <div class="row">

        <div class="span3">
          <?php echo get_component_slot('install_sidebar') ?>
        </div>

        <div class="span9">

          <?php include_slot('before-content') ?>

          <?php if (!include_slot('content')): ?>
            <div id="content">
              <?php echo $sf_content ?>
            </div>
          <?php endif; ?>

          <?php include_slot('after-content') ?>

        </div>

      </div>

    </div>

  </body>
</html>
