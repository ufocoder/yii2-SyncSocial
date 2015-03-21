<?php

namespace tests\unit\components;

use Yii;
use yii\codeception\TestCase;

use \ufocoder\SyncSocial\SyncService;

/**
 * Class SyncServiceTest
 *
 * @package tests\unit\components
 */
class SyncServiceTest extends TestCase {

    public $appConfig = '@tests/unit/_config.php';


    public function testNotExistsServiceClass() {

        $serviceClass = 'NotExistsServiceClass';

        $this->setExpectedException( 'yii\base\Exception', Yii::t( 'SyncSocial', 'Authclient Extension not support "{serviceName}" service', [
            '{serviceName}' => $serviceClass
        ] ) );

        new SyncService( [
            'serviceClass' => $serviceClass
        ] );

        $this->assertTrue( true );
    }


    public function testSyncServiceGetName() {

        $sync = new SyncService( [
            'serviceClass' => '\tests\models\TestOAuth2',
        ] );

        $service = $sync->service;

        $this->assertTrue( $service->getName() === $sync->getName() );
    }


    public function testGetAuthorizationUriOAuth1() {

        $sync = new SyncService( [
            'serviceClass'    => '\tests\models\TestOAuth1',
            'serviceSettings' => [
                'authUrl' => 'https://fake_service/oauth/authorize',
            ],
            'returnUrl'       => 'http://my_own_site/returnUrl'
        ] );

        $service      = $sync->service;
        $requestToken = $service->fetchRequestToken();

        $this->assertTrue( $service->buildAuthUrl( $requestToken ) == $sync->getAuthorizationUri() );
    }

    public function testGetAuthorizationUriOAuth2() {

        $sync = new SyncService( [
            'serviceClass'    => '\tests\models\TestOAuth2',
            'serviceSettings' => [
                'authUrl' => 'https://fake_service/oauth/authorize',
            ],
            'returnUrl'       => 'http://my_own_site/returnUrl'
        ] );

        $service = $sync->service;

        $this->assertTrue( $service->buildAuthUrl() == $sync->getAuthorizationUri() );
    }


    public function testGetAuthorizationUriOpenID() {

        $className = 'yii\authclient\OpenId';

        $this->setExpectedException( 'yii\base\Exception', Yii::t( 'SyncSocial', 'SyncSocial is not support {serviceName}.', [
            'serviceName' => $className
        ] ) );

        $sync = new SyncService( [
            'serviceClass'    => $className
        ] );

        $sync->getAuthorizationUri();

        $this->assertTrue( true );
    }


    public function testConnectionOAuth2NowEmptyCode() {

        $_REQUEST['code'] = 'fake_value';

        $sync = new SyncService( [
            'serviceClass'    => '\tests\models\TestOAuth2',
            'serviceSettings' => [
                'authUrl' => 'https://fake_service/oauth/authorize',
            ],
            'returnUrl'       => 'http://my_own_site/returnUrl'
        ] );

        $this->assertTrue( ! $sync->isConnected() );
        $this->assertTrue( $sync->connect() );
        $this->assertTrue( $sync->isConnected() );
        $sync->disconnect();
        $this->assertTrue( ! $sync->isConnected() );
    }


    public function testConnectionOAuth2EmptyCode() {

        $sync = new SyncService( [
            'serviceClass'    => '\tests\models\TestOAuth2',
            'serviceSettings' => [
                'authUrl' => 'https://fake_service/oauth/authorize',
            ],
            'returnUrl'       => 'http://my_own_site/returnUrl'
        ] );

        $this->assertTrue( $sync->connect() );

        $this->assertTrue( $sync->isConnected() );
        $sync->disconnect();
        $this->assertTrue( ! $sync->isConnected() );
    }


    public function testConnectionOAuth1() {

        $_GET['oauth_token']    = 'fake_value';
        $_GET['oauth_verifier'] = 'fake_value';

        $sync = new SyncService( [
            'serviceClass'    => '\tests\models\TestOAuth1',
            'serviceSettings' => [
                'authUrl' => 'https://fake_service/oauth/authorize',
            ],
            'returnUrl'       => 'http://my_own_site/returnUrl'
        ] );

        $sync->service->fetchRequestToken();

        $this->assertTrue( ! $sync->isConnected() );
        $this->assertTrue( $sync->connect() );
        $this->assertTrue( $sync->isConnected() );
        $sync->disconnect();
        $this->assertTrue( ! $sync->isConnected() );
    }


    public function testConnectionOpenID() {

        $className = 'yii\authclient\OpenId';

        $this->setExpectedException( 'yii\base\Exception', Yii::t( 'SyncSocial', 'SyncSocial is not support {serviceName}.', [
            'serviceName' => $className
        ] ) );

        $sync = new SyncService( [
            'serviceClass'    => $className
        ] );

        $sync->connect();

        $this->assertTrue( true );
    }


    public function testReturnNullDefault() {

        $sync = new SyncService( [
            'serviceClass' => '\tests\models\TestOAuth2',
            'returnUrl'    => 'http://fakehost/returnUrl'
        ] );

        $this->assertTrue( is_null( $sync->getPosts() ) );
        $this->assertTrue( is_null( $sync->publishPost( 'message' ) ) );
        $this->assertTrue( $sync->deletePost( 1 ) === false );
        $this->assertTrue( ! $sync->isReadOnly() );
    }

}