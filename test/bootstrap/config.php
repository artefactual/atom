<?php

return array (
  'all' =>
  array (
    'propel' =>
    array (
      'class' => 'sfPropelDatabase',
      'param' =>
      array (
        'encoding' => 'utf8mb4',
        'persistent' => true,
        'pooling' => true,
        'dsn' => 'mysql:host=127.0.0.1;port=63003;dbname=atom;charset=utf8mb4',
        'username' => 'atom',
        'password' => 'atom_12345',
      ),
    ),
  ),
  'dev' =>
  array (
    'propel' =>
    array (
      'param' =>
      array (
        'classname' => 'DebugPDO',
        'debug' =>
        array (
          'realmemoryusage' => true,
          'details' =>
          array (
            'time' =>
            array (
              'enabled' => true,
            ),
            'slow' =>
            array (
              'enabled' => true,
              'threshold' => 0.1,
            ),
            'mem' =>
            array (
              'enabled' => true,
            ),
            'mempeak' =>
            array (
              'enabled' => true,
            ),
            'memdelta' =>
            array (
              'enabled' => true,
            ),
          ),
        ),
      ),
    ),
  ),
  'test' =>
  array (
    'propel' =>
    array (
      'param' =>
      array (
        'classname' => 'DebugPDO',
      ),
    ),
  ),
);

?>
