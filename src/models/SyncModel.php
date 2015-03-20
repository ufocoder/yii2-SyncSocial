<?php

namespace ufocoder\SyncSocial\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "sync_model".
 *
 * @property integer $model_id
 * @property string $service_name
 * @property string $service_id_author
 * @property string $service_id_post
 * @property integer $time_created
 *
 */
class SyncModel extends ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%sync_model}}';
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
        return [
            'default' => [ 'model_id', 'service_name', 'service_id_author', 'service_id_post', 'time_created' ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [ [ 'model_id', 'service_id_author', 'service_id_post', 'time_created', 'service_name' ], 'required' ],
            [ [ 'model_id', 'time_created' ], 'integer' ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'model_id'          => Yii::t( 'SyncSocial', 'Sign' ),
            'service_name'      => Yii::t( 'SyncSocial', 'Service name' ),
            'service_id_author' => Yii::t( 'SyncSocial', 'ID service author' ),
            'service_id_post'   => Yii::t( 'SyncSocial', 'ID post' ),
            'time_created'      => Yii::t( 'SyncSocial', 'Time created' )
        ];
    }

}
