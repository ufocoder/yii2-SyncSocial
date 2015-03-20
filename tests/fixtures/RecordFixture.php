<?php

namespace tests\fixtures;

use yii\test\ActiveFixture;

/**
 * Class RecordFixture
 *
 * @package tests\fixtures
 */
class RecordFixture extends ActiveFixture {

    public $modelClass = '\tests\models\Record';

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init() {
        parent::init();
        $this->dataFile = __DIR__ . '/data/records.php';
    }

}