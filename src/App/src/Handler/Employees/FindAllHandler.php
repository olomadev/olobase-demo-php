<?php

declare(strict_types=1);

namespace App\Handler\Employees;

use App\Model\EmployeeModel;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class FindAllHandler implements RequestHandlerInterface
{
    public function __construct(
        Translator $translator,
        EmployeeModel $employeeModel
    )
    {
        $this->translator = $translator;
        $this->employeeModel = $employeeModel;
    }

    /**
     * @OA\Get(
     *   path="/employees/findAll",
     *   tags={"Employees"},
     *   summary="Find all employees",
     *   operationId="employees_findAll",
     *   
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/CommonFindAll"),
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="No result found"
     *   )
     *)
     **/
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $get = $request->getQueryParams();
        $data = $this->employeeModel->findAllBySearch($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

}
