<?php

namespace tests\unit\components\services;

use Codeception\Codecept;
use Mockery;
use Yii;
use yii\codeception\TestCase;
use ufocoder\SyncSocial\components\services\Vkontakte;

/**
 * Class VkontakteTest
 */
class VKontakteTest extends TestCase {

    public $appConfig = '@tests/unit/_config.php';

    /**
     * @return array
     */
    protected function getWallResponseSuccess() {
        return [
            'response' => [
                [
                    'id'      => 1,
                    'from_id' => 2,
                    'text'    => 'test',
                    'date'    => time()
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getPostResponseSuccess() {
        return [
            'response' => [
                'post_id' => 1
            ]
        ];
    }

    /**
     * @return \ufocoder\SyncSocial\components\services\Vkontakte
     */
    protected function buildVKontakteServiceWithResponseSuccess() {

        $sync = new Vkontakte();

        $mock = Mockery::mock( $sync->service )
                       ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive( 'api' )
             ->andReturnUsing( function ( $apiSubUrl, $method = 'GET', array $params = [ ], array $headers = [ ] ) {
                 if ( preg_match( '/^wall\.get/i', $apiSubUrl ) && $method == 'GET' ) {
                     return $this->getWallResponseSuccess();
                 }
                 if ( $apiSubUrl == 'wall.post' && $method == 'GET' ) {
                     return $this->getPostResponseSuccess();
                 }
                 if ( $apiSubUrl == 'wall.getById' && $method == 'GET' ) {
                     return $this->getWallResponseSuccess();
                 }
                 if ( $apiSubUrl == 'wall.delete' && $method == 'GET' ) {
                     return [
                         'response' => 1
                     ];
                 }
             } );

        $mock->shouldReceive( 'getUserAttributes' )
             ->andReturnUsing( function () {
                 return [
                     'id' => 1
                 ];
             } );

        $sync->service = $mock;

        return $sync;
    }


    /**
     * @return \ufocoder\SyncSocial\components\services\Vkontakte
     */
    protected function buildVKontakteServiceWithResponseEmpty() {

        $sync = new Vkontakte();

        $mock = Mockery::mock( $sync->service );
        $mock->shouldReceive( 'api' )
             ->andReturnUsing( function ( $apiSubUrl, $method = 'GET', array $params = [ ], array $headers = [ ] ) {
                 return [ ];
             } );

        $sync->service = $mock;

        return $sync;
    }


    public function testGetPostsSuccess() {

        $sync = $this->buildVKontakteServiceWithResponseSuccess();

        $response = $this->getWallResponseSuccess();
        $result   = $sync->getPosts();

        $this->assertTrue( $result[0]['service_id_post'] == $response['response'][0]['id'] );
        $this->assertTrue( $result[0]['service_id_author'] == $response['response'][0]['from_id'] );
        $this->assertTrue( $result[0]['content'] == $response['response'][0]['text'] );
        $this->assertTrue( $result[0]['time_created'] == $response['response'][0]['date'] );

    }


    public function testGetPostsEmpty() {

        $sync   = $this->buildVKontakteServiceWithResponseEmpty();
        $result = $sync->getPosts();

        $this->assertTrue( $result === null );

    }


    public function testPublishPost() {

        $sync   = $this->buildVKontakteServiceWithResponseSuccess();
        $result = $sync->publishPost( "fake_message" );

        $this->assertTrue( true );

    }

    public function testDeletePost() {
        $sync = $this->buildVKontakteServiceWithResponseSuccess();
        $this->assertTrue( $sync->deletePost( 1 ) );
    }

}