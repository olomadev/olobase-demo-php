<?php

declare(strict_types=1);

namespace App\Handler\JobTitleLists;

use Mezzio\Authentication\UserInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ResetHandler implements RequestHandlerInterface
{
    public function __construct(StorageInterface $cache) 
    {
        $this->cache = $cache;     
    }
    
    /**
     * @OA\Delete(
     *   path="/jobtitlelists/reset",
     *   tags={"JobTitle Lists"},
     *   summary="Reset all statuses",
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
        $user = $this->request->getAttribute(UserInterface::class);
        $fileKey = CACHE_TMP_FILE_KEY.$user->getId();
        $this->cache->removeItem($fileKey);
        $this->cache->removeItem($fileKey.'_status');
        $this->cache->removeItem($fileKey.'_status2');
        $this->cache->removeItem("jobtitlelist_save");
    
        return new JsonResponse([], 200);
    }
}
