<?php

namespace tests\unit\components;

use Yii;
use yii\codeception\TestCase;
use \ufocoder\SyncSocial\models\SyncModel;

/**
 * Class SyncServiceTest
 *
 * @package tests\unit\components
 */
class SyncModelTest extends TestCase {

    public $appConfig = '@tests/unit/_config.php';

    public function testModelReturnType() {

        $model = new SyncModel();

        $this->assertTrue( is_string( $model->tableName() ) );
        $this->assertTrue( is_array( $model->scenarios() ) );
        $this->assertTrue( is_array( $model->rules() ) );
        $this->assertTrue( is_array( $model->attributeLabels() ) );


    }
}