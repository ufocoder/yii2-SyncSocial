<?php

namespace tests\functional\components;

use Mockery;
use ufocoder\SyncSocial\SyncService;
use Yii;
use yii\base\Exception;
use yii\codeception\TestCase;

/**
 * Class SyncServicesValidator
 * @package tests\functional\components
 */
class SyncServicesTest extends TestCase {

    public $appConfig = '@tests/functional/_config.php';


    public function testFakeSyncOAuth2WrongConnection() {

        $fakeSyncService = new SyncService( [
            'serviceClass' => '\tests\models\TestOAuth2',
            'returnUrl'    => 'http://my_own_site/returnUrl'
        ] );

        $mockService = Mockery::mock( $fakeSyncService->service );
        $mockService->shouldReceive( 'fetchAccessToken' )
                    ->andReturnUsing( function ( $id ) {
                        throw new Exception( 'Error message' );
                    } );

        $fakeSyncService->service = $mockService;
        $this->assertTrue( ! $fakeSyncService->connect() );
    }


    public function testFakeSyncOAuth1WrongConnection() {

        $fakeSyncService = new SyncService( [
            'serviceClass' => '\tests\models\TestOAuth1',
            'returnUrl'    => 'http://my_own_site/returnUrl'
        ] );

        $mockService = Mockery::mock( $fakeSyncService->service );
        $mockService->shouldReceive( 'fetchAccessToken' )
                    ->andReturnUsing( function ( ) {
                        throw new Exception( 'Error message' );
                    } );

        $fakeSyncService->service = $mockService;
        $this->assertTrue( ! $fakeSyncService->connect() );
    }


    public function testFakeSyncOpenIDWrongConnection() {

        $fakeSyncService = new SyncService( [
            'serviceClass' => '\yii\authclient\OpenId',
            'returnUrl'    => 'http://my_own_site/returnUrl'
        ] );

        $this->setExpectedException( 'yii\base\Exception', Yii::t('SyncSocial', 'SyncSocial is not support {serviceName}.', [
                'serviceName' => 'yii\authclient\OpenId',
            ]));

        $fakeSyncService->connect();

        $this->assertTrue( true );
    }

}