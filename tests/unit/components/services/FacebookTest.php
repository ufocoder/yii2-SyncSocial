<?php

namespace tests\unit\components\services;

use Codeception\Util\Debug;
use Mockery;
use yii\codeception\TestCase;
use ufocoder\SyncSocial\components\services\Facebook;


/**
 * Class FacebookTest
 */
class FacebookTest extends TestCase {

    public $appConfig = '@tests/unit/_config.php';

    /**
     * @return array
     */
    protected function getResponseFeedList() {
        return [
            'data' => [
                [
                    'id'           => 1,
                    'from'         => [
                        'id' => 2
                    ],
                    'message'      => 'test',
                    'created_time' => 'Thu Oct 23 07:00:00 +0000 2014'
                ]
            ]
        ];
    }


    /**
     * @return array
     */
    protected function getResponseFeedItem() {
        return [
            'id'           => 1,
            'from'         => [
                'id' => 2
            ],
            'created_time' => 'Thu Oct 23 07:00:00 +0000 2014'
        ];
    }

    /**
     * @return \ufocoder\SyncSocial\components\services\Facebook
     */
    protected function buildFacebookServiceWithResponseSuccess() {

        $sync = new Facebook();

        $mock = Mockery::mock( $sync->service );
        $mock->shouldReceive( 'api' )
             ->andReturnUsing( function ( $apiSubUrl, $method = 'GET', array $params = [ ], array $headers = [ ] ) {
                 if ( $apiSubUrl == 'me/feed' and $method == 'POST' ) {
                     return $this->getResponseFeedItem();
                 } elseif ( $apiSubUrl == 'me/feed' && $method == 'GET' ) {
                     return $this->getResponseFeedList();
                 } elseif ( is_integer( $apiSubUrl ) && $method == 'GET' ) {
                     return $this->getResponseFeedItem();
                 } elseif ( is_integer( $apiSubUrl ) && $method == 'DELETE' ) {
                     return [
                         'success' => 1
                     ];
                 }
             } );

        $sync->service = $mock;

        return $sync;
    }


    /**
     * @return \ufocoder\SyncSocial\components\services\Facebook
     */
    protected function buildFacebookServiceWithResponseEmpty() {

        $sync = new Facebook();

        $mock = Mockery::mock( $sync->service );
        $mock->shouldReceive( 'api' )
             ->andReturnUsing( function ( $apiSubUrl, $method = 'GET', array $params = [ ], array $headers = [ ] ) {
                 return [ ];
             } );

        $sync->service = $mock;

        return $sync;
    }


    public function testPublishPostSuccess() {

        $sync = $this->buildFacebookServiceWithResponseSuccess();

        $response = $this->getResponseFeedItem();
        $result   = $sync->publishPost( 'message' );

        $this->assertTrue( $result['service_id_post'] == $response['id'] );
        $this->assertTrue( $result['service_id_author'] == $response['from']['id'] );
        $this->assertTrue( $result['time_created'] == strtotime( $response['created_time'] ) );

    }


    public function testPublishPostEmpty() {

        $sync   = $this->buildFacebookServiceWithResponseEmpty();
        $result = $sync->publishPost( 'message' );
        $this->assertTrue( $result === [ ] );

    }


    public function testGetPostsSuccess() {

        $sync = $this->buildFacebookServiceWithResponseSuccess();

        $response = $this->getResponseFeedList();
        $result   = $sync->getPosts();

        $this->assertTrue( $result[0]['service_id_post'] == $response['data'][0]['id'] );
        $this->assertTrue( $result[0]['service_id_author'] == $response['data'][0]['from']['id'] );
        $this->assertTrue( $result[0]['content'] == $response['data'][0]['message'] );
        $this->assertTrue( $result[0]['time_created'] == strtotime( $response['data'][0]['created_time'] ) );

    }


    public function testDeletePost() {
        $sync = $this->buildFacebookServiceWithResponseSuccess();
        $this->assertTrue( $sync->deletePost( 1 ) );
    }


    public function testGetPostsEmpty() {

        $sync   = $this->buildFacebookServiceWithResponseEmpty();
        $result = $sync->getPosts();

        $this->assertTrue( $result === null );
    }

}