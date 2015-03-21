<?php

namespace ufocoder\SyncSocial\components;

use Closure;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\Exception;
use yii\db\ActiveRecord;
use ufocoder\SyncSocial\SyncService;
use ufocoder\SyncSocial\models\SyncModel;

/**
 * Class Synchronizer
 */
class Synchronizer extends Component {

    /**
     * Value of timeout
     */
    const DEFAULT_TIMEOUT = 18000;

    /**
     * @var array
     */
    protected $services = [ ];

    /**
     * @var array
     */
    public $settings = [ ];

    /**
     * @var Closure
     */
    public $connectUrl;

    /**
     * @var Closure
     */
    public $disconnectUrl;

    /**
     * @var Closure
     */
    public $syncUrl;

    /**
     * @var \yii\db\ActiveRecord
     */
    public $modelClass;

    /**
     * @var string
     */
    public $modelAttribute = 'content';

    /**
     * @var string
     */
    public $modelScenario = 'default';

    /**
     * @var Closure
     */
    public $absolutePostUrl = null;

    /**
     * @throws Exception
     */
    public function init() {

        $className = $this->modelClass;

        if ( ! class_exists( $className ) ) {
            throw new Exception( Yii::t( 'SyncSocial', 'Set model class to synchronization' ) );
        }

        if ( ! in_array( $this->modelAttribute, $className::getTableSchema()->columnNames ) ) {
            throw new Exception( Yii::t( 'SyncSocial', 'Set model attribute to synchronization' ) );
        }
    }

    /**
     * @return array
     */
    public function getServiceList() {
        return array_unique( array_merge(
            array_keys( $this->settings ),
            array_keys( $this->services )
        ) );
    }

    /**
     * @param $serviceName
     * @param $function
     *
     * @return mixed
     */
    protected function createUrlByFunction( $serviceName, Closure $function = null ) {
        if ( is_callable( $function ) && ( $function instanceof Closure ) ) {
            return $function( $serviceName );
        }
    }


    /**
     * @param $serviceName
     *
     * @return mixed
     */
    public function getConnectUrl( $serviceName ) {
        return $this->createUrlByFunction( $serviceName, $this->connectUrl );
    }

    /**
     * @param $serviceName
     *
     * @return mixed
     */
    public function getDisconnectUrl( $serviceName ) {
        return $this->createUrlByFunction( $serviceName, $this->disconnectUrl );
    }

    /**
     * @param $serviceName
     *
     * @return mixed
     */
    public function getSyncUrl( $serviceName ) {
        return $this->createUrlByFunction( $serviceName, $this->syncUrl );
    }

    /**
     * Create service synchronizer wrapper class
     *
     * @param $serviceName
     *
     * @return \yii\authclient\BaseOAuth
     * @throws Exception
     */
    protected function factorySyncService( $serviceName ) {

        $syncClass = '\\ufocoder\\SyncSocial\\components\\services\\' . ucfirst( $serviceName );

        if ( ! class_exists( $syncClass ) ) {
            throw new Exception( Yii::t( 'SyncSocial', 'SyncSocial Extension not support "{serviceName}" service', [
                'serviceName' => $serviceName
            ] ) );
        }

        return new $syncClass( [
            'serviceSettings' => isset( $this->settings[ $serviceName ] ) ? $this->settings[ $serviceName ] : [ ],
            'connectUrl'      => $this->getConnectUrl( $serviceName )
        ] );
    }

    /**
     * @param $serviceName
     *
     * @return SyncService
     * @throws Exception
     */
    public function getService( $serviceName ) {

        if ( empty( $this->services[ $serviceName ] ) ) {
            $this->services[ $serviceName ] = $this->factorySyncService( $serviceName );
        }

        if ( ! $this->services[ $serviceName ] instanceof SyncService ) {
            throw new Exception( Yii::t( 'SyncSocial', 'Component service must be instance of SyncService class' ) );
        }

        return $this->services[ $serviceName ];
    }

    /**
     * Set service
     *
     * @param $serviceName
     * @param SyncService $service
     */
    public function setService( $serviceName, SyncService $service ) {
        $this->services[ $serviceName ] = $service;
    }

    /**
     * @param null $serviceName
     *
     * @return string
     */
    public function getAuthorizationUri( $serviceName = null ) {
        return $this->getService( $serviceName )->getAuthorizationUri();
    }

    /**
     * @param null $serviceName
     *
     * @return boolean
     */
    public function connect( $serviceName = null ) {
        return $this->getService( $serviceName )->connect();
    }

    /**
     * Check if service is connected
     *
     * @param null $serviceName
     *
     * @return boolean
     */
    public function isConnected( $serviceName = null ) {
        return $this->getService( $serviceName )->isConnected();
    }

    /**
     * @param null $serviceName
     *
     * @return bool
     */
    public function disconnect( $serviceName = null ) {
        $service = $this->getService( $serviceName );
        $service->disconnect();

        return ! $service->isConnected();
    }

    /**
     * @param array $data
     *
     * @return ActiveRecord
     * @throws Exception
     */
    protected function createModel( array $data ) {

        /**
         * @var $model \yii\db\ActiveRecord
         */
        $model                          = new $this->modelClass;
        $model->scenario                = $this->modelScenario;
        $model->{$this->modelAttribute} = $data['content'];
        $model->save();

        if ( $model->hasErrors() ) {
            throw new InvalidConfigException( Yii::t( 'SyncSocial', 'Wrong model configuration for SyncSocial extension' ) );
        }

        return $model;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    protected function checkPostData( $data = array ()){
        return !empty($data['service_id_author'])
            && !empty($data['service_id_post']);
    }

    /**
     * @param SyncService $service
     * @param array $data
     *
     * @return bool
     */
    protected function isExistsSyncModelByData( SyncService $service, array $data ) {

        return SyncModel::find()->where( [
            'service_name'      => $service->getName(),
            'service_id_author' => $data['service_id_author'],
            'service_id_post'   => $data['service_id_post']
        ] )->exists();
    }

    /**
     * @param SyncService $service
     * @param ActiveRecord $model
     *
     * @return bool
     */
    protected function isExistsSyncModelByActiveRecord( SyncService $service, ActiveRecord $model ) {

        return SyncModel::find()->where( [
            'model_id'     => $model->getPrimaryKey(),
            'service_name' => $service->getName()
        ] )->exists();
    }

    /**
     * @param array $conditions
     *
     * @return array|null|ActiveRecord
     */
    protected function finSyncModel( $conditions = [ ] ) {
        return SyncModel::find()->where( $conditions )->one();
    }

    /**
     * @param SyncService $service
     * @param ActiveRecord $syncActiveRecord
     *
     * @return array|null|SyncModel
     */
    protected function getSyncModel( SyncService $service, ActiveRecord $syncActiveRecord ) {

        return $this->finSyncModel( [
            'service_name'    => $service->getName(),
            'service_id_post' => $syncActiveRecord->getPrimaryKey()
        ] );
    }

    /**
     * @param SyncService $service
     * @param ActiveRecord $model
     *
     * @return array|null|SyncModel
     */
    protected function getSyncActiveRecord( SyncService $service, ActiveRecord $model ) {

        return $this->finSyncModel( [
            'model_id'     => $model->getPrimaryKey(),
            'service_name' => $service->getName()
        ] );
    }

    /**
     * @param SyncService $service
     * @param ActiveRecord $model
     * @param array $data
     *
     * @return bool
     * @throws Exception
     */
    protected function createSyncModel( SyncService $service, ActiveRecord $model, array $data = array() ) {

        $syncModel             = new SyncModel();
        $syncModel->attributes = [
            'model_id'          => $model->getPrimaryKey(),
            'service_name'      => $service->getName(),
            'service_id_author' => $data['service_id_author'],
            'service_id_post'   => $data['service_id_post'],
            'time_created'      => ! empty( $data['time_created'] ) ? $data['time_created'] : time(),
        ];

        $flag = $syncModel->save();

        if ( $syncModel->hasErrors() ) {
            throw new InvalidConfigException( Yii::t( 'SyncSocial', 'Wrong sync model configuration for SyncSocial extension' ) );
        }

        return $flag;
    }

    /**
     * @param $serviceName
     * @param ActiveRecord $model
     * @param bool $flagSync
     *
     * @return bool
     */
    public function deleteSyncModel( $serviceName, ActiveRecord $model, $flagSync = false ) {

        $service          = $this->getService( $serviceName );
        $syncActiveRecord = $this->getSyncActiveRecord( $service, $model );

        if ( ! $syncActiveRecord instanceof SyncModel ) {
            return false;
        }

        $syncModel        = $this->getSyncModel( $service, $syncActiveRecord );
        $flagActiveRecord = (bool) $syncActiveRecord->delete();

        if ( $syncModel !== null && $flagActiveRecord ) {

            $flagModel = (bool) $syncModel->delete();

            if ( $flagModel && $flagSync ) {
                return $service->deletePost( $syncModel->service_id_post );
            } else {
                return $flagModel;
            }

        } else {
            return $flagActiveRecord;
        }
    }

    /**
     * @param null $serviceName
     *
     * @return bool
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function syncService( $serviceName = null ) {

        $flag  = false;
        $count = 0;

        $service  = $this->getService( $serviceName );
        $postData = $service->getPosts();

        if ( $postData !== null ) {

            $flag = true;
            foreach ( $postData as $data ) {
                if ( $this->checkPostData( $data ) && ! $this->isExistsSyncModelByData( $service, $data ) ) {
                    $model = $this->createModel( $data );
                    if ( $this->createSyncModel( $service, $model, $data ) ) {
                        $count ++;
                    }
                }
            }
        }

        return [
            'flag'  => $flag,
            'count' => $count
        ];
    }

    /**
     * Post message with URL to Social Network
     *
     * @param null $serviceName
     * @param \yii\db\ActiveRecord $model
     *
     * @return bool
     */
    public function syncActiveRecord( $serviceName = null, ActiveRecord $model ) {

        $service = $this->getService( $serviceName );

        if ( $service->isConnected() && ! $this->isExistsSyncModelByActiveRecord( $service, $model ) ) {

            $message  = $model->{$this->modelAttribute};
            $function = $this->absolutePostUrl;

            $url = null;
            if ( is_callable( $function ) && ( $function instanceof Closure ) ) {
                $url = $function( $serviceName, $model->getPrimaryKey() );
            }

            $publishData = $service->publishPost( $message, $url );

            return $this->checkPostData( $publishData ) && $this->createSyncModel( $service, $model, $publishData );

        } else {
            return false;
        }
    }

}