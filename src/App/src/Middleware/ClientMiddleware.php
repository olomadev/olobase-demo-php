<?php

declare(strict_types=1);

namespace App\Middleware;

use Mezzio\Router\RouteResult;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
use Oloma\Php\Exception\BodyDecodeException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class ClientMiddleware implements MiddlewareInterface
{
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
        if (! empty($headers['client-locale'][0])) {
            $currentLocale = $headers['client-locale'][0];
            if ($currentLocale && in_array($currentLocale, $this->acceptedLanguages)) {
                $langId = $currentLocale;
            }
        }
        define('LANG_ID', $langId);
        $this->translator->setLocale(LANG_ID);
        //
        // Parses & sets json content to request body
        //
        $contentType = empty($headers['content-type'][0]) ? null : current($headers['content-type']);
        if ($contentType 
            && strpos($contentType, 'application/json') === 0) {
            $contentBody = $request->getBody()->getContents();
            $post = json_decode($contentBody, true);
            if ($primaryId = $request->getAttribute($primaryKey)) {
                $post['id'] = $primaryId;
            }
            $lastError = json_last_error();
            if ($lastError != JSON_ERROR_NONE) {
                throw new BodyDecodeException(
                    $this->translator->translate($lastError)
                );
            }
            $request = $request->withParsedBody($post);
        }
        return $handler->handle($request);
    }
}
