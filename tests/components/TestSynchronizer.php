<?php

namespace tests\components;

use ufocoder\SyncSocial\components\Synchronizer;

/**
 * Class MySynchronizer
 * @package tests\functional\components
 */
class TestSynchronizer extends Synchronizer {

    /**
     * @param $title
     * @param $value
     */
    public function someMethodThatUpdateService( $title, $value ) {
        $this->services[ $title ] = $value;
    }

}