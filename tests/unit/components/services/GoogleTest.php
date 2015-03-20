<?php

namespace tests\unit\components\services;

use Mockery;
use Yii;
use yii\codeception\TestCase;
use ufocoder\SyncSocial\components\services\Google;

/**
 * Class GoogleTest
 */
class GoogleTest extends TestCase {

    public $appConfig = '@tests/unit/_config.php';


    /**
     * @return array
     */
    protected function getResponseFeedList() {
        return [
            'items' => [
                [
                    'id'        => 1,
                    'actor'     => [
                        'id' => 2
                    ],
                    'object'    => [
                        'content' => 2
                    ],
                    'published' => 'Thu Oct 23 07:00:00 +0000 2014'
                ]
            ]
        ];
    }

    /**
     * @return \ufocoder\SyncSocial\components\services\Facebook
     */
    protected function buildFacebookServiceWithResponseSuccess() {

        $sync = new Google();

        $mock = Mockery::mock( $sync->service );
        $mock->shouldReceive( 'api' )
             ->andReturnUsing( function ( $apiSubUrl, $method = 'GET', array $params = [ ], array $headers = [ ] ) {
                 if ( $apiSubUrl == 'people/me/activities/public' and $method == 'GET' ) {
                     return $this->getResponseFeedList();
                 }
             } );

        $sync->service = $mock;

        return $sync;
    }


    public function testGetPostsSuccess() {

        $sync = $this->buildFacebookServiceWithResponseSuccess();

        $response = $this->getResponseFeedList();
        $result   = $sync->getPosts();

        $this->assertTrue( $result[0]['service_id_post'] == $response['items'][0]['id'] );
        $this->assertTrue( $result[0]['service_id_author'] == $response['items'][0]['actor']['id'] );
        $this->assertTrue( $result[0]['time_created'] == strtotime( $response['items'][0]['published'] ) );

    }


    public function testPublishPostException() {

        $sync = $this->buildFacebookServiceWithResponseSuccess();

        $this->setExpectedException( 'yii\base\Exception', Yii::t( 'SyncSocial', '{service} not support post publish', [
            'service' => $sync->getName()
        ] ) );

        $sync->publishPost( 'fake_message');

        $this->assertTrue( true );
    }


    public function testDeletePostException() {
        $sync = $this->buildFacebookServiceWithResponseSuccess();

        $this->setExpectedException( 'yii\base\Exception', Yii::t( 'SyncSocial', '{service} not support post delete', [
            'service' => $sync->getName()
        ] ) );

        $sync->deletePost(1);

        $this->assertTrue( true );
    }

    public function testReadOnly() {
        $sync = $this->buildFacebookServiceWithResponseSuccess();
        $this->assertTrue( $sync->isReadOnly() );
    }

}