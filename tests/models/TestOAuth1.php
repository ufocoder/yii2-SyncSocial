<?php

namespace tests\models;

use yii\authclient\OAuth1;

/**
 * Class TestOAuth2
 */
class TestOAuth1 extends OAuth1
{
    protected function sendRequest($method, $url, array $params = [], array $headers = [])
    {
        return [
            'oauth_token' => md5(time()),
            'oauth_token_secret' => md5(time() + 1),
            'user_id' => '123',
            'expires_in' => time() + 3600
        ];
    }
}
