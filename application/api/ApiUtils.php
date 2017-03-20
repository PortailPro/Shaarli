<?php
namespace Shaarli\Api;

use Shaarli\Base64Url;
use Shaarli\Api\Exceptions\ApiAuthorizationException;

/**
 * REST API utilities
 */
class ApiUtils
{
    /**
     * Validates a JWT token authenticity.
     *
     * @param string $token  JWT token extracted from the headers.
     * @param string $secret API secret set in the settings.
     *
     * @throws ApiAuthorizationException the token is not valid.
     */
    public static function validateJwtToken($token, $secret)
    {
        $parts = explode('.', $token);
        if (count($parts) != 3 || strlen($parts[0]) == 0 || strlen($parts[1]) == 0) {
            throw new ApiAuthorizationException('Malformed JWT token');
        }

        $genSign = Base64Url::encode(hash_hmac('sha512', $parts[0] .'.'. $parts[1], $secret, true));
        if ($parts[2] != $genSign) {
            throw new ApiAuthorizationException('Invalid JWT signature');
        }

        $header = json_decode(Base64Url::decode($parts[0]));
        if ($header === null) {
            throw new ApiAuthorizationException('Invalid JWT header');
        }

        $payload = json_decode(Base64Url::decode($parts[1]));
        if ($payload === null) {
            throw new ApiAuthorizationException('Invalid JWT payload');
        }

        if (empty($payload->iat)
            || $payload->iat > time()
            || time() - $payload->iat > ApiMiddleware::$TOKEN_DURATION
        ) {
            throw new ApiAuthorizationException('Invalid JWT issued time');
        }
    }
}
