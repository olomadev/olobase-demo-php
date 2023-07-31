<?php

declare(strict_types=1);

namespace App\Middleware;

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
        // print_r($headers);
        //
        // Set http method
        // 
        $method  = $request->getMethod();
        define('HTTP_METHOD', $method);
        // 
        // Set language (Don't change below the lines: front end 
        // application sends current language in http accept header)
        //
        $langId = "en"; // fallback language
        if (! empty($headers['accept-language'][0])) {
            $exp = explode(",", $headers['accept-language'][0]);
            if (! empty($exp[0]) && in_array((string)$exp[0], $this->acceptedLanguages)) {
                $langId = (string)$exp[0];
            }
        }
        define('LANG_ID', $langId);
        $this->translator->setLocale(LANG_ID);
        //
        // Parse & set json content to request body
        //
        $contentType = empty($headers['content-type'][0]) ? null : current($headers['content-type']);
        if ($contentType 
            && strpos($contentType, 'application/json') === 0) {
            $contentBody = $request->getBody()->getContents();
            $parsedContent = json_decode($contentBody, true);
            $lastError = json_last_error();
            if ($lastError != JSON_ERROR_NONE) {
                throw new BodyDecodeException(
                    $this->translator->translate($lastError)
                );
            }
            $request = $request->withParsedBody($parsedContent);
        }
        return $handler->handle($request);
    }
}
