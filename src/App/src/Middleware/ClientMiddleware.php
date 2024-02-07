<?php

declare(strict_types=1);

namespace App\Middleware;

use Mezzio\Router\RouteResult;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Olobase\Mezzio\Exception\BodyDecodeException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class ClientMiddleware implements MiddlewareInterface
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
    
    private $config;
    private $translator;
    private $acceptedLanguages = array('tr','en');

    public function __construct(
        array $config,
        Translator $translator
    )
    {
        $this->config = $config;
        $this->translator = $translator;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $headers = $request->getHeaders();
        $server  = $request->getServerParams();
        $routeResult = $request->getAttribute(RouteResult::class, false);
        //
        // Sets primary id if it's exists
        // 
        $primaryKey = null;
        if ($routeResult) {
            $params = $routeResult->getMatchedParams();
            if (is_array($params) && ! empty($params)) {
                unset($params['middleware']);
                $paramArray = array_keys($params);
                $primaryKey  = empty($paramArray[0]) ? null : trim((string)$paramArray[0]);
            }  
        }
        //
        // Sets http method
        // 
        $method  = $request->getMethod();
        define('HTTP_METHOD', $method);
        // 
        // Sets language (don't change below the lines: front end 
        // application sends current language in http header)
        //
        $langId = "en"; // fallback language
        if (! empty($headers['x-client-locale'][0])) {
            $currentLocale = $headers['x-client-locale'][0];
            if ($currentLocale 
                && in_array(
                    $currentLocale,
                    $this->acceptedLanguages
                )
            ) {
                $langId = $currentLocale;
            }
        }
        define('LANG_ID', $langId);
        $this->translator->setLocale(LANG_ID);
        //
        // Parses & sets json content to request body
        //
        $get = array();
        $post = array();
        $contentType = empty($headers['content-type'][0]) ? null : current($headers['content-type']);
        if ($contentType 
            && strpos($contentType, 'application/json') === 0) {
            $contentBody = $request->getBody()->getContents();
            $post = json_decode($contentBody, true);
            $lastError = json_last_error();
            if ($lastError != JSON_ERROR_NONE) {
                throw new BodyDecodeException(
                    $this->translator->translate($lastError)
                );
            }
        }
        // Set $primaryKey as "id"
        //
        switch ($request->getMethod()) {
            case Self::METHOD_POST:
            case Self::METHOD_PUT:
            case Self::METHOD_OPTIONS:
                $post = empty($post) ? $request->getParsedBody() : $post;
                if ($primaryId = $request->getAttribute($primaryKey)) {
                    $post['id'] = $primaryId;
                }
                $request = $request->withParsedBody($post);
                break;
            // https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/HEAD
            //
            case Self::METHOD_PATCH:
            case Self::METHOD_HEAD:
            case Self::METHOD_GET:
            case Self::METHOD_TRACE:
            case Self::METHOD_CONNECT:
            case Self::METHOD_DELETE:
            case Self::METHOD_PROPFIND: // PROPFIND — used to retrieve properties, stored as XML, from a web resource.
                $get = $request->getQueryParams();
                if ($primaryId = $request->getAttribute($primaryKey)) {
                    $get['id'] = $primaryId;
                    $request = $request->withQueryParams($get);
                }
                break;
        }
        return $handler->handle($request);
    }
}
