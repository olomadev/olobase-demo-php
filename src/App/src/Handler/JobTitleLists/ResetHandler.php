<?php

declare(strict_types=1);

namespace App\Handler\JobTitleLists;

use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

class ResetHandler implements RequestHandlerInterface
{
    public function __construct(SimpleCacheInterface $simpleCache) 
    {
        $this->simpleCache = $simpleCache;     
    }
    
    /**
     * @OA\Delete(
     *   path="/jobtitlelists/reset",
     *   tags={"JobTitle Lists"},
     *   summary="Reset all statuses to restart operation",
     *   operationId="jobTitleLists_reset",
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
        $fileKey = CACHE_TMP_FILE_KEY.$user->getId();
        $this->simpleCache->delete($fileKey);
        $this->simpleCache->delete($fileKey.'_status');
        $this->simpleCache->delete($fileKey.'_status2');
        $this->simpleCache->delete("jobtitlelist_save");
    
        return new JsonResponse([], 200);
    }
}
