<!DOCTYPE html>
<html lang="<?php echo $sf_user->getCulture(); ?>"
      dir="<?php echo sfCultureInfo::getInstance($sf_user->getCulture())->direction; ?>"
      media="<?php echo isset($_GET['media']) ? htmlspecialchars($_GET['media'], ENT_QUOTES, 'UTF-8') : 'screen'; ?>">
  <head>
    <?php echo get_partial('default/googleAnalytics'); ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include_title(); ?>
    <?php echo get_component('default', 'tagManager', ['code' => 'script']); ?>
    <?php if (file_exists($staticPath = sfConfig::get('app_static_path').DIRECTORY_SEPARATOR.'favicon.ico')) { ?>
      <?php $faviconLoc = sfConfig::get('app_static_alias').'/favicon.ico'; ?>
    <?php } else { ?>
      <?php $faviconLoc = public_path('favicon.ico'); ?>
    <?php } ?>
    <link rel="shortcut icon" href="<?php echo $faviconLoc; ?>">
    <%= htmlWebpackPlugin.tags.headTags %>
    <?php echo get_component_slot('css'); ?>
  </head>
  <body class="d-flex flex-column min-vh-100 <?php echo $sf_context->getModuleName(); ?> <?php echo $sf_context->getActionName(); ?><?php echo sfConfig::get('app_show_tooltips') ? ' show-edit-tooltips' : ''; ?>">
    <?php echo get_component('default', 'tagManager', ['code' => 'noscript']); ?>
    <?php echo get_partial('header'); ?>
    <?php include_slot('pre'); ?>
