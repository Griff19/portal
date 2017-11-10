<?php
//Окружение продакшн common
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'pgsql:host=192.168.0.7;port=5432;dbname=portal',
            'username' => 'portal',
            'password' => 'Q_dXWBQjF4H0',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
        ],
    ],
];
