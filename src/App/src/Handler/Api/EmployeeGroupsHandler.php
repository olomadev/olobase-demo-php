<?php

declare(strict_types=1);

namespace App\Handler\Api;

use App\Utils\Error;
use App\Model\CommonModel;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class EmployeeGroupsHandler extends AbstractHandler
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
     *   path="/employeeGroups/findAll",
     *   tags={"Common"},
     *   summary="Find all employee types",
     *   operationId="employeeGroups_findAll",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeGroupsFindAllResultVM"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *
     * 
     *   )
     *)
     **/
    public function onGetFindAll(array $get)
    {
        $data = $this->commonModel->findEmployeeGroups();
        return new JsonResponse([
            'data' => $data
        ]);
    }
}
