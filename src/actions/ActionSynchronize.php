<?php

namespace ufocoder\SyncSocial\actions;

use Yii;
use yii\base\Action;
use yii\base\Exception;
use yii\web\Application;
use ufocoder\SyncSocial\traits\Synchonizer;

/**
 * Class RunAction
 * @package ufocoder\SyncSocial\actions
 */
class ActionSynchronize extends Action {

    use Synchonizer;

    /**
     * @var
     */
    public $successUrl;

    /**
     * @var
     */
    public $failedUrl;

    /**
     * @var \yii\web\Controller
     */
    public $controller;

    /**
     * Set default redirect url
     */
    protected function initialRedirectUrl() {

        $defaultUrl = '/'. $this->controller->id . '/' . $this->controller->defaultAction;

        if (isset($this->controller->module->id) && !$this->controller->module instanceof Application) {
            $defaultUrl = '/' . $this->controller->module->id . $defaultUrl;
        }

        if ( empty( $this->successUrl ) ) {
            $this->successUrl = $defaultUrl;
        }

        if ( empty( $this->failedUrl ) ) {
            $this->failedUrl = $defaultUrl;
        }

    }

    /**
     * @param $flag
     * @param $successMessage
     * @param $failedMessage
     *
     * @return \yii\web\Response
     */
    protected function redirectWithMessages( $flag, $successMessage, $failedMessage ) {
        if ( $flag ) {
            Yii::$app->session->setFlash( 'success', $successMessage );
            return $this->controller->redirect( $this->successUrl );
        } else {
            Yii::$app->session->setFlash( 'warning', $failedMessage );
            return $this->controller->redirect( $this->failedUrl );
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function beforeRun() {

        $this->initialRedirectUrl();

        $this->synchronizer = $this->getSynchonizer();

        return parent::beforeRun();
    }
}