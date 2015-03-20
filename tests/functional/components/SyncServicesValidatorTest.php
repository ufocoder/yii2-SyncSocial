<?php

namespace tests\functional\components;

use Mockery;
use Yii;
use yii\codeception\TestCase;
use ufocoder\SyncSocial\components\SyncServicesValidator;
use tests\models\Record;

/**
 * Class SyncServicesValidator
 * @package tests\functional\components
 */
class SyncServicesValidatorTest extends TestCase {

    public $appConfig = '@tests/functional/_config.php';


    public function testEmptyClassModelInit() {

        $model               = new Record();
        $model->property     = 'property';
        $model->syncServices = [ 'non_exists_service', 'service_1' ];

        $validator = new SyncServicesValidator();
        $validator->validateAttribute( $model, 'non_exists_attribute' );
        $errors = $model->getErrors( 'non_exists_attribute' );
        $this->assertTrue( count( $errors ) === 0 );

        $validator->validateAttribute( $model, 'property' );
        $errors = $model->getErrors( 'property' );
        $this->assertTrue( in_array(Yii::t( 'SyncSocial', 'Attribute "{attribute}" must be array', [
            'attribute' => 'property'
        ] ), $errors) );


        $validator->validateAttribute( $model, 'syncServices' );
        $errors = $model->getErrors( 'syncServices' );
        $this->assertTrue( in_array( Yii::t( 'SyncSocial', 'Service list has wrong value' ), $errors ) );
    }

}