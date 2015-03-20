<?php

use yii\db\Schema;
use yii\db\Migration;

class m141023_180552_sync_record extends Migration {

    /**
     * @return boolean
     */
    public function safeUp() {

        $this->createTable( '{{%sync_model}}', [
            'model_id'          => Schema::TYPE_INTEGER. ' NOT NULL',
            'service_name'      => Schema::TYPE_STRING . '(64) NOT NULL',
            'service_id_author' => Schema::TYPE_STRING . '(128) DEFAULT NULL',
            'service_id_post'   => Schema::TYPE_STRING . '(128) DEFAULT NULL',
            'service_url_post'   => Schema::TYPE_STRING . '(512) DEFAULT NULL',
            'time_created'      => Schema::TYPE_INTEGER . ' DEFAULT NULL'
        ] );

        $this->addPrimaryKey('sync_model_pk', '{{%sync_model}}', ['model_id', 'service_name']);

        return true;
    }

    /**
     * @return boolean
     */
    public function safeDown() {

        $this->dropTable( '{{%sync_model}}' );

        return true;
    }

}
