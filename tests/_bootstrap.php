<?php

error_reporting( E_ALL );
ini_set( 'display_errors', true );
ini_set( 'display_startup_errors', 1 );
ini_set( 'gd.jpeg_ignore_warning', true );

defined( 'YII_DEBUG' ) or define( 'YII_DEBUG', true );
defined( 'YII_ENV' ) or define( 'YII_ENV', 'test' );
defined( 'YII_ENABLE_ERROR_HANDLER' ) or define( 'YII_ENABLE_ERROR_HANDLER', false );
defined( 'YII_APP_BASE_PATH' ) or define( 'YII_APP_BASE_PATH', dirname( __DIR__ ) . '/app' );

defined( 'FRONTEND_ENTRY_URL' ) or define( 'FRONTEND_ENTRY_URL', parse_url( \Codeception\Configuration::config()['config']['test_entry_url'], PHP_URL_PATH ) );
defined( 'FRONTEND_ENTRY_FILE' ) or define( 'FRONTEND_ENTRY_FILE', YII_APP_BASE_PATH . '/index-test.php' );

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$_SERVER['SCRIPT_FILENAME'] = FRONTEND_ENTRY_FILE;
$_SERVER['SCRIPT_NAME']     = FRONTEND_ENTRY_URL;
$_SERVER['SERVER_NAME']     = parse_url( \Codeception\Configuration::config()['config']['test_entry_url'], PHP_URL_HOST );
$_SERVER['SERVER_PORT']     = parse_url( \Codeception\Configuration::config()['config']['test_entry_url'], PHP_URL_PORT ) ?: '80';

Yii::setAlias( '@tests', __DIR__ );