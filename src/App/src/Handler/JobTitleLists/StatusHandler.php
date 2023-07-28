<?php

declare(strict_types=1);

namespace App\Handler\JobTitleLists;

use Mezzio\Authentication\UserInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StatusHandler implements RequestHandlerInterface
{
    public function __construct(StorageInterface $cache) 
    {
        $this->cache = $cache;     
    }
    
     /**
     * @OA\Get(
     *   path="/jobtitlelists/status",
     *   tags={"JobTitle Lists"},
     *   summary="Get excel file import status",
     *   operationId="jobTitleLists_importStatus",
     *
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad request, returns to validation errors"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserInterface::class);
        $fileKey = CACHE_TMP_FILE_KEY.$user->getId().'_status2';
        $result = $this->cache->getItem($fileKey);
        
        $error = null;
        $status = false;
        if (empty($result['status'])) {
            $status = false;
        } else if ($result['status']) {
            $status = true;
        }
        return new JsonResponse(
            [
                'data' => [
                    'status' => $status, 
                    'error' => empty($result['error']) ? null : $result['error'],
                ]
            ],
            200
        );
    }
}

