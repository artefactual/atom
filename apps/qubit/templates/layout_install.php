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

    <div id="page-wrapper">
      <div id="page">

        <div id="main-wrapper">
          <div class="clearfix" id="main">

            <div class="column sidebar" id="sidebar-first">
              <div class="section">
                <?php echo get_component_slot('install_sidebar') ?>
              </div>
            </div>

            <div class="column" id="content">
              <div class="section">
                <?php echo $sf_content ?>
              </div>
            </div>

          </div>
        </div> <!-- /#main-wrapper -->

      </div>
    </div> <!-- /#page-wrapper -->

  </body>
</html>
