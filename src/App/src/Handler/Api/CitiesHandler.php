<?php

declare(strict_types=1);

namespace App\Handler\Api;

use App\Utils\Error;
use App\Model\CommonModel;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class CitiesHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        CommonModel $commonModel,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->translator = $translator;
        $this->commonModel = $commonModel;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/cities/findAll",
     *   tags={"Common"},
     *   summary="Find all cities",
     *   operationId="cities_findAll",
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
        if (empty($get['countryId'])) {
            return new JsonResponse([
                'error' => "Country ID cannot be empty"
            ]);    
        }
        $data = $this->commonModel->findCitiesByCountryId($get['countryId']);
        return new JsonResponse([
            'data' => $data
        ]);
    }
}
