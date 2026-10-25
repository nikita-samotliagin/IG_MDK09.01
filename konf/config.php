<?php

return [
    'dsn'      => getenv('KONF_DSN') ?: 'sqlite:' . __DIR__ . '/database/konf.sqlite',
    'db_user'  => getenv('KONF_DB_USER') ?: null,
    'db_pass'  => getenv('KONF_DB_PASS') ?: null,
    'admin'    => ['login' => 'Conf2027', 'password' => 'Demo77'],
    'max_fail' => 5,      
    'lock_sec' => 300,    
];
