<?php

declare(strict_types=1);

namespace App\Handler\Api;

use App\Utils\Error;
use App\Model\CommonModel;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class AreaCodesHandler extends AbstractHandler
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
     *   path="/areaCodes/findAll",
     *   tags={"Common"},
     *   summary="Find all area codes",
     *   operationId="areaCodes_findAll",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/AreaCodesFindAllResultVM"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function onGetFindAll(array $get)
    {
        $data = $this->commonModel->findAreaCodes();
        return new JsonResponse([
            'data' => $data
        ]);
    }
}
