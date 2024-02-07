<?php

declare(strict_types=1);

namespace App\Handler\JobTitleLists;

use App\Filter\JobTitleLists\FileUploadFilter;
use Predis\ClientInterface as Predis;
use Olobase\Mezzio\DataManagerInterface;
use Mezzio\Authentication\UserInterface;
use Olobase\Mezzio\Error\ErrorWrapperInterface as Error;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UploadHandler implements RequestHandlerInterface
{
    public function __construct(
        Predis $predis,
        FileUploadFilter $filter,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->predis = $predis;
        $this->error = $error;
    }
    
     /**
     * @OA\Post(
     *   path="/jobtitlelists/upload",
     *   tags={"JobTitle Lists"},
     *   summary="Upload excel file",
     *   operationId="jobTitleLists_upload",
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
        $this->filter->setInputData($_FILES);
        if ($this->filter->isValid()) {
            $tmpFilename = createGuid();
            $user = $request->getAttribute(UserInterface::class);
            $fileKey = CACHE_TMP_FILE_KEY.$user->getId();

            // Handle Psr7 upload with Laminas Diactoros
            $request = $request->getUploadedFiles();
            $file = $request['file'];
            $code = $file->getError();

            if ($code == UPLOAD_ERR_OK) {
                // move file to temp directory
                //
                $ext = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
                $file->moveTo(PROJECT_ROOT."/data/tmp/".$tmpFilename.".".$ext);
                // write temp file meta data to cache
                // 
                $data = [
                    'userId'   => $user->getId(),
                    'fileId'   => $tmpFilename,
                    'fileExt'  => $ext,
                    'fileKey'  => $fileKey,
                    'fileName' => $file->getClientFilename(),
                    'fileType' => $file->getClientMediaType(),
                    'fileSize' => $file->getSize(),
                    'status'   => false,
                    'data'     => null,
                    'error'    => null,
                    'env'      => getenv('APP_ENV'),
                    'locale'   => LANG_ID,
                ];
                // send to queue
                // https://www.vultr.com/docs/implement-redis-queue-and-worker-with-php-on-ubuntu-20-04/
                // 
                $this->predis->rpush("jobtitlelist_parse", json_encode($data));
                $this->predis->expire("jobtitlelist_save", 300);

                return new JsonResponse([], 200); 
            } else {
                return new JsonResponse(
                    [
                        'data' => ['error' => $this->error->getUploadError($code)]
                    ],
                    400
                );
            }
        } else {
            return new JsonResponse($this->error->getMessages($this->filter), 400);
        }
        return new JsonResponse([], 200); 
    }
}
