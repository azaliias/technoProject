<?php
return array(
    // backend #############################################################################
    'admin-login' => 'user/admin-login',
    'admin/index' => 'admin/contact',
    'admin/<controller:\w+>/<id:\d+>' => 'admin/<controller>/view',
    'admin/<controller:\w+>/<action:\w+>/<id:\d+>' => 'admin/<controller>/<action>',
    'admin/<controller:\w+>/<action:\w+>' => 'admin/<controller>/<action>',

    // frontend ############################################################################

    'create' => 'site/create-log',
    'search' => 'site/search-log',

    '<controller:\w+>/<id:\d+>' => '<controller>/view',
    '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
    '<controller:\w+>/<action:\w+>/*' => '<controller>/<action>',
);