<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class NotFoundResponseGenerator
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $server  = $request->getServerParams();
        $http    = empty($server['HTTPS']) ? 'http://' : 'https://';
        $host    = empty($server['HTTP_HOST']) ? 'localhost' : $server['HTTP_HOST'];
        $httpUri = $http.$host;
        $json = [
            "title" => 'Not Found',
            "type" => "https://httpstatus.es/404",
            "status" => 404,
            "error" => sprintf('Cannot %s %s', $request->getMethod(), $httpUri),
        ];
        $response = $response->withHeader('Access-Control-Allow-Origin', '*');
        $response = $response->withHeader('Access-Control-Allow-Headers', '*');
        $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        $response = $response->withHeader('Access-Control-Expose-Headers', 'Token-Expired');
        $response = $response->withHeader('Access-Control-Max-Age', '3600');

        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->withStatus(404);
        $response->getBody()->write(json_encode($json));
        return $response;
    }
}
