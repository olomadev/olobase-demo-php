<?php

declare(strict_types=1);

namespace App\Middleware;

use Throwable;
use Psr\Container\ContainerInterface;
use App\Exception\ConsultationSessionException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Mezzio\Cors\Configuration\ConfigurationInterface;

class ErrorResponseGenerator
{
    protected $config;
    protected $container;

    public function __construct(array $config, ContainerInterface $container)
    {
        $this->config = $config;
        $this->container = $container;
    }

    public function __invoke(Throwable $e, ServerRequestInterface $request, ResponseInterface $response)
    {
        $data = $e->getTrace();
        $trace = array_map(
            function ($a) {
                    if (isset($a['file'])) {
                        $a['file'] = str_replace(PROJECT_ROOT, '', $a['file']);
                    }
                    return $a;
                },
            $data
        );        
        $json = [
            'title' => get_class($e),
            'type' => 'https://httpstatus.es/400',
            'status' => 400,
            'file' => str_replace(PROJECT_ROOT, '', $e->getFile()),
            'line' => $e->getLine(),
            'error' => $e->getMessage(),
        ];
        if (getenv('APP_ENV') == 'local') {
            $json['trace'] = $trace;
        }
        $response = $response->withHeader('Access-Control-Expose-Headers', 'Token-Expired');
        $response = $response->withHeader('Access-Control-Max-Age', '3600');
        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->withStatus(400);
        $response->getBody()->write(json_encode($json));

        // Error mailer
        // 
        // if (getenv('APP_ENV') == 'prod') {
        //     $class = get_class($e);
        //     if (false === strpos($class, 'App\Exception') 
        //         AND false === strpos($class, 'Laminas\Validator\Exception')) {
        //         $errorMailer = $this->container->get(ErrorMailer::class);
        //         $errorMailer->setEnv("production");
        //         $errorMailer->setException($e);
        //         $errorMailer->setUri($request->getUri()->getPath());
        //         $errorMailer->setServerParams($request->getServerParams());
        //         $errorMailer->send();
        //     }
        // }
        return $response;
    }
}
