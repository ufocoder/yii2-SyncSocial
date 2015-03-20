<?php

namespace tests\unit\components\services;

use Mockery;
use yii\codeception\TestCase;
use ufocoder\SyncSocial\components\services\Twitter;


/**
 * Class TwitterTest
 */
class TwitterTest extends TestCase {

    public $appConfig = '@tests/unit/_config.php';

    /**
     * @return array
     */
    protected function getResponseFeedList() {
        return [
            [
                'id'         => 1,
                'user'       => [
                    'id' => 2
                ],
                'text'       => 'test',
                'created_at' => 'Thu Oct 23 07:00:00 +0000 2014'
            ]
        ];
    }


    /**
     * @return array
     */
    protected function getResponseFeedItem() {
        return [
            'id'         => 1,
            'user'       => [
                'id' => 2
            ],
            'created_at' => 'Thu Oct 23 07:00:00 +0000 2014'
        ];
    }

    /**
     * @return \ufocoder\SyncSocial\components\services\Twitter
     */
    protected function buildTwitterServiceWithResponseSuccess() {

        $sync = new Twitter();

        $mock = Mockery::mock( $sync->service );
        $mock->shouldReceive( 'api' )
             ->andReturnUsing( function ( $apiSubUrl, $method = 'GET', array $params = [ ], array $headers = [ ] ) {
                 if ( $apiSubUrl == 'statuses/update.json' && $method == 'POST' ) {
                     return $this->getResponseFeedItem();
                 } elseif ( $apiSubUrl == 'statuses/user_timeline.json' && $method == 'GET' ) {
                     return $this->getResponseFeedList();
                 } elseif ( $apiSubUrl == 'statuses/destroy/1.json' && $method == 'POST' ) {
                     return $this->getResponseFeedItem();
                 }
             } );

        $sync->service = $mock;


        return $sync;
    }


    /**
     * @return \ufocoder\SyncSocial\components\services\Twitter
     */
    protected function buildTwitterServiceWithResponseEmpty() {

        $sync = new Twitter();

        $mock = Mockery::mock( $sync->service );
        $mock->shouldReceive( 'api' )
             ->andReturnUsing( function ( $apiSubUrl, $method = 'GET', array $params = [ ], array $headers = [ ] ) {
                 return [ ];
             } );

        $sync->service = $mock;

        return $sync;
    }


    public function testPublishPostSuccess() {

        $sync = $this->buildTwitterServiceWithResponseSuccess();

        $response = $this->getResponseFeedItem();
        $result   = $sync->publishPost( 'message' );

        $this->assertTrue( $result['service_id_post'] == $response['id'] );
        $this->assertTrue( $result['service_id_author'] == $response['user']['id'] );
        $this->assertTrue( $result['time_created'] == strtotime( $response['created_at'] ) );

    }


    public function testPublishPostEmpty() {

        $sync   = $this->buildTwitterServiceWithResponseEmpty();
        $result = $sync->publishPost( 'message' );

        $this->assertTrue( $result === [ ] );

    }


    public function testGetPostsSuccess() {

        $sync = $this->buildTwitterServiceWithResponseSuccess();

        $response = $this->getResponseFeedList();
        $result   = $sync->getPosts();

        $this->assertTrue( $result[0]['service_id_post'] == $response[0]['id'] );
        $this->assertTrue( $result[0]['service_id_author'] == $response[0]['user']['id'] );
        $this->assertTrue( $result[0]['content'] == $response[0]['text'] );
        $this->assertTrue( $result[0]['time_created'] == strtotime( $response[0]['created_at'] ) );

    }


    public function testDeletePost() {
        $sync = $this->buildTwitterServiceWithResponseSuccess();
        $this->assertTrue( $sync->deletePost( 1 ) );
    }


    public function testGetPostsEmpty() {

        $sync   = $this->buildTwitterServiceWithResponseEmpty();
        $result = $sync->getPosts();

        $this->assertTrue( $result === null );

    }

}