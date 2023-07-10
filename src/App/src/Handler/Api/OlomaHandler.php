<?php

declare(strict_types=1);

namespace App\Handler\Api;

use ReflectionClass;
use ReflectionParameter;
use App\Middleware\NotFoundResponseGenerator;
use Laminas\Diactoros\Response;
use Mezzio\Exception\InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class OlomaHandler implements RequestHandlerInterface
{
    const METHOD_POST  = 'POST';
    const METHOD_PUT   = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_TRACE = 'TRACE';
    const METHOD_CONNECT = 'CONNECT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PROPFIND = 'PROPFIND';

    protected $config;
    
    /**
     * Return to request
     *
     * @return object
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Handler request
     *
     * @param  ServerRequestInterface $request request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $lowercaseMethod = strtolower($request->getMethod());
        $method = 'on'.ucfirst($lowercaseMethod);
        /**
         * Convert second segment to method
         *
         * GET /user/findById  => onGetFindById()
         */
        $exp = explode('/', trim($request->getUri()->getPath(), '/'));
        
        if (empty($exp[2])) {
            $method = $method.ucfirst($exp[1]);
        } else {
            $method = $method.ucfirst($exp[2]);
        }
        $reflection = new ReflectionClass($this);
        $handlerName = $reflection->getName();
        if ($reflection->hasMethod($method)) {
            $reflectionParameters = $reflection->getMethod($method)->getParameters();
            if (empty($reflectionParameters)) {
                return $this->$method();
            }
            $resolver = $this->resolveParameters($handlerName);
            $parameters = array_map($resolver, $reflectionParameters);
            return $this->dispatch($method, $parameters);
        }
        $notFoundResponse = new NotFoundResponseGenerator;
        return $notFoundResponse($request, new Response);
    }

    /**
     * Dispatch helper method
     *
     * @param  string $method     name
     * @param  array  $parameters params
     * @return ResponseInterface
     */
    public function dispatch($method = 'onGet', $parameters = [])
    {
        return $this->$method(...$parameters);
    }

    /**
     * Returns a callback for resolving a parameter from matched route arguments, including http raw body data.
     *
     * @param string $handlerName handler name
     * @return callable
     */
    private function resolveParameters($handlerName)
    {
        /**
         * @param ReflectionParameter $param
         * @return mixed
         * @throws ServiceNotFoundException If type-hinted parameter cannot be
         *   resolved to a argument name in the matched route.
         */
        return function (ReflectionParameter $param) use ($handlerName) {
            $parameterName = $param->getName();
            $request = $this->getRequest();

            $isArray = $param->getType() && $param->getType()->getName() === 'array';
            if ($isArray) {

                // https://tools.ietf.org/html/rfc7231
                //
                switch ($request->getMethod()) {
                    case Self::METHOD_POST:
                    case Self::METHOD_PUT:
                    case Self::METHOD_OPTIONS:
                        $parameterData = $request->getParsedBody();
                        break;
                    // https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/HEAD
                    //
                    case Self::METHOD_PATCH:
                    case Self::METHOD_HEAD:
                    case Self::METHOD_GET:
                    case Self::METHOD_TRACE:
                    case Self::METHOD_CONNECT:
                    case Self::METHOD_DELETE:
                    // PROPFIND — used to retrieve properties, stored as XML, from a web resource.
                    case Self::METHOD_PROPFIND:
                        $parameterData = $request->getQueryParams();
                        break;
                    default:
                        $parameterData = array();
                        break;
                }
                if (in_array($parameterName, [
                        'options',
                        'get',
                        'head',
                        'post',
                        'put',
                        'delete',
                        'trace',
                        'connect',
                        'patch',
                        'propfind'
                    ])) {
                    return $parameterData;
                }
                return [];  // default array
            }
            return $this->resolveRouteParameter($param, $handlerName);
        };
    }

    /**
     * Logic common to route parameter resolution.
     *
     * @param ReflectionParameter $param
     * @param string $handlerName class name
     * @return mixed
     * @throws ServiceNotFoundException If type-hinted parameter cannot be
     *   resolved to a argument name in the matched route.
     */
    private function resolveRouteParameter(ReflectionParameter $param, $handlerName)
    {
        $name = $param->getName();
        /**
         * Bind route arguments
         */
        if ($this->request->getAttribute($name)) {
            return $this->request->getAttribute($name);
        }
        if (! $param->isDefaultValueAvailable()) {
            throw new InvalidArgumentException(sprintf(
                'Unable to create service "%s"; unable to resolve value of route parameter "%s"',
                $handlerName,
                $name
            ));
        }
        return $param->getDefaultValue();
    }
}
