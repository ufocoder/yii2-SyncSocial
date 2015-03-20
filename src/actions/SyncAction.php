<?php

namespace ufocoder\SyncSocial\actions;

use Yii;

/**
 * Class RunAction
 * @package ufocoder\SyncSocial\actions
 */
class SyncAction extends ActionSynchronize {

    /**
     * @param $service
     *
     * @return \yii\web\Response
     */
    public function run( $service ) {

        $flagConnect = $this->synchronizer->isConnected( $service );

        if ( $flagConnect ) {
            $resultSync = $this->synchronizer->syncService( $service );

            $successMessage = $resultSync['count'] == 0
                ? Yii::t( 'SyncSocial', 'Service was successfully synchronized! There\'s no new records!', [
                'count' => $resultSync['count']
            ] )
                : Yii::t( 'SyncSocial', 'Service was successfully synchronized! {count} record(s) was added!', [
                'count' => $resultSync['count']
            ] );

            return $this->redirectWithMessages(
                $resultSync['flag'],
                $successMessage,
                Yii::t( 'SyncSocial', 'There is a error in service synchronization' )
            );
        } else {
            Yii::$app->session->setFlash( 'warning', Yii::t( 'SyncSocial', 'Service is not connected' ) );

            return $this->controller->redirect( $this->failedUrl );
        }

    }
}