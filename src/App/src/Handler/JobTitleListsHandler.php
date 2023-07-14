<?php

declare(strict_types=1);

namespace App\Handler\Api;

use function createGuid;
use function paginatorJsonDecode;

use App\Utils\Error;
use App\Utils\UploadError;
use App\Utils\DataManager;
use App\Model\JobTitleListModel;
use App\Entity\JobTitleListEntity;
use App\Schema\JobTitleListSave;
use App\Filter\JobTitleListImportFilter;
use App\Filter\JobTitleListSaveFilter;
use App\Filter\FileUploadFilter;
use Mezzio\Authentication\UserInterface;
use Predis\ClientInterface as Predis;
use Psr\SimpleCache\CacheInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class JobTitleListsHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        JobTitleListModel $jobTitleListModel,
        Predis $predis,
        DataManager $dataManager,
        StorageInterface $cache,
        Error $error
    )
    {
        $this->filter = $filter;        
        $this->translator = $translator;
        $this->jobTitleListModel = $jobTitleListModel;
        $this->predis = $predis;
        $this->cache = $cache;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/jobtitlelists/findAll",
     *   tags={"JobTitle Lists"},
     *   summary="Find all jobTitles",
     *   operationId="jobTitleLists_findAll",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/CommonFindAllResultVM"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function onGetFindAll(array $get)
    {
        $data = $this->jobTitleListModel->findOptions($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/jobtitlelists/findAllByPaging",
     *   tags={"JobTitle Lists"},
     *   summary="Find all jobtitle lists by pagination",
     *   operationId="jobTitleLists_findAllByPaging",
     *
     *   @OA\Parameter(
     *       name="q",
     *       in="query",
     *       required=false,
     *       description="Search string",
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       name="_page",
     *       in="query",
     *       required=false,
     *       description="Page number",
     *       @OA\Schema(
     *           type="integer",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       name="_perPage",
     *       in="query",
     *       required=false,
     *       description="Per page",
     *       @OA\Schema(
     *           type="integer",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       name="_sort",
     *       in="query",
     *       required=false,
     *       description="Order items",
     *       @OA\Schema(
     *           type="array",
     *           @OA\Items()
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/JobTitleListFindAllByPageResultVM"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function onGetFindAllByPaging(array $get)
    {
        $page = empty($get['_page']) ? 1 : (int)$get['_page'];
        $perPage = empty($get['_perPage']) ? 5 : (int)$get['_perPage'];

        // queries:
        // q=ersin+güvenç&JobTitle ListshortName=test&_sort=taxOffice&_order=desc&_sort=taxNumber&_order=asc

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->jobTitleListModel->findAllByPaging($get);

        $page = ($page < 1) ? 1 : $page;
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($perPage);

        return new JsonResponse([
            'page' => $paginator->getCurrentPageNumber(),
            'perPage' => $paginator->getItemCountPerPage(),
            'totalPages' => $paginator->count(),
            'totalItems' => $paginator->getTotalItemCount(),
            'data' => paginatorJsonDecode($paginator->getCurrentItems()),
        ]);
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
    public function onPostUpload()
    {
        $inputFilter = $this->filter->get(FileUploadFilter::class);
        $inputFilter->setInputData($_FILES);

        if ($inputFilter->isValid()) {
            $tmpFilename = createGuid();
            $user = $this->request->getAttribute(UserInterface::class);
            $fileKey = CACHE_TMP_FILE_KEY.$user->getId();

            // Handle Psr7 upload with Laminas Diactoros
            $request = $this->request->getUploadedFiles();
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
                    'clientId' => CLIENT_ID,
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
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse([], 200);
    }

     /**
     * @OA\Get(
     *   path="/jobtitlelists/previewResults",
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
    public function onGetPreviewResults()
    {
        ini_set('memory_limit', '1024M');

        $user = $this->request->getAttribute(UserInterface::class);
        $fileKey = CACHE_TMP_FILE_KEY.$user->getId();
        $resultStatus = $this->cache->getItem($fileKey.'_status');
        $result = $this->cache->getItem($fileKey);

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
    public function onDeleteRemove()
    {
        ini_set('memory_limit', '1024M');

        $user = $this->request->getAttribute(UserInterface::class);
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
        $result = $this->cache->removeItem($fileKey);

        return new JsonResponse([], 200); 
    }

    /**
     * @OA\Post(
     *   path="/jobtitlelists/create",
     *   tags={"JobTitle Lists"},
     *   summary="Create a new excel list",
     *   operationId="jobTitleLists_create",
     *
     *   @OA\RequestBody(
     *     description="Create new job title list",
     *     @OA\JsonContent(ref="#/components/schemas/JobTitleListImport"),
     *   ),
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
    public function onPostImport(array $post)
    {
        $user = $this->request->getAttribute(UserInterface::class);
        $fileKey = CACHE_TMP_FILE_KEY.$user->getId();
        $hasFile = $this->cache->getItem($fileKey);

        if (! $hasFile) {
            return new JsonResponse(
                [
                    'data' => 
                    [
                        'error' => $this->translator->translate(
                            'Uploaded file has expired or file does not exists'
                        )
                    ]
                ], 
                400
            );
        }
        $inputFilter = $this->filter->get(JobTitleListImportFilter::class);
        $inputFilter->setInputData($post);

        if ($inputFilter->isValid()) {
            $data = array();
            $data['clientId'] = CLIENT_ID;
            $data['fileKey'] = $fileKey;

            $yearId = $inputFilter->getValue('yearId');
            $data['yearId'] = null;
            if (! empty($yearId['id'])) {
                $data['yearId'] = $yearId['id'];    
            }
            $data['listName'] = $inputFilter->getValue('listName');

            // send to queue
            // https://www.vultr.com/docs/implement-redis-queue-and-worker-with-php-on-ubuntu-20-04/
            // 
            $this->predis->rpush("jobtitlelist_save", json_encode($data));
            $this->predis->expire("jobtitlelist_save", 300);

            return new JsonResponse([], 200); 
        }
        return new JsonResponse($this->error->getMessages($inputFilter), 400);
    }

     /**
     * @OA\Get(
     *   path="/jobtitlelists/importStatus",
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
    public function onGetImportStatus()
    {
        $user = $this->request->getAttribute(UserInterface::class);
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
    public function onDeleteReset(array $post)
    {   
        $user = $this->request->getAttribute(UserInterface::class);
        $fileKey = CACHE_TMP_FILE_KEY.$user->getId();
        $this->cache->removeItem($fileKey);
        $this->cache->removeItem($fileKey.'_status');
        $this->cache->removeItem($fileKey.'_status2');
        $this->cache->removeItem("jobtitlelist_save");
    
        return new JsonResponse([], 200);
    }

    /**
     * @OA\Put(
     *   path="/jobtitlelists/update/{listId}",
     *   tags={"JobTitle Lists"},
     *   summary="Update jobtitle list",
     *   operationId="jobTitleLists_update",
     *
     *   @OA\Parameter(
     *       name="listId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update jobtitle list",
     *     @OA\JsonContent(ref="#/components/schemas/JobTitleListSave"),
     *   ),
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
    public function onPutUpdate(array $post, string $listId)
    {   
        $inputFilter = $this->filter->get(JobTitleListSaveFilter::class);
        $post['id'] = $listId;
        $inputFilter->setInputData($post);
        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                JobTitleListSave::class,
                [
                    'jobTitleList' => JobTitleListEntity::class,
                ]
            );
            $data['jobTitleListId'] = $inputFilter->getValue('id');
            $this->jobTitleListModel->update($data);
            return new JsonResponse($response); 
        }
        return new JsonResponse($this->error->getMessages($inputFilter), 400);    
    }

    /**
     * @OA\Delete(
     *   path="/jobtitlelists/delete/{listId}",
     *   tags={"JobTitle Lists"},
     *   summary="Delete jobtitle list",
     *   operationId="jobTitleLists_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="listId",
     *       required=true,
     *       description="Jobtitle list uuid",
     *       @OA\Schema(
     *           type="string",
     *           format="uuid",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *   )
     *)
     **/
    public function onDeleteDelete(string $listId)
    {
        $this->jobTitleListModel->delete($listId);
        return new JsonResponse([]);
    }
}
