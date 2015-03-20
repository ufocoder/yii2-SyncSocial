<?php

namespace tests\models;

use yii\authclient\OAuth2;

/**
 * Class TestOAuth2
 */
class TestOAuth2 extends OAuth2
{
    protected function sendRequest($method, $url, array $params = [], array $headers = [])
    {
        return [
            'access_token' => md5(time()),
            'oauth_token_secret' => md5(time() + 1),
            'user_id' => '123',
            'expires_in' => time() + 3600
        ];
    }

}
