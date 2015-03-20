<?php

namespace ufocoder\SyncSocial\components;

use ufocoder\SyncSocial\traits\Synchonizer;
use Yii;
use yii\validators\Validator;

/**
 * Class CountryValidator
 *
 * @package ufocoder\SyncSocial\components
 */
class SyncServicesValidator extends Validator {

    use Synchonizer;

    /**
     * @param \yii\base\Model $model
     *
     * @param string $attribute
     */
    public function validateAttribute( $model, $attribute ) {

        $serviceList = $this->getSynchonizer()->getServiceList();

        if ( $model->hasProperty( $attribute ) ) {
            if ( ! is_array( $model->$attribute ) ) {
                $this->addError( $model, $attribute, Yii::t( 'SyncSocial', 'Attribute "{attribute}" must be array', [
                    'attribute' => $attribute
                ] ) );
            } else {
                foreach ( $model->$attribute as $service ) {
                    if ( ! in_array( $service, $serviceList ) ) {
                        $this->addError( $model, $attribute, Yii::t( 'SyncSocial', 'Service list has wrong value' ) );
                    }
                }
            }
        }

    }

}