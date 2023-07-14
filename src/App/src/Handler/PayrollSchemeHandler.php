<?php

declare(strict_types=1);

namespace App\Handler\Api;

use App\Utils\Error;
use App\Utils\DataManager;
use App\Schema\PayrollSchemeSave;
use App\Model\PayrollSchemeModel;
use App\Filter\PayrollSchemeSaveFilter;
use App\Entity\PayrollSchemeEntity;
use Mezzio\Authentication\UserInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class PayrollSchemeHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        PayrollSchemeModel $payrollSchemeModel,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->payrollSchemeModel = $payrollSchemeModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }
    
    /**
     * @OA\Get(
     *   path="/payrolschemes/findAllByPaging",
     *   tags={"Payrol Schemes"},
     *   summary="Find all payrol schemes by pagination",
     *   operationId="payrolSchemes_findAllByPaging",
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
     *     @OA\JsonContent(ref="#/components/schemas/SalaryFindAllByPageResultVM"),
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
        // q=ersin+güvenç&SalarieshortName=test&_sort=taxOffice&_order=desc&_sort=taxNumber&_order=asc

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->payrollSchemeModel->findAllByPaging($get);

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
     *   path="/payrolschemes/create",
     *   tags={"Payrol Schemes"},
     *   summary="Create a new scheme",
     *   operationId="payrolSchemes_create",
     *
     *   @OA\RequestBody(
     *     description="Create new scheme",
     *     @OA\JsonContent(ref="#/components/schemas/PayrollSchemeSave"),
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
        $inputFilter = $this->filter->get(PayrollSchemeSaveFilter::class);
        $inputFilter->setInputData($post);
        $user = $this->request->getAttribute(UserInterface::class);
        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                PayrollSchemeSave::class,
                [
                    'payrollScheme' => PayrollSchemeEntity::class,
                ]
            );
            $data['payrollSchemeId'] = $inputFilter->getValue('id');
            $this->payrollSchemeModel->create($data, $user);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response);            
    }

    /**
     * @OA\Put(
     *   path="/payrolschemes/update/{schemeId}",
     *   tags={"Payrol Schemes"},
     *   summary="Update scheme",
     *   operationId="payrolSchemes_update",
     *
     *   @OA\Parameter(
     *       name="schemeId",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\RequestBody(
     *     description="Update scheme",
     *     @OA\JsonContent(ref="#/components/schemas/PayrollSchemeSave"),
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
    public function onPutUpdate(array $post, string $schemeId)
    {   
        $inputFilter = $this->filter->get(PayrollSchemeSaveFilter::class);
        $post['id'] = $schemeId;
        $inputFilter->setInputData($post);
        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                PayrollSchemeSave::class,
                [
                    'payrollScheme' => PayrollSchemeEntity::class,
                ]
            );
            $data['payrollSchemeId'] = $inputFilter->getValue('id');
            $this->payrollSchemeModel->update($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

    /**
     * @OA\Delete(
     *   path="/payrolschemes/delete/{schemeId}",
     *   tags={"Payrol Schemes"},
     *   summary="Delete scheme",
     *   operationId="payrolSchemes_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="schemeId",
     *       required=true,
     *       description="Scheme uuid",
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
    public function onDeleteDelete(string $schemeId)
    {
        $this->payrollSchemeModel->delete($schemeId);
        return new JsonResponse([]);
    }   

}
