<?php

declare(strict_types=1);

namespace App\Handler\Common\Stream;

use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

/**
 * An "EventSource" instance opens a persistent connection to an HTTP server, 
 * which sends events in text/event-stream format. 
 * The connection remains open until closed by calling EventSource.close().
 * 
 * https://developer.mozilla.org/en-US/docs/Web/API/EventSource
 */
class EventsHandler implements RequestHandlerInterface
{
    public function __construct(private SimpleCacheInterface $simpleCache)
    {
        $this->simpleCache = $simpleCache;
    }

    /**
     * @OA\Get(
     *   path="/stream/events",
     *   tags={"Common"},
     *   summary="Server-Sent Events",
     *   operationId="stream_events",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation"
     *   ),
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Which modules use event stream
        // 
        // 1 - Job title lists
        // 2 - x ..

        $get = $request->getQueryParams();
        $error = "";
        $status = 0;
        $progress = 0;
        if (empty($get['userId'])) {
            $error = "User id cannot be empty";
        }
        if (empty($get['route'])) {
            $error = "Event route cannot be empty";
        }
        $route = trim($get['route']);
        $userId = trim($get['userId']);
        $time = date('r');
        $serverTime = time();
        $headers = [
            'Content-Type' => ['text/event-stream'],
            'Cache-Control' => ['no-cache'],
        ];
        if (empty($error)) {
            $processData = false;
            switch ($route) {
                case 'upload':
                    $processData = $this->simpleCache->get(CACHE_TMP_FILE_KEY.$userId.'_status');
                    break;
                case 'list':
                    $processData = $this->simpleCache->get(CACHE_TMP_FILE_KEY.$userId.'_status2');
                    break;
            }
            if ($processData && array_key_exists("status", $processData)) {
                $status = $processData['status'];    
            }
        }
        // debug:
        // file_put_contents(PROJECT_ROOT."/data/tmp/error-output.txt", print_r($processData, true), FILE_APPEND | LOCK_EX);

        //
        // encode data
        //
        $data = json_encode(
            [
                "status" => (int)$status,
                "error" => $error,
            ]
        );
        $text = "id: $serverTime\n";
        $text.= "data: {$data}\n\n";

        return new TextResponse($text, 200, $headers);
    }
}