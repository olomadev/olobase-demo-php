<?php

declare(strict_types=1);

namespace App\Handler\JobTitleLists;

use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

class PreviewHandler implements RequestHandlerInterface
{
    public function __construct(SimpleCacheInterface $simpleCache) 
    {
        $this->simpleCache = $simpleCache;     
    }
    
     /**
     * @OA\Get(
     *   path="/jobtitlelists/preview",
     *   tags={"JobTitle Lists"},
     *   summary="Get excel file to preview results",
     *   operationId="jobTitleLists_previewResults",
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
        ini_set('memory_limit', '1024M');

        $user = $request->getAttribute(UserInterface::class);
        $fileKey = CACHE_TMP_FILE_KEY.$user->getId();
        $resultStatus = $this->simpleCache->get($fileKey.'_status');
        $result = $this->simpleCache->get($fileKey);

        $status = false;
        if (empty($resultStatus['status'])) {
            $status = false;
        } else if ($resultStatus['status']) {
            $status = true;
        }
        return new JsonResponse(
            [
                'data' => [
                    'status' => $status, 
                    'error' => empty($resultStatus['error']) ? null : $resultStatus['error'],
                    'validationError' => empty($resultStatus['validationError']) ? false : (boolean)$resultStatus['validationError'],
                    'results' => empty($result['data']) ? [] : $result['data'],
                ]
            ],
            200
        );
    }
}

