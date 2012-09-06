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

        <div id="update-check">
          <?php echo get_component('default', 'updateCheck') ?>
        </div>

        <?php echo get_partial('header') ?>

        <?php if ($sf_user->isAuthenticated()): ?>
          <div id="navigation">
            <div class="section">
              <?php echo get_component('menu', 'mainMenu', array('sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID())) ?>
            </div> <!-- /.section -->
          </div> <!-- /#navigation -->
        <?php endif; ?>

        <div id="main-wrapper">
          <div class="clearfix" id="main">

            <div class="column" id="content">
              <div id="print-date">
                <?php echo __('Printed: %d%', array('%d%' => date('Y-m-d'))) ?>
              </div>

              <div class="section">
                <?php echo $sf_content ?>
              </div> <!-- /.section -->
            </div> <!-- /.column#content -->

            <?php echo get_partial('sidebar') ?>

          </div> <!-- /#main -->
        </div> <!-- /#main-wrapper -->

        <?php echo get_partial('footer') ?>

      </div> <!-- /#page -->
    </div> <!-- /#page-wrapper -->

  </body>
</html>
