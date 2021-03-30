<?php

return [
    'all' => [
        'propel' => [
            'class' => 'sfPropelDatabase',
            'param' => [
                'encoding' => 'utf8mb4',
                'persistent' => true,
                'pooling' => true,
                'dsn' => 'mysql:host=127.0.0.1;port=63003;dbname=atom;charset=utf8mb4',
                'username' => 'atom',
                'password' => 'atom_12345',
            ],
        ],
    ],
    'dev' => [
        'propel' => [
            'param' => [
                'classname' => 'DebugPDO',
                'debug' => [
                    'realmemoryusage' => true,
                    'details' => [
                        'time' => [
                            'enabled' => true,
                        ],
                        'slow' => [
                            'enabled' => true,
                            'threshold' => 0.1,
                        ],
                        'mem' => [
                            'enabled' => true,
                        ],
                        'mempeak' => [
                            'enabled' => true,
                        ],
                        'memdelta' => [
                            'enabled' => true,
                        ],
                    ],
                ],
            ],
        ],
    ],
    'test' => [
        'propel' => [
            'param' => [
                'classname' => 'DebugPDO',
            ],
        ],
    ],
];
