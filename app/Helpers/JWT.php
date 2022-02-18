<?php

namespace App\Helpers;

use ReallySimpleJWT\Token;

class JWT {
    protected $secret;

    function __construct()
    {
        $this->secret  = env('JWT_ACCESS_TOKEN_SECRET');
    }

    public static function generate($uid) {
        $expired = time() + 6 * 3600; //Expired in 6 hours
        $payload = [
            'iat' => time(),
            'uid' => $uid,
            'exp' => $expired,
            'iss' => 'localhost'
        ];

        $secret = (new static)->secret;
        $token = Token::customPayload($payload, $secret);
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expired
        ];
    }

    public static function validate($token) {
        $secret = (new static)->secret;
        return Token::validateExpiration($token, $secret);
    }
}