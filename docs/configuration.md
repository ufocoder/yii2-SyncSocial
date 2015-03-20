### Configuration

All commented lines is written to show full possible config. You could delete them.

Add the following in your config:

```php
'components' => array(
        // ..

        // add Synchronizer component
        'synchronizer' => [
            'class'            => '\ufocoder\SyncSocial\components\Synchronizer',
            'modelClass'       => '\app\models\Post',
            'modelAttribute'   => 'content',
            'modelScenario'    => 'default',
            'services'    => [
                'facebook'   => [
                    'clientId'      => 'YOUR_FACEBOOK_APP_KEY',
                    'clientSecret'  => 'YOUR_FACEBOOK_APP_SECRET',
                    'scope'         => 'offline_access,publish_actions,read_stream'
                ],
                'vkontakte' => [
                    'clientId'     => 'YOUR_VKONTAKTE_APP_KEY',
                    'clientSecret' => 'YOUR_VKONTAKTE_APP_SECRET'
                ],
                'google' => [
                    'clientId'     => 'YOUR_GOOGLE_APP_KEY',
                    'clientSecret' => 'YOUR_GOOGLE_APP_SECRET',
                    'scope'        => implode( ' ', [
                        'profile',
                        'email',
                        'https://www.googleapis.com/auth/plus.me'
                    ] )
                ],
                'twitter'   => [
                    'consumerKey'     => 'YOUR_TWITTER_APP_KEY',
                    'consumerSecret' => 'YOUR_TWITTER_APP_SECRET'
                ],
            ],
            /*
            'absolutePostUrl' => function ( $service, $id_post ) {
                return Yii::$app->urlManager->createAbsoluteUrl( [
                    'default/post/view',
                    'id' => $id_post
                ] );
            },
            'connectUrl' => function ( $service ) {
                return Yii::$app->urlManager->createUrl( [
                    'admin/sync/connect',
                    'service' => $service
                ] );
            },
            'disconnectUrl' => function ( $service ) {
                return Yii::$app->urlManager->createUrl( [
                    'admin/sync/disconnect',
                    'service' => $service
                ] );
            },
            'syncUrl' => function ( $service ) {
                return Yii::$app->urlManager->createUrl( [
                    'admin/sync/sync',
                    'service' => $service
                ] );
            }
            */
        ],

        // ..

        // add Synchronizer's messages
        'i18n' => array(
            'translations' => array(
                // ..
                'SyncSocial' => array(
                    'class'    => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@ufocoder/SyncSocial/messages',
                ),
                // ..
            ),
        )

        // ..
    ),
```

Add the following in your controller:

```php
        public function actions() {
            return [
                'connect' => [
                    'class' => '\ufocoder\SyncSocial\actions\ConnectAction',
                    // 'successUrl' => 'YOUR_CUSTOM_SUCCESS_URL',
                    // 'failedUrl' => 'YOUR_CUSTOM_FAILED_URL'
                ],
                'disconnect' => [
                    'class' => '\ufocoder\SyncSocial\actions\ConnectAction',
                    // 'successUrl' => 'YOUR_CUSTOM_SUCCESS_URL',
                    // 'failedUrl' => 'YOUR_CUSTOM_FAILED_URL'
                ],
                'sync' => [
                    'class' => '\ufocoder\SyncSocial\actions\SyncAction',
                    // 'successUrl' => 'YOUR_CUSTOM_SUCCESS_URL',
                    // 'failedUrl' => 'YOUR_CUSTOM_FAILED_URL'
                ]
            ];
        }
```

Add the following in your model:


```php
    //..

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSyncModel()
    {
        return $this->hasOne('\ufocoder\SyncSocial\models\SyncModel', ['model_id' => 'id']);
    }

    //..

    /**
     * @return array
     */
    public function scenarios() {
        return [
            // ..
            'default' => [ 'content', 'syncServices' ]
            // ..
        ];
    }

    //..

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            //..
            'syncServices' => [
                'class' => SyncServicesValidator::className(),
                'canSyncActiveRecord' => function( $model ){
                    return $model->time_published >= time();
                },
                'syncDelete' => false
            ]
            //..
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            //..
            \ufocoder\SyncSocial\behaviors\SynchronizerBehavior::className(),
            //..
        ];
    }

    //..
```

Run migration to create table for sync related model:

```bash
php app/yiic.php migrate --migrationPath='@vendor/ufocoder/yii2-syncsocial/src/migrations'
```