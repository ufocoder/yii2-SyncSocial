<?php

namespace ufocoder\SyncSocial\components\services;

use Yii;
use yii\base\Exception;
use ufocoder\SyncSocial\SyncService;
use yii\helpers\ArrayHelper;

/**
 * Class Vkontakte
 *
 * @package ufocoder\SyncSocial\components\services
 */
class Vkontakte extends SyncService {

    /**
     * @var \yii\authclient\clients\VKontakte
     */
    public $service;

    /**
     * @var string
     */
    public $serviceClass = '\yii\authclient\clients\VKontakte';

    /**
     * @var string
     */
    public $returnUrl = 'https://oauth.vk.com/blank.html';

    /**
     * @param $message
     * @param null $url
     *
     * @return array|void
     * @throws Exception
     */
    public function publishPost( $message, $url = null ) {

        $userAttributes = $this->service->getUserAttributes();

        $postResponse = $this->service->api( 'wall.post', 'GET', [
            'message' => $message
        ] );

        $getResponse = $this->service->api( 'wall.getById', 'GET', [
            'posts' => $userAttributes['id'] . '_' . $postResponse['response']['post_id']
        ] );

        return $this->parsePublishPost( $getResponse, 'response.0', [
            'service_id_author' => 'from_id',
            'service_id_post'   => 'id',
            'time_created'      => 'date'
        ] );
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function deletePost( $id ) {

        $userAttributes = $this->service->getUserAttributes();

        $response = $this->service->api( 'wall.delete', 'GET', [
            'owner_id' => $userAttributes['id'],
            'post_id'  => $id
        ] );

        return $response['response'] == 1;
    }

    /**
     * @param integer $limit
     *
     * @return string
     */
    protected function getWallGetRequestUrl( $limit = null ) {

        $query = http_build_query( [
            'owner_id'   => ArrayHelper::getValue( $this->serviceSettings, 'options.owner_id' ),
            'from_group' => ArrayHelper::getValue( $this->serviceSettings, 'options.from_group' ),
            'limit'      => $limit
        ] );

        return 'wall.get' . ( ! empty( $query ) ? "?" . $query : null );
    }

    /**
     * @param int $limit
     *
     * @return array
     * @throws Exception
     */
    public function getPosts( $limit = 100 ) {

        return $this->parseGetPosts(
            $this->service->api( $this->getWallGetRequestUrl( $limit ), 'GET' ),
            'response',
            [
                'id',
                'text'
            ],
            [
                'service_id_author' => 'from_id',
                'service_id_post'   => 'id',
                'time_created'      => 'date',
                'content'           => 'text'
            ] );
    }

}