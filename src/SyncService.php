<?php

namespace ufocoder\SyncSocial;

use Yii;
use yii\authclient\OAuth1;
use yii\authclient\OAuth2;
use yii\authclient\OAuthToken;
use yii\base\Exception;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Class SyncService
 *
 * @package ufocoder\SyncSocial
 */
class SyncService extends Object implements ISyncService {

	/**
	 * @var \yii\authclient\OAuth2|\yii\authclient\OAuth1
	 */
	public $service;

	/**
	 * @var string
	 */
	public $serviceClass;

	/**
	 * @var array
	 */
	public $serviceSettings = [];

	/**
	 * @var string
	 */
	public $returnUrl;

	/**
	 * @var string
	 */
	public $connectUrl;

	/**
	 * @var string
	 */
	public $flash = 'yii2-SyncSocial';

	/**
	 * @var bool
	 */
	protected static $readOnly = false;

	/**
	 * @throws Exception
	 */
	public function init() {

		if (!class_exists($this->serviceClass)) {
			throw new Exception(Yii::t('SyncSocial', 'Authclient Extension not support "{serviceName}" service', [
				'{serviceName}' => $this->serviceClass,
			]));
		}

		if (empty($this->returnUrl)) {
			$this->returnUrl = $this->connectUrl;
		}

		$this->service = new $this->serviceClass($this->serviceSettings);
		$this->service->setReturnUrl($this->returnUrl);
	}

	/**
	 * @return bool
	 */
	public function isReadOnly() {
		return static::$readOnly;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function getAuthorizationUri() {

		if ($this->service instanceof OAuth1) {
			$token = $this->service->fetchRequestToken();
			$authorizationUri = $this->service->buildAuthUrl($token);
		} elseif ($this->service instanceof OAuth2) {
			$authorizationUri = $this->service->buildAuthUrl();
		} else {
			throw new Exception(Yii::t('SyncSocial', 'SyncSocial is not support {serviceName}.', [
				'serviceName' => get_class($this->service),
			]));
		}

		return $authorizationUri;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->service->getName();
	}

	/**
	 * @return bool
	 * @throws Exception
	 * @throws Exception
	 */
	public function connect() {

		if ($this->service instanceof OAuth1) {
			try {
				$accessToken = $this->service->fetchAccessToken();

				return $this->isConnected($accessToken);
			} catch (Exception $e) {
				Yii::$app->session->setFlash($this->flash, $e->getMessage());

				return false;
			}

		} elseif ($this->service instanceof OAuth2) {
			try {
				$accessToken = $this->service->fetchAccessToken(Yii::$app->request->get('code', null));

				return $this->isConnected($accessToken);
			} catch (Exception $e) {
				Yii::$app->session->setFlash($this->flash, $e->getMessage());

				return false;
			}
		} else {
			throw new Exception(Yii::t('SyncSocial', 'SyncSocial is not support {serviceName}.', [
				'serviceName' => get_class($this->service),
			]));
		}
	}

	/**
	 * @param OAuthToken $accessToken
	 *
	 * @return bool
	 */
	public function isConnected(OAuthToken $accessToken = null) {

		/**
		 * @var $service \yii\authclient\BaseOAuth
		 */

		if ($accessToken === null) {
			$accessToken = $this->service->getAccessToken();
		}

		if (is_object($accessToken) && $accessToken->getIsValid()) {
			return true;
		} else {

			return false;
		}
	}

	/**
	 * @return boolean
	 */
	public function disconnect() {

		$this->service->setAccessToken(new OAuthToken());

		return !$this->isConnected();
	}

	/**
	 * @return array|null
	 */
	public function getPosts() {
		return null;
	}

	/**
	 * Check if array has keys (include path keys like a.b.c)
	 *
	 * @param array $array
	 * @param array $keys
	 *
	 * @return bool
	 */
	protected function isArrayHasKeys($array = [], $keys = []) {
		foreach ($keys as $key) {
			if (!ArrayHelper::getValue($array, $key, false)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param $response
	 * @param string $indexAttribute
	 * @param array $postAttributes
	 *
	 * @return array
	 */
	protected function parsePublishPost($response, $indexAttribute = null, $postAttributes = []) {
		if (ArrayHelper::getValue($response, $indexAttribute)) {
			$time_created = ArrayHelper::getValue($response, $postAttributes['time_created']);

			return [
				'service_id_author' => ArrayHelper::getValue($response, $postAttributes['service_id_author']),
				'service_id_post' => ArrayHelper::getValue($response, $postAttributes['service_id_post']),
				'time_created' => is_integer($time_created) ? $time_created : strtotime($time_created),
			];
		} else {
			return [];
		}

	}

	/**
	 * @param $response
	 * @param string $indexAttribute
	 * @param array $existsAttributes
	 * @param array $postAttributes
	 *
	 * @return array|null
	 */
	protected function parseGetPosts($response, $indexAttribute = null, $existsAttributes = [], $postAttributes = []) {

		$posts = $indexAttribute === null ? $response : ArrayHelper::getValue($response, $indexAttribute);

		if (empty($posts)) {
			return null;
		}

		$list = [];
		foreach ($posts as $post) {
			if ($this->isArrayHasKeys($post, $existsAttributes)) {

				$time_created = ArrayHelper::getValue($post, $postAttributes['time_created']);

				array_push($list, [
					'service_id_author' => ArrayHelper::getValue($post, $postAttributes['service_id_author']),
					'service_id_post' => ArrayHelper::getValue($post, $postAttributes['service_id_post']),
					'time_created' => is_integer($time_created) ? $time_created : strtotime($time_created),
					'content' => ArrayHelper::getValue($post, $postAttributes['content']),
				]);
			}
		}

		return $list;
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function deletePost($id) {
		return false;
	}

	/**
	 * @param $message
	 * @param null $url
	 *
	 * @return array|null
	 */
	public function publishPost($message, $url = null) {
		return null;
	}

}