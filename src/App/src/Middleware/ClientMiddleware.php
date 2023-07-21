<?php

declare(strict_types=1);

namespace App\Middleware;

use function define, current, str_pad;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Oloma\Php\Exception\BodyDecodeException;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\JsonResponse;
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
        $method  = $request->getMethod();

        define('HTTP_METHOD', $method);
        define('CLIENT_ID', '00001');

        // $langId = $row['lang_id']; // fallback language 
        // if (! empty($headers['accept-language'][0]) && in_array((string)$headers['accept-language'][0], $this->acceptedLanguages)) {
        //     $langId = (string)$headers['accept-language'][0];
        // }
        define('LANG_ID', 'tr');
        $contentType = empty($headers['content-type'][0]) ? null : current($headers['content-type']);
        // 
        // Json content type
        // 
        if ($contentType && strpos($contentType, 'application/json') === 0) {
            $jsonErrors = array(
                JSON_ERROR_DEPTH => 'Azami yığın boyutu aşıldı',
                JSON_ERROR_CTRL_CHAR => 'Kontrol karakteri hatası, muhtemelen yanlış şifrelenmiş',
                JSON_ERROR_SYNTAX => 'Sözdizimi hatası',
            );
            $contentBody = $request->getBody()->getContents();
            $parsedContent = json_decode($contentBody, true);
            $lastError = json_last_error();
            if (! empty($jsonErrors[$lastError])) {
                 throw new BodyDecodeException($jsonErrors[$lastError]);
            }
            $request = $request->withParsedBody($parsedContent);
        }
        // Set system language
        // 
        $this->translator->setLocale('tr');

        return $handler->handle($request);
    }
}
