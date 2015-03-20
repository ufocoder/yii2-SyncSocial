<?php

namespace ufocoder\SyncSocial\components\services;

use Yii;
use ufocoder\SyncSocial\SyncService;
use yii\base\Exception;

/**
 * Class Google
 *
 * @package ufocoder\SyncSocial\components\services
 */
class Google extends SyncService {

    /**
     * @var \yii\authclient\clients\GoogleOAuth
     */
    public $service;

    /**
     * @var string
     */
    public $serviceClass = '\yii\authclient\clients\GoogleOAuth';

    /**
     * @var bool
     */
    static $readOnly = true;

    /**
     * @param $message
     * @param null $url
     *
     * @return array|void
     * @throws Exception
     */
    public function publishPost( $message, $url = null ) {
        throw new Exception( Yii::t( 'SyncSocial', '{service} not support post publish', [
            'service' => $this->service->getName()
        ] ) );

    }

    /**
     * @param $id
     *
     * @return bool|void
     * @throws Exception
     */
    public function deletePost( $id ) {
        throw new Exception( Yii::t( 'SyncSocial', '{service} not support post delete', [
            'service' => $this->service->getName()
        ] ) );
    }

    /**
     * @param int $limit
     *
     * @return array
     */
    public function getPosts( $limit = 100 ) {

        return $this->parseGetPosts(
            $this->service->api( 'people/me/activities/public', 'GET' ),
            'items',
            [
                'id',
                'object.content'
            ],
            [
                'service_id_author' => 'actor.id',
                'service_id_post'   => 'id',
                'time_created'      => 'published',
                'content'           => 'object.content'
            ]
        );
    }

}