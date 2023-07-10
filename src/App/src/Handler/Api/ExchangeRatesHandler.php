<?php

declare(strict_types=1);

namespace App\Handler\Api;

use App\Utils\Error;
use App\Utils\DataManager;
use App\Model\ExchangeRatesModel;
use App\Entity\ExchangeRatesEntity;
use App\Schema\ExchangeRateSave;
use App\Filter\ExchangeRateSaveFilter;
use App\ViewModel\ExchangeRateFindOneVM;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class ExchangeRatesHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        ExchangeRatesModel $exchangeRatesModel,
        DataManager $dataManager,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->exchangeRatesModel = $exchangeRatesModel;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/exchangeRates/findOne",
     *   tags={"Settings"},
     *   summary="Find exchange rates data",
     *   operationId="exchangeRates_findOne",
     *  
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/ExchangeRateFindOneResultVM"),
     *   ),
     *)
     **/
    public function onGetFindOne()
    {
        $row = $this->exchangeRatesModel->findOne();
        if ($row) {
            $viewModel = new ExchangeRateFindOneVM($row);
            return new JsonResponse(['data' => $viewModel->getData()]);            
        }
        return new JsonResponse([], 404);
    }
    
    /**
     * @OA\Get(
     *   path="/exchangeRates/findAllByPaging",
     *   tags={"Settings"},
     *   summary="Find all exchange rates by pagination",
     *   operationId="exchangeRates_findAllByPaging",
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
     *     @OA\JsonContent(ref="#/components/schemas/ExchangeRateFindAllByPageResultVM"),
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

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->exchangeRatesModel->findAllByPaging($get);

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
     * @OA\Get(
     *   path="/exchangeRates/findWeeklyChart",
     *   tags={"Settings"},
     *   summary="Find all weekly chart data",
     *   operationId="exchangeRates_findWeeklyChart",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/ExchangeRateFindWeeklyChartResultVM"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function onGetFindWeeklyChart(array $get)
    { 
        $results = $this->exchangeRatesModel->findWeeklyChart($get);
        return new JsonResponse([
            'data' => $results,
        ]);
    }

    /**
     * @OA\Post(
     *   path="/exchangeRates/create",
     *   tags={"Settings"},
     *   summary="Add rate",
     *   operationId="exchangeRates_create",
     *
     *   @OA\RequestBody(
     *     description="Exchange Rate Create",
     *     @OA\JsonContent(ref="#/components/schemas/ExchangeRateSave"),
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
        $inputFilter = $this->filter->get(ExchangeRateSaveFilter::class);
        $inputFilter->setInputData($post);

        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                ExchangeRateSave::class,
                [
                    'exchangeRates' => ExchangeRatesEntity::class,
                ]
            );
            $this->exchangeRatesModel->create($data);
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
        return new JsonResponse($response); 
    }

}