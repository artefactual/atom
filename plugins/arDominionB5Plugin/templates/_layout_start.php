<!DOCTYPE html>
<html lang="<?php echo $sf_user->getCulture(); ?>" dir="<?php echo sfCultureInfo::getInstance($sf_user->getCulture())->direction; ?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include_title(); ?>
    <?php echo get_component('default', 'tagManager', ['code' => 'script']); ?>
    <link rel="shortcut icon" href="<?php echo public_path('favicon.ico'); ?>"/>
    <!-- TODO: Organize, customize and build CSS from Bootstrap and custom SASS -->
    <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/plugins/arDominionB5Plugin/css/tmp.css">
    <!-- TODO: Move jQuery after footer when all JS is also there -->
    <script src="/node_modules/jquery/dist/jquery.min.js"></script>
  </head>
  <body class="<?php echo $sf_context->getModuleName(); ?> <?php echo $sf_context->getActionName(); ?>">
    <?php echo get_component('default', 'tagManager', ['code' => 'noscript']); ?>
    <?php echo get_partial('header'); ?>
    <?php include_slot('pre'); ?>
