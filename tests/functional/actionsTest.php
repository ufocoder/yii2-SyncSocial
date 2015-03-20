<?php

namespace tests\functional\components;

use Yii;
use yii\base\Module;
use yii\helpers\Url;
use yii\web\Controller;
use yii\codeception\TestCase;

use Mockery;

use ufocoder\SyncSocial\SyncService;
use ufocoder\SyncSocial\actions\SyncAction;
use ufocoder\SyncSocial\actions\ConnectAction;
use ufocoder\SyncSocial\actions\DisconnectAction;

/**
 * Class SynchronizerTest
 */
class actionSynchronizeTest extends TestCase {

    /**
     * @var \yii\web\Application
     */
    protected $mockApp;

    /**
     * @var string
     */
    protected $serviceName = 'fake_service';

    /**
     * @var string
     */
    public $appConfig = '@tests/functional/_config.php';


    protected function _before() {

        $this->mockApp             = $this->mockApplication();
        $this->mockApp->controller = new Controller( 'mockController', 'mockModule' );

        /**
         * @var $synchronizer \ufocoder\SyncSocial\components\Synchronizer
         */
        $syncService = new SyncService( [
            'serviceClass'    => '\tests\models\TestOAuth2',
            'serviceSettings' => [
                'authUrl' => 'https://fake_service/oauth/authorize',
            ],
            'returnUrl'       => 'http://my_own_site/returnUrl'
        ] );

        $synchronizer = $this->mockApp->synchronizer;
        $synchronizer->setService( $this->serviceName, $syncService );

        $this->mockApp->set( 'synchronizer', $synchronizer );

    }


    public function testNonExistsComponentInAction() {

        $this->setExpectedException( 'yii\base\Exception', Yii::t( 'SyncSocial', 'Component is not configured!' ) );

        $controller = new Controller( 'mockController', 'mockModule' );

        $action = new ConnectAction( 'action', $controller, [
            'componentName' => 'non_exists_component'
        ] );

        $action->runWithParams( [
            'service' => 'WhatEverService'
        ] );

        $this->assertTrue( true );
    }


    public function testNonExistsServiceInAction() {

        $serviceName = 'non_exists_service';
        $this->setExpectedException( 'yii\base\Exception', Yii::t( 'SyncSocial', 'SyncSocial Extension not support "{serviceName}" service', [
            'serviceName' => $serviceName
        ] ) );

        $controller = new Controller( 'mockController', 'mockModule' );
        $action     = new ConnectAction( 'action', $controller );

        $action->runWithParams( [
            'service' => $serviceName
        ] );

        $this->assertTrue( true );
    }


    public function testConnectionSuccessActionWithDefaultURLInModule() {

        $mockSynchronizer = Mockery::mock( $this->mockApp->synchronizer );
        $mockSynchronizer->shouldReceive( 'connect' )
                         ->andReturnUsing( function ( $serviceName = null ) {
                             return true;
                         } );

        $this->mockApp->set( 'synchronizer', $mockSynchronizer );
        $this->mockApp->controller->module = new Module( 'fake_module' );

        Yii::$app = $this->mockApp;

        $action = new ConnectAction( 'action', Yii::$app->controller );

        $response = $action->runWithParams( [
            'service' => $this->serviceName
        ] );

        $defaultUrl = '/' . $this->mockApp->controller->module->id
                      . '/' . $this->mockApp->controller->id
                      . '/' . $this->mockApp->controller->defaultAction;

        $this->assertTrue( $response->getIsRedirection() );
        $this->assertTrue( $response->getHeaders()->get( 'location' ) === Yii::$app->getRequest()->getHostInfo() . Url::to($defaultUrl) );
    }

    public function testConnectionSuccessAction() {

        $mockSynchronizer = Mockery::mock( $this->mockApp->synchronizer );
        $mockSynchronizer->shouldReceive( 'connect' )
                         ->andReturnUsing( function ( $serviceName = null ) {
                             return true;
                         } );

        $this->mockApp->set( 'synchronizer', $mockSynchronizer );

        Yii::$app = $this->mockApp;

        $action = new ConnectAction( 'action', Yii::$app->controller, [
            'successUrl' => 'http://fakehost/successUrl',
            'failedUrl'  => 'http://fakehost/failedUrl'
        ] );

        $response = $action->runWithParams( [
            'service' => $this->serviceName
        ] );

        $this->assertTrue( $response->getIsRedirection() );
        $this->assertTrue( $response->getHeaders()->get( 'location' ) === 'http://fakehost/successUrl' );
    }


    public function testConnectionFailedAction() {

        $mockSynchronizer = Mockery::mock( $this->mockApp->synchronizer );
        $mockSynchronizer->shouldReceive( 'connect' )
                         ->andReturnUsing( function ( $serviceName = null ) {
                             return false;
                         } );

        $this->mockApp->set( 'synchronizer', $mockSynchronizer );

        Yii::$app = $this->mockApp;

        $action = new ConnectAction( 'action', Yii::$app->controller, [
            'successUrl' => 'http://fakehost/successUrl',
            'failedUrl'  => 'http://fakehost/failedUrl'
        ] );

        $response = $action->runWithParams( [
            'service' => $this->serviceName
        ] );

        $this->assertTrue( $response->getIsRedirection() );
        $this->assertTrue( $response->getHeaders()->get( 'location' ) === 'http://fakehost/failedUrl' );
    }


    public function testDisconnectionSuccessAction() {

        $mockSynchronizer = Mockery::mock( $this->mockApp->synchronizer );
        $mockSynchronizer->shouldReceive( 'disconnect' )
                         ->andReturnUsing( function ( $serviceName = null ) {
                             return true;
                         } );

        $this->mockApp->set( 'synchronizer', $mockSynchronizer );

        Yii::$app = $this->mockApp;

        $action = new DisconnectAction( 'action', Yii::$app->controller, [
            'successUrl' => 'http://fakehost/successUrl',
            'failedUrl'  => 'http://fakehost/failedUrl'
        ] );

        $response = $action->runWithParams( [
            'service' => $this->serviceName
        ] );

        $this->assertTrue( $response->getIsRedirection() );
        $this->assertTrue( $response->getHeaders()->get( 'location' ) === 'http://fakehost/successUrl' );
    }


    public function testDisconnectionFailedAction() {

        $mockSynchronizer = Mockery::mock( $this->mockApp->synchronizer );
        $mockSynchronizer->shouldReceive( 'disconnect' )
                         ->andReturnUsing( function ( $serviceName = null ) {
                             return false;
                         } );

        $this->mockApp->set( 'synchronizer', $mockSynchronizer );

        Yii::$app = $this->mockApp;

        $action = new DisconnectAction( 'action', Yii::$app->controller, [
            'successUrl' => 'http://fakehost/successUrl',
            'failedUrl'  => 'http://fakehost/failedUrl'
        ] );

        $response = $action->runWithParams( [
            'service' => $this->serviceName
        ] );

        $this->assertTrue( $response->getIsRedirection() );
        $this->assertTrue( $response->getHeaders()->get( 'location' ) === 'http://fakehost/failedUrl' );
    }


    public function testSyncNotConnectAction() {

        Yii::$app = $this->mockApp;

        $action = new SyncAction( 'action', Yii::$app->controller, [
            'successUrl' => 'http://fakehost/successUrl',
            'failedUrl'  => 'http://fakehost/failedUrl'
        ] );

        $response = $action->runWithParams( [
            'service' => $this->serviceName
        ] );

        $this->assertTrue( $response->getIsRedirection() );
        $this->assertTrue( $response->getHeaders()->get( 'location' ) === 'http://fakehost/failedUrl' );
    }


    public function testSyncWithConnectAndSuccessSyncAction() {

        $mockSynchronizer = Mockery::mock( $this->mockApp->synchronizer );
        $mockSynchronizer->shouldReceive( 'isConnected' )
                         ->andReturnUsing( function ( $serviceName = null ) {
                             return true;
                         } );

        $mockSynchronizer->shouldReceive( 'syncService' )
                         ->andReturnUsing( function ( $serviceName = null ) {
                             return [
                                 'flag'  => true,
                                 'count' => 5
                             ];
                         } );

        $this->mockApp->set( 'synchronizer', $mockSynchronizer );

        Yii::$app = $this->mockApp;

        $action = new SyncAction( 'action', Yii::$app->controller, [
            'successUrl' => 'http://fakehost/successUrl',
            'failedUrl'  => 'http://fakehost/failedUrl'
        ] );

        $response = $action->runWithParams( [
            'service' => $this->serviceName
        ] );

        $this->assertTrue( $response->getIsRedirection() );
        $this->assertTrue( $response->getHeaders()->get( 'location' ) === 'http://fakehost/successUrl' );
    }


    public function testSyncWithConnectAndFailedSyncAction() {

        $mockSynchronizer = Mockery::mock( $this->mockApp->synchronizer );
        $mockSynchronizer->shouldReceive( 'isConnected' )
                         ->andReturnUsing( function ( $serviceName = null ) {
                             return true;
                         } );

        $mockSynchronizer->shouldReceive( 'syncService' )
                         ->andReturnUsing( function ( $serviceName = null ) {
                             return [
                                 'flag'  => false,
                                 'count' => 0
                             ];
                         } );

        $this->mockApp->set( 'synchronizer', $mockSynchronizer );

        Yii::$app = $this->mockApp;

        $action = new SyncAction( 'action', Yii::$app->controller, [
            'successUrl' => 'http://fakehost/successUrl',
            'failedUrl'  => 'http://fakehost/failedUrl'
        ] );

        $response = $action->runWithParams( [
            'service' => $this->serviceName
        ] );

        $this->assertTrue( $response->getIsRedirection() );
        $this->assertTrue( $response->getHeaders()->get( 'location' ) === 'http://fakehost/failedUrl' );
    }

}