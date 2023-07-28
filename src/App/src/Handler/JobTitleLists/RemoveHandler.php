<?php

declare(strict_types=1);

namespace App\Handler\JobTitleLists;

use Mezzio\Authentication\UserInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RemoveHandler implements RequestHandlerInterface
{
    public function __construct(StorageInterface $cache) 
    {
        $this->cache = $cache;     
    }
    
    /**
     * @OA\Delete(
     *   path="/jobtitlelists/remove",
     *   tags={"JobTitle Lists"},
     *   summary="Remove cached data",
     *   operationId="jobTitleLists_remove",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {   
        ini_set('memory_limit', '1024M');

        $user = $request->getAttribute(UserInterface::class);
        $fileKey = CACHE_TMP_FILE_KEY.$user->getId();

        $data = $this->cache->getItem($fileKey);
        if (! empty($data['fileId'])) {
            $tmpFile = PROJECT_ROOT."/data/tmp/".$data['fileId'].".xlsx";
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
        $this->cache->removeItem('jobtitlelist_parse');
        $this->cache->removeItem($fileKey);
        $fileKey = CACHE_TMP_FILE_KEY.$user->getId().'_status';
        $this->cache->removeItem($fileKey);

        return new JsonResponse([], 200);
    }
}
