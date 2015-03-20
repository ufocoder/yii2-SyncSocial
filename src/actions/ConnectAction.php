<?php

namespace ufocoder\SyncSocial\actions;

use Yii;

/**
 * Class ConnectAction
 * @package ufocoder\SyncSocial\actions
 */
class ConnectAction extends ActionSynchronize {

    /**
     * @param $service
     *
     * @return \yii\web\Response
     */
    public function run( $service ) {

        return $this->redirectWithMessages(
            $this->synchronizer->connect( $service ),
            Yii::t( 'SyncSocial', 'Service was successfully connected' ),
            Yii::t( 'SyncSocial', 'Service could not be connected' )
        );

    }
}