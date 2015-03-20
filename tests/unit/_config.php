<?php

return [
    'id'         => 'test application',
    'basePath'   => dirname( dirname( dirname( __DIR__ ) ) ),
    'components' => array(
        'i18n' => array(
            'translations' => array(
                'SyncSocial' => array(
                    'class'    => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@SyncSocial/messages',
                ),
            ),
        )
    ),
];