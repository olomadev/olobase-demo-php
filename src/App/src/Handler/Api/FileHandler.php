<?php

declare(strict_types=1);

namespace App\Handler\Api;

use Laminas\Db\Sql\Sql;
use App\Utils\Error;
use App\Filter\InputFilter;
use Laminas\Filter\StringTrim;
use Laminas\Validator\Uuid;
use Laminas\Validator\InArray;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\Response;
use Laminas\I18n\Translator\TranslatorInterface as Translator;

class FileHandler extends AbstractHandler
{
    public function __construct(
        AdapterInterface $adapter,
        Translator $translator,
        InputFilterPluginManager $filter,
        Error $error
    )
    {
        $this->filter = $filter;
        $this->adapter = $adapter;
        $this->translator = $translator;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/files/findOne/{fileId}",
     *   tags={"Common"},
     *   summary="Find ",
     *   operationId="files_findOne",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="fileId",
     *       required=true,
     *       description="File id",
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Parameter(
     *       in="query",
     *       name="tableName",
     *       required=true,
     *       description="File tableName",
     *       @OA\Schema(
     *           type="string",
     *       ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation (File content returns to Base64 string)",
     *   ),
     *)
     **/
    public function onGetFindOne(array $get, string $fileId)
    {
        $get['fileId'] = $fileId;
        $inputFilter = $this->filter->get(InputFilter::class);
        $inputFilter->add([
            'name' => 'fileId',
            'required' => true,
            'validators' => [
                [
                    'name' => Uuid::class
                ],
            ],
            'name' => 'tableName',
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                [
                    'name' => InArray::class,
                    'options' => [
                        'haystack' => ['expenseFiles','invoiceItems','expenseReportHistory'],
                    ],
                ],
            ],
        ]);
        $inputFilter->setInputData($get);
        if ($inputFilter->isValid()) {
            $tableName = $inputFilter->getValue('tableName');
            $sql = new Sql($this->adapter);
            $select = $sql->select();
            $select->columns(
                [
                    'fileData',
                ]
            );
            $select->from(['f' => 'files']);
            $select->join(
                ['t' => $tableName], 't.fileId = f.fileId',
                [
                    'fileId',
                    'fileName',
                    'fileSize',
                    'fileType',
                ],
                $select::JOIN_LEFT
            );
            $select->where(['f.fileId' => $fileId]);
            $statement = $sql->prepareStatementForSqlObject($select);
            $resultSet = $statement->execute();
            $row = $resultSet->current();
            if (empty($row)) {
                return new TextResponse($this->translator->translate('No document found'), 404);
            }
            $response = new Response('php://temp', 200);
            $response->getBody()->write($row['fileData']);
            $response = $response->withHeader('Pragma', 'public');
            $response = $response->withHeader('Expires', 0);
            $response = $response->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
            $response = $response->withHeader('Content-Type', 'application/force-download');
            $response = $response->withHeader('Content-Type', 'application/octet-stream');
            $response = $response->withHeader('Content-Type', 'application/download');
            // read file
            // $response = $response->withHeader('Content-Type', $row['fileType']);
            // $response = $response->withHeader('Content-Disposition', 'inline; filename='.$row['fileName']);
            // download file
            $response = $response->withHeader('Content-Disposition', 'attachment; filename="'.$row['fileName'].'"');
            $response = $response->withHeader('Content-Transfer-Encoding', 'binary');
            // $response = $response->withHeader('Connection', 'Keep-Alive');
            // $response = $response->withHeader('Content-Length', $row['fileSize']);
            return $response;
        } else {
            return new JsonResponse($this->error->getMessages($inputFilter), 400);
        }
    }
}
