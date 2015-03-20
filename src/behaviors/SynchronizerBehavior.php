<?php

namespace ufocoder\SyncSocial\behaviors;

use Closure;
use Yii;
use yii\base\Behavior;
use yii\base\Exception;
use yii\db\ActiveRecord;
use ufocoder\SyncSocial\traits\Synchonizer;

/**
 * Class SynchronizerBehavior
 * @package ufocoder\SyncSocial\behaviors
 */
class SynchronizerBehavior extends Behavior {

    use Synchonizer;

    /**
     * @var array
     */
    public $syncServices = [ ];

    /**
     * @var bool
     */
    protected $syncDelete = false;

    /**
     * @return Closure
     */
    public $canSyncActiveRecord;

    /**
     * @return array
     */
    public function events() {

        return [
            ActiveRecord::EVENT_AFTER_FIND   => 'afterFind',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
            ActiveRecord::EVENT_AFTER_INSERT => 'syncActiveRecord',
            ActiveRecord::EVENT_AFTER_UPDATE => 'syncActiveRecord'
        ];
    }

    /**
     * @param $event
     */
    public function afterFind( $event ) {
        $this->syncServices = [ ];
        foreach ( $event->sender->syncModel as $syncModel ) {
            $this->syncServices[] = $syncModel->service_name;
        }
    }

    /**
     * @param \yii\base\Event $event
     *
     * @throws Exception
     */
    public function syncActiveRecord( $event ) {

        $synchronizer = $this->getSynchonizer();

        $function = $this->canSyncActiveRecord;
        $flag     = true;
        $model    = $event->sender;

        if ( is_callable( $function ) && ( $function instanceof Closure ) ) {
            $flag = $function( $model );
        }

        if ($flag) {
            foreach ( $this->syncServices as $serviceName ) {
                $synchronizer->syncActiveRecord( $serviceName, $model );
            }
        }
    }

    /**
     * @param \yii\base\Event $event
     *
     * @throws Exception
     */
    public function afterDelete( $event ) {
        $synchronizer = $this->getSynchonizer();
        foreach ( $this->syncServices as $serviceName ) {
            $synchronizer->deleteSyncModel( $serviceName, $event->sender, $this->syncDelete );
        }
    }

}