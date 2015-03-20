<?php

namespace tests\functional\components;

use Yii;
use Mockery;
use yii\codeception\TestCase;
use ufocoder\SyncSocial\SyncService;
use ufocoder\SyncSocial\components\Synchronizer;
use tests\models\Record;
use tests\components\TestSynchronizer;

/**
 * Class SynchronizerTest
 *
 * @package tests\functional\components
 */
class SynchronizerTest extends TestCase {

    public $appConfig = '@tests/functional/_config.php';


    public function testEmptyClassModelInit() {
        $this->setExpectedException( 'yii\base\Exception', 'Set model class to synchronization' );
        new Synchronizer();
        $this->assertTrue( true );
    }


    public function testNonExistsAttributeInit() {

        $this->setExpectedException( 'yii\base\Exception', 'Set model attribute to synchronization' );
        new Synchronizer( [
            'modelClass'     => '\tests\models\Record',
            'modelAttribute' => 'non_exists_attribute'
        ] );
        $this->assertTrue( true );
    }


    public function testGetServiceExtendClass() {

        $this->setExpectedException( 'yii\base\Exception', Yii::t( 'SyncSocial', 'Component service must be instance of SyncService class' ) );
        $synchronizer = new TestSynchronizer( [
            'modelClass'    => '\tests\models\Record',
            'settings'      => [
                'provider' => [ ]
            ]
        ] );

        $synchronizer->someMethodThatUpdateService('service', 'fake');
        $synchronizer->getService('service');

        $this->assertTrue( true );

    }


    public function testGetServiceList() {

        $synchronizer = new Synchronizer( [
            'modelClass' => '\tests\models\Record',
            'settings'   => [
                'provider_1' => [ ],
                'provider_2' => [ ],
                'provider_3' => [ ]
            ]
        ] );

        $serviceListByConfig = array_keys( $synchronizer->settings );
        $serviceListByMethod = $synchronizer->getServiceList();
        $this->assertTrue( $serviceListByConfig === $serviceListByMethod );
    }


    public function testSynchronizerUrlClosure() {

        $synchronizer = new Synchronizer( [
            'modelClass'    => '\tests\models\Record',
            'settings'      => [
                'provider' => [ ]
            ],
            'connectUrl'    => function ( $service ) {
                return Yii::$app->urlManager->createUrl( [ 'action/connect', 'service' => $service ] );
            },
            'disconnectUrl' => function ( $service ) {
                return Yii::$app->urlManager->createUrl( [ 'action/disconnect', 'service' => $service ] );
            },
            'syncUrl'       => function ( $service ) {
                return Yii::$app->urlManager->createUrl( [ 'action/sync', 'service' => $service ] );
            }
        ] );

        $this->assertTrue( $synchronizer->getConnectUrl( 'provider' ) == '/action/connect?service=provider' );
        $this->assertTrue( $synchronizer->getDisconnectUrl( 'provider' ) == '/action/disconnect?service=provider' );
        $this->assertTrue( $synchronizer->getSyncUrl( 'provider' ) == '/action/sync?service=provider' );
    }


    public function testSynchronizerUrlEmpty() {

        $synchronizer = new Synchronizer( [
            'modelClass' => '\tests\models\Record'
        ] );

        $this->assertTrue( $synchronizer->getConnectUrl( 'provider' ) == null );
        $this->assertTrue( $synchronizer->getDisconnectUrl( 'provider' ) == null );
        $this->assertTrue( $synchronizer->getSyncUrl( 'provider' ) == null );
    }


    public function testGetNonExistsService() {

        $serviceClass = 'non_exists_service';

        $this->setExpectedException( 'yii\base\Exception', Yii::t( 'SyncSocial', 'SyncSocial Extension not support "{serviceName}" service', [
            'serviceName' => $serviceClass
        ] ) );

        $synchronizer = new Synchronizer( [
            'modelClass' => '\tests\models\Record',
            'settings'   => [
                'provider' => [ ]
            ]
        ] );

        $synchronizer->getService( 'non_exists_service' );

        $this->assertTrue( true );
    }


    public function testGetExistsService() {

        $synchronizer = new Synchronizer( [
            'modelClass' => '\tests\models\Record'
        ] );

        $syncService = $synchronizer->getService( 'facebook' );

        $this->assertTrue( ! empty( $syncService ) );
    }


    public function testFakeGetSetService() {

        $synchronizer = new Synchronizer( [
            'modelClass' => '\tests\models\Record'
        ] );

        $fakeSyncService = new SyncService( [
            'serviceClass'    => '\tests\models\TestOAuth2',
            'serviceSettings' => [
                'authUrl' => 'https://fake_service/oauth/authorize',
            ],
            'returnUrl'       => 'http://my_own_site/returnUrl'
        ] );

        $synchronizer->setService( 'fake_service', $fakeSyncService );

        $syncService = $synchronizer->getService( 'fake_service' );

        $this->assertTrue( $syncService !== null );
        $this->assertTrue( $syncService === $fakeSyncService );
    }


    public function testFakeGetAuthorizationUri() {

        $synchronizer = new Synchronizer( [
            'modelClass' => '\tests\models\Record'
        ] );

        $fakeSyncService = new SyncService( [
            'serviceClass'    => '\tests\models\TestOAuth2',
            'serviceSettings' => [
                'authUrl' => 'https://fake_service/oauth/authorize',
            ],
            'returnUrl'       => 'http://my_own_site/returnUrl'
        ] );

        $synchronizer->setService( 'fake_service', $fakeSyncService );

        $syncUri = $synchronizer->getAuthorizationUri( 'fake_service' );

        $syncService    = $synchronizer->getService( 'fake_service' );
        $syncServiceUri = $syncService->getAuthorizationUri();
        $serviceUri     = $syncService->service->buildAuthUrl();

        $this->assertTrue( $syncUri === $syncServiceUri && $syncServiceUri === $serviceUri );
    }


    public function testFakeSyncConnection() {

        $_GET['code'] = 'fake_value';

        $synchronizer = new Synchronizer( [
            'modelClass' => '\tests\models\Record'
        ] );

        $fakeSyncService = new SyncService( [
            'serviceClass' => '\tests\models\TestOAuth2',
            'returnUrl'    => 'http://my_own_site/returnUrl'
        ] );

        $serviceName = 'fake_service';
        $synchronizer->setService( $serviceName, $fakeSyncService );

        $this->assertTrue( ! $synchronizer->isConnected( $serviceName ) );
        $this->assertTrue( $synchronizer->connect( $serviceName ) );
        $this->assertTrue( $synchronizer->isConnected( $serviceName ) );
        $synchronizer->disconnect( $serviceName );
        $this->assertTrue( ! $synchronizer->isConnected( $serviceName ) );
    }


    public function testSyncService() {

        $serviceName = 'fake_service';

        $synchronizer = new Synchronizer( [
            'modelClass' => '\tests\models\Record'
        ] );

        $fakeSyncService = new SyncService( [
            'serviceClass' => '\tests\models\TestOAuth2',
            'returnUrl'    => 'http://my_own_site/returnUrl'
        ] );

        $mockSyncService = Mockery::mock( $fakeSyncService );
        $mockSyncService->shouldReceive( 'isConnected' )
                        ->andReturnUsing( function () {
                            return true;
                        } );

        $mockSyncService->shouldReceive( 'getPosts' )
                        ->andReturnUsing( function () {
                            return [
                                [
                                    'service_id_author' => '2000',
                                    'service_id_post'   => '1000',
                                    'content'           => 'something',
                                    'time_created'      => 12345
                                ]
                            ];
                        } );


        $synchronizer->setService( $serviceName, $mockSyncService );

        $result = $synchronizer->syncService( $serviceName );

        $this->assertTrue( $result['flag'] && $result['count'] == 1 );
    }


    public function testSyncModel() {

        $serviceName = 'fake_service';

        $synchronizer = new Synchronizer( [
            'modelClass' => '\tests\models\Record'
        ] );

        $fakeSyncService = new SyncService( [
            'serviceClass' => '\tests\models\TestOAuth2',
            'returnUrl'    => 'http://my_own_site/returnUrl'
        ] );

        $mockSyncService = Mockery::mock( $fakeSyncService );
        $mockSyncService->shouldReceive( 'isConnected' )
                        ->andReturnUsing( function () {
                            return true;
                        } );

        $mockSyncService->shouldReceive( 'publishPost' )
                        ->andReturnUsing( function ( $message, $url = null ) {
                            return [
                                'service_id_author' => '2000',
                                'service_id_post'   => '1000',
                                'service_language'  => 'ru',
                                'time_created'      => strtotime( 'Thu Oct 23 07:00:00 +0000 2014' ),
                            ];
                        } );


        $mockSyncService->shouldReceive( 'deletePost' )
                        ->andReturnUsing( function ( $id) {
                            return true;
                        } );


        $synchronizer->setService( $serviceName, $mockSyncService );

        $model  = new Record();
        $model->content = 'test content';
        $model->save();

        $result = $synchronizer->syncActiveRecord($serviceName, $model );

        $this->assertTrue( $result );

        $flag = $synchronizer->deleteSyncModel($serviceName, $model);

        $this->assertTrue( $flag );


    }
}