<?php

// This check prevents access to debug front controllers that are deployed by
// accident to production servers. Feel free to remove this, extend it or make
// something more sophisticated.

$allowedIps = array('127.0.0.1', '::1');
if (false !== $envIp = getenv('ATOM_DEBUG_IP'))
{
  $allowedIps = array_merge($allowedIps, array_filter(explode(',', $envIp)));
}

if (!in_array(@$_SERVER['REMOTE_ADDR'], $allowedIps))
{
  die('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

require_once(dirname(__FILE__).'/config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'dev', true);
sfContext::createInstance($configuration)->dispatch();
