<?php

namespace ufocoder\SyncSocial\components\services;

use ufocoder\SyncSocial\SyncService;
use yii\helpers\ArrayHelper;

/**
 * Class Twitter
 *
 * @package ufocoder\SyncSocial\components\services
 */
class Twitter extends SyncService {

    /**
     * length limit for twitter publish message
     */
    const MESSAGE_LENGTH = 140;

    /**
     * @var \yii\authclient\clients\Twitter
     */
    public $service;

    /**
     * @var string
     */
    public $serviceClass = '\yii\authclient\clients\Twitter';

    /**
     * @param $message
     * @param null $url
     *
     * @return array
     * @throws \yii\base\Exception
     */
    public function publishPost( $message, $url = null ) {

        return $this->parsePublishPost(
            $this->service->api( 'statuses/update.json', 'POST', [
                'status' => $message
            ] ),
            'id',
            [
                'service_id_author' => 'user.id',
                'service_id_post'   => 'id',
                'time_created'      => 'created_at'
            ] );
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function deletePost( $id ) {
        $response = $this->service->api( 'statuses/destroy/' . $id . '.json', 'POST' );

        return isset( $response['id'] ) && $response['id'] == $id;
    }

    /**
     * @param int $limit
     *
     * @return array
     * @throws \yii\base\Exception
     */
    public function getPosts( $limit = 200 ) {

        return $this->parseGetPosts(
            $this->service->api( 'statuses/user_timeline.json' ),
            null, [
                'id',
                'text'
            ], [

                'service_id_author' => 'user.id',
                'service_id_post'   => 'id',
                'time_created'      => 'created_at',
                'content'           => 'text'
            ] );
    }
}