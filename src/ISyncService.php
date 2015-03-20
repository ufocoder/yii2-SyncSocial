<?php

namespace ufocoder\SyncSocial;

/**
 * Interface ISyncService
 *
 * @package ufocoder\SyncSocial
 */
interface ISyncService {

    /**
     * @param $message
     * @param null $url
     *
     *  Example of return response:
     *      return [
     *          'service_id_author' => '2000',
     *          'service_id_post'   => '1000',
     *          'service_language'  => 'ru',
     *          'time_created'      => strtotime( 'Thu Oct 23 07:00:00 +0000 2014' ),
     *      ];
     *
     * @return array
     */
    public function publishPost($message, $url = null);

    /**
     * @param $id
     *
     * @return bool
     */
    public function deletePost ( $id );

    /**
     *  Example of return response:
     *      return [
     *          [
     *              'service_id_author' => '2000',
     *              'service_id_post'   => '1000',
     *              'content'           => 'something',
     *              'time_created'      => 12345
     *          ]
     *     ];
     *
     * @return array
     */
    public function getPosts();

}