<?php

declare(strict_types=1);

namespace App\Authentication;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * https://sergeyzhuk.me/2019/04/22/restful-api-with-reactphp-jwt-auth/
 */
final class JwtEncoder
{
    private $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function encode(array $payload): string
    {
        return JWT::encode($payload, $this->key, 'HS256');
    }

    public function decode(string $jwt): array
    {
        JWT::$leeway = 60;
        $decoded = JWT::decode($jwt, new Key($this->key, 'HS256'));
        return (array)$decoded;
    }
}
