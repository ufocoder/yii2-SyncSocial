<?php

namespace tests\functional\behaviors;

use Codeception\Codecept;
use Mockery;
use Yii;
use yii\codeception\TestCase;
use ufocoder\SyncSocial\SyncService;
use ufocoder\SyncSocial\behaviors\SynchronizerBehavior;
use tests\models\Record;

/**
 * Class SynchronizerTest
 * @package tests\functional\behaviors
 */
class SynchronizerTest extends TestCase {

    public $appConfig = '@tests/functional/_config.php';


    public function testСanSyncActiveRecord() {

        Yii::$app->setComponents([
            'synchronizer' => [
                'class' => 'ufocoder\SyncSocial\components\Synchronizer',
                'modelClass' => 'tests\models\Record'
            ]
        ]);

        $model = new Record();
        $model->attachBehavior( 'SyncSocialBehavior', [
            'class'               => SynchronizerBehavior::className(),
            'canSyncActiveRecord' => function ( $model ) {
                return false;
            }
        ] );

        $model->save();
        $syncModel = $model->syncModel;

        $this->assertTrue( empty( $syncModel ) );
    }


    public function testSyncService() {

        Yii::$app->setComponents([
            'synchronizer' => [
                'class' => 'ufocoder\SyncSocial\components\Synchronizer',
                'modelClass' => 'tests\models\Record'
            ]
        ]);

        $fakeSyncService = new SyncService( [
            'serviceClass' => '\tests\models\TestOAuth2',
            'returnUrl'    => 'http://my_own_site/returnUrl'
        ] );

        $mockSyncService = Mockery::mock( $fakeSyncService );
        $mockSyncService->shouldReceive( 'isConnected' )
                        ->andReturnUsing( function () {
                            return true;
                        } );

        $mockSyncService->shouldReceive( 'publishPost' )
                        ->andReturnUsing( function ( $message, $url = null ) {
                            return [
                                'service_id_author' => '2000',
                                'service_id_post'   => '1000',
                                'service_language'  => 'ru',
                                'time_created'      => strtotime( 'Thu Oct 23 07:00:00 +0000 2014' ),
                            ];
                        } );

        $mockSyncService->shouldReceive( 'deletePost' )
                        ->andReturnUsing( function ( $id ) {
                            return true;
                        } );

        Yii::$app->synchronizer->setService('fakeService', $mockSyncService );

        $model = new Record();
        $model->attachBehavior( 'SyncSocialBehavior', [
            'class'               => SynchronizerBehavior::className(),
            'canSyncActiveRecord' => function ( $model ) {
                return false;
            }
        ] );
        $model->syncServices = ['fakeService'];
        $model->save();

        $this->assertTrue( count($model->syncModel) === 1 );

        $find = Record::findOne($model->getPrimaryKey());
        $this->assertTrue( $model->getPrimaryKey() === $find->getPrimaryKey() );

        $count = $model->delete();
        $this->assertTrue( $count == 1 );
    }


}