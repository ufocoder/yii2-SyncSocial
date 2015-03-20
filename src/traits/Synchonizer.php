<?php

namespace ufocoder\SyncSocial\traits;

use Yii;
use yii\base\Exception;

/**
 * Trait Synchonizer
 *
 * @package ufocoder\SyncSocial\traits
 */
trait Synchonizer {

    /**
     * @var string
     */
    public $componentName = 'synchronizer';

    /**
     * @var \ufocoder\SyncSocial\components\Synchronizer
     */
    protected $synchronizer;

    /**
     * @return \ufocoder\SyncSocial\components\Synchronizer
     * @throws Exception
     */
    public function getSynchonizer() {

        if ( $this->synchronizer === null ) {

            $components = Yii::$app->getComponents();

            if ( ! isset( $components[ $this->componentName ] ) ) {
                throw new Exception( Yii::t( 'SyncSocial', 'SyncSocial Component is not configured!' ) );
            } else {
                $this->synchronizer = Yii::$app->{$this->componentName};
            }
        }

        return $this->synchronizer;
    }

}