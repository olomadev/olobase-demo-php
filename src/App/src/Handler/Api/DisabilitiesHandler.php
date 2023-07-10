<?php

declare(strict_types=1);

namespace App\Handler\Api;

use function paginatorJsonDecode;

use App\Utils\Error;
use App\Model\CommonModel;
use App\Utils\DataManager;
use App\Model\DisabilityModel;
use App\Entity\DisabilitiesEntity;
use App\Schema\DisabilitySave;
use App\Filter\DisabilitySaveFilter;
use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class DisabilitiesHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        CommonModel $commonModel,
        DisabilityModel $disabilityModel,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->disabilityModel = $disabilityModel;
        $this->commonModel = $commonModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }
    
    /**
     * @OA\Get(
     *   path="/disabilities/findAll",
     *   tags={"Common"},
     *   summary="Find all employee types",
     *   operationId="disabilities_findAll",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/DisabilitiesFindAllResultVM"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function onGetFindAll(array $get)
    {
        $data = $this->commonModel->findDisabilities();
        return new JsonResponse([
            'data' => $data
        ]);
    }

    /**
     * @OA\Get(
     *   path="/disabilities/findAllByPaging",
     *   tags={"Disabilities"},
     *   summary="Find all disabilities by pagination",
     *   operationId="disabilities_findAllByPaging",
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
     *     @OA\JsonContent(ref="#/components/schemas/DisabilityFindAllByPageResultVM"),
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
        // q=ersin+güvenç&shortName=test&_sort=taxOffice&_order=desc&_sort=taxNumber&_order=asc

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->disabilityModel->findAllByPaging($get);

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
     *   path="/disabilities/create",
     *   tags={"Disabilities"},
     *   summary="Create a new disability",
     *   operationId="disabilities_create",
     *
     *   @OA\RequestBody(
     *     description="Create new disability",
     *     @OA\JsonContent(ref="#/components/schemas/DisabilitySave"),
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
    public function onPostCreate(array $post)
    {
        $inputFilter = $this->filter->get(DisabilitySaveFilter::class);
        $inputFilter->setInputData($post);
        $user = $this->request->getAttribute(UserInterface::class);
        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                DisabilitySave::class,
                [
                    'disabilities' => DisabilitiesEntity::class,
                ]
            );
            $data['disabilityId'] = $inputFilter->getValue('id');
            $this->disabilityModel->create($data, $user);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response);            
    }

    /**
     * @OA\Put(
     *   path="/disabilities/update/{disabilityId}",
     *   tags={"Disabilities"},
     *   summary="Update disability",
     *   operationId="disabilities_update",
     *
     *   @OA\Parameter(
     *       name="disabilityId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update disability",
     *     @OA\JsonContent(ref="#/components/schemas/DisabilitySave"),
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
    public function onPutUpdate(array $post, string $disabilityId)
    {   
        $inputFilter = $this->filter->get(DisabilitySaveFilter::class);
        $post['id'] = $disabilityId;
        $inputFilter->setInputData($post);
        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                DisabilitySave::class,
                [
                    'disabilities' => DisabilitiesEntity::class,
                ]
            );
            $data['disabilityId'] = $inputFilter->getValue('id');
            $this->disabilityModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

    /**
     * @OA\Delete(
     *   path="/disabilities/delete/{disabilityId}",
     *   tags={"Disabilities"},
     *   summary="Delete disability",
     *   operationId="disabilities_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="disabilityId",
     *       required=true,
     *       description="Disability uuid",
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
    public function onDeleteDelete(string $disabilityId)
    {
        $this->disabilityModel->delete($disabilityId);
        return new JsonResponse([]);
    }
    

}
