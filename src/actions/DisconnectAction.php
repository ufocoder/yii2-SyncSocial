<?php

namespace ufocoder\SyncSocial\actions;

use Yii;

/**
 * Class ConnectAction
 * @package ufocoder\SyncSocial\actions
 */
class DisconnectAction extends ActionSynchronize {

    /**
     * @param $service
     *
     * @return \yii\web\Response
     */
    public function run( $service ) {

        return $this->redirectWithMessages(
            $this->synchronizer->disconnect( $service ),
            Yii::t( 'SyncSocial', 'Service was successfully disconnected' ),
            Yii::t( 'SyncSocial', 'There is error in disconnection' )
        );
    }

}