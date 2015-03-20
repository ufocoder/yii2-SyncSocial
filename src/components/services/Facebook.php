<?php

namespace ufocoder\SyncSocial\components\services;

use ufocoder\SyncSocial\SyncService;
use yii\helpers\ArrayHelper;

/**
 * Class Facebook
 *
 * @package ufocoder\SyncSocial\components\services
 */
class Facebook extends SyncService {

    /**
     * @var \yii\authclient\clients\Facebook
     */
    public $service;

    /**
     * @var string
     */
    public $serviceClass = '\yii\authclient\clients\Facebook';

    /**
     * @param $message
     * @param null $url
     *
     * @return array
     * @throws \yii\base\Exception
     */
    public function publishPost( $message, $url = null ) {

        $response = $this->service->api( 'me/feed', 'POST', [
            'message' => $message,
        ] );

        if ( ! isset( $response['id'] ) ) {
            return [ ];
        }

        return $this->parsePublishPost(
            $this->service->api( $response['id'], 'GET' ),
            'id',
            [
                'service_id_author' => 'from.id',
                'service_id_post'   => 'id',
                'time_created'      => 'created_time'
            ]
        );
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function deletePost( $id ) {

        $response = $this->service->api( $id, 'DELETE' );

        return ! empty( $response['success'] );
    }

    /**
     * @param int $limit
     *
     * @return array
     */
    public function getPosts( $limit = 100 ) {

        return $this->parseGetPosts(
            $this->service->api( 'me/feed', 'GET' ),
            'data', [
                'id',
                'message'
            ], [
                'service_id_author' => 'from.id',
                'service_id_post'   => 'id',
                'time_created'      => 'created_time',
                'content'           => 'message'
            ] );
    }
}