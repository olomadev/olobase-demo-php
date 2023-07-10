<?php

declare(strict_types=1);

namespace App\Handler\Api;

use function createGuid;
use function paginatorJsonDecode;

use App\Utils\Error;
use App\Utils\UploadError;
use App\Utils\DataManager;
use App\Model\SalaryListModel;
use App\Entity\SalaryListEntity;
use App\Schema\SalaryListSave;
use App\Filter\SalaryListImportFilter;
use App\Filter\SalaryListSaveFilter;
use App\Filter\FileUploadFilter;
use Mezzio\Authentication\UserInterface;
use Predis\ClientInterface as Predis;
use Psr\SimpleCache\CacheInterface;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\InputFilter\InputFilterPluginManager;
use Laminas\Diactoros\Response;
use Laminas\I18n\Translator\TranslatorInterface as Translator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SalaryListsHandler extends AbstractHandler
{
    public function __construct(
        Translator $translator,
        InputFilterPluginManager $filter,
        SalaryListModel $salaryListModel,
        Predis $predis,
        DataManager $dataManager,
        StorageInterface $cache,
        Error $error
    )
    {
        $this->filter = $filter;        
        $this->translator = $translator;
        $this->salaryListModel = $salaryListModel;
        $this->predis = $predis;
        $this->cache = $cache;
        $this->dataManager = $dataManager;
        $this->error = $error;
    }

    /**
     * @OA\Get(
     *   path="/salarylists/findAll",
     *   tags={"Salary Lists"},
     *   summary="Find all salary lists",
     *   operationId="salaryLists_findAll",
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
        $data = $this->salaryListModel->findOptions($get);
        return new JsonResponse([
            'data' => $data,
        ]);
    }

    /**
     * @OA\Get(
     *   path="/salarylists/findAllByPaging",
     *   tags={"Salary Lists"},
     *   summary="Find all jobtitle lists by pagination",
     *   operationId="salaryLists_findAllByPaging",
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
     *     @OA\JsonContent(ref="#/components/schemas/SalaryListFindAllByPageResultVM"),
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
        // q=ersin+güvenç&Salary ListshortName=test&_sort=taxOffice&_order=desc&_sort=taxNumber&_order=asc

        // https://docs.laminas.dev/tutorials/pagination/
        $paginator = $this->salaryListModel->findAllByPaging($get);

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
    
    public function onGetDownloadXls(array $get)
    {
        $results = $this->salaryListModel->findPaymentTypesByYearId($get['yearId']);
        $pRow = $results[0];

        $filename = 'Salaries.xlsx';
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('Salaries')
            ->setSubject('All Salaries');
        $styles = [
            'headings' => [
                'font' => ['bold' => true, 'size' => 10],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                'borders' => ['bottom' => ['style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM]]
            ],
            'cells' => [
                'font' => ['bold' => false, 'size' => 10],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ]
        ];
        $sheet = $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getActiveSheet()->setTitle("Masraf");

        $headings['A'] = 'employeeListId';
        $headings['B'] = 'employeeNumber';
        $headings['C'] = 'yearId';

        $columns['employeeListId'] = 'A';
        $columns['employeeNumber'] = 'B';
        $columns['yearId'] = 'C';

        if ($pRow['calculationTypeId'] == 'dayWorked') {
            $activeSheet = $spreadsheet->getActiveSheet();
            $colNames = ['D','E','F','G','H','I','J','K','L','N'];
            $ci = 0;
            foreach ($results as $res) {
                // assign col names
                // 
                $headings[$res['paymentTypeParamId']] = $colNames[$ci]; 
                $headings[$res['paymentTypeParamId'].'_day'] = $colNames[$ci + 1];
                $columns[$res['paymentTypeParamId']] = $colNames[$ci];
                $columns[$res['paymentTypeParamId'].'_day'] = $colNames[$ci + 1];
                // money and string formats
                // 
                $activeSheet->getStyle($colNames[$ci])->getNumberFormat()->setFormatCode('""#,##0.00_-');
                $activeSheet->getStyle($colNames[$ci + 1])->getNumberFormat()->setFormatCode('#');
                $ci = $ci + 2;
            } 
            $spreadsheet->getActiveSheet()
                ->getStyle('A')
                ->getNumberFormat()
                ->setFormatCode('#'); // Set as string
            $spreadsheet->getActiveSheet()
                ->getStyle('B')
                ->getNumberFormat()
                ->setFormatCode('#'); // Set as string
            $spreadsheet->getActiveSheet()
                ->getStyle('C')
                ->getNumberFormat()
                ->setFormatCode('#'); // Set as string
        } else {
            $headings['D'] = 'sgkDay';
            $columns['sgkDay'] = 'D';
            $activeSheet =$spreadsheet->getActiveSheet();
            $colNames = ['E','F','G','H','I','J','K','L','N'];
            $ci = 0;
            foreach ($results as $res) {
                // assign col names
                // 
                $headings[$colNames[$ci]] = $res['paymentTypeParamId'];
                $columns[$res['paymentTypeParamId']] = $colNames[$ci];
                // money and string formats
                // 
                $activeSheet->getStyle($colNames[$ci])->getNumberFormat()->setFormatCode('""#,##0.00_-');
                $ci = $ci + 1;
            } 
            $spreadsheet->getActiveSheet()
                ->getStyle('A')
                ->getNumberFormat()
                ->setFormatCode('#'); // Set as string
            $spreadsheet->getActiveSheet()
                ->getStyle('B')
                ->getNumberFormat()
                ->setFormatCode('#'); // Set as string
            $spreadsheet->getActiveSheet()
                ->getStyle('C')
                ->getNumberFormat()
                ->setFormatCode('#'); // Set as string
            $spreadsheet->getActiveSheet()
                ->getStyle('D')
                ->getNumberFormat()
                ->setFormatCode('#'); // Set as string
        }
        // row background
        $spreadsheet
            ->getActiveSheet()
            ->getStyle('A1:N1')
            ->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('ffffcc');

        foreach ($columns as $fi => $col) {
            $row = 1;
            $sheet->setCellValue($col.$row, $headings[$col]);
            $sheet->getStyle($col.$row)->applyFromArray($styles['headings']);
        }
        // foreach ($rowArray as $index => $record) {
        //     $row = $index + 2;
        //     foreach ($columns as $field => $col) {
        //         if ($field == 'startDate' && ! empty($record[$field])) {
        //             $date = new DateTime($record[$field]);
        //             $sheet->setCellValue($col.$row, $date->format('d-m-Y'));
        //         } else if ($col == 'A') {
        //             $sheet->setCellValue($col.$row, $index + 1);
        //         } else {
        //             if (isset($record[$field])) {
        //                 $sheet->setCellValue($col.$row, $record[$field]);        
        //             }
        //         }
        //         // if ($field == 'confirmStatus') {
        //         //     $sheet->setCellValue($col.$row, ($record[$field]) ? 'Evet' : 'Hayır'); 
        //         // }
        //         $sheet->getStyle($col.$row)->applyFromArray($styles['cells']);
        //     }
        // }
        foreach ($columns as $field => $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $this->fileContent = ob_get_clean();
        $response = new Response('php://temp', 200);
        $response->getBody()->write($this->fileContent);
        $response = $response->withHeader('Pragma', 'public');
        $response = $response->withHeader('Expires', 0);
        $response = $response->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response = $response->withHeader('Content-Type', 'application/force-download');
        $response = $response->withHeader('Content-Type', 'application/octet-stream');
        $response = $response->withHeader('Content-Type', 'application/download');
        $response = $response->withHeader('Content-Disposition', 'attachment; filename='.$filename);
        return $response;
    }

     /**
     * @OA\Post(
     *   path="/salarylists/upload",
     *   tags={"Salary Lists"},
     *   summary="Upload excel file",
     *   operationId="salaryLists_upload",
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
                $this->predis->rpush("salarylist_parse", json_encode($data));
                $this->predis->expire("salarylist_save", 300);

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
     *   path="/salarylists/previewResults",
     *   tags={"Salary Lists"},
     *   summary="Get excel file to preview results",
     *   operationId="salaryLists_previewResults",
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
     *   path="/salarylists/remove",
     *   tags={"Salary Lists"},
     *   summary="Remove cached data",
     *   operationId="salaryLists_remove",
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
        $this->cache->removeItem('salarylist_parse');
        $this->cache->removeItem($fileKey);
        $fileKey = CACHE_TMP_FILE_KEY.$user->getId().'_status';
        $result = $this->cache->removeItem($fileKey);

        return new JsonResponse([], 200); 
    }

    /**
     * @OA\Post(
     *   path="/salarylists/create",
     *   tags={"Salary Lists"},
     *   summary="Create a new excel list",
     *   operationId="salaryLists_create",
     *
     *   @OA\RequestBody(
     *     description="Create new job title list",
     *     @OA\JsonContent(ref="#/components/schemas/SalaryListImport"),
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
        $inputFilter = $this->filter->get(SalaryListImportFilter::class);
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
            $this->predis->rpush("salarylist_save", json_encode($data));
            $this->predis->expire("salarylist_save", 300);

            return new JsonResponse([], 200); 
        }
        return new JsonResponse($this->error->getMessages($inputFilter), 400);
    }

     /**
     * @OA\Get(
     *   path="/salarylists/importStatus",
     *   tags={"Salary Lists"},
     *   summary="Get excel file import status",
     *   operationId="salaryLists_importStatus",
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
     *   path="/salarylists/reset",
     *   tags={"Salary Lists"},
     *   summary="Reset all statuses",
     *   operationId="salaryLists_reset",
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
        $this->cache->removeItem("salarylist_save");
    
        return new JsonResponse([], 200);
    }

    /**
     * @OA\Put(
     *   path="/salarylists/update/{listId}",
     *   tags={"Salary Lists"},
     *   summary="Update salary list",
     *   operationId="salaryLists_update",
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
     *     description="Update salary",
     *     @OA\JsonContent(ref="#/components/schemas/SalaryListSave"),
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
        $inputFilter = $this->filter->get(SalaryListSaveFilter::class);
        $post['id'] = $listId;
        $inputFilter->setInputData($post);
        $data = array();
        $response = array();
        if ($inputFilter->isValid()) {
            $this->dataManager->setInputFilter($inputFilter);
            $data = $this->dataManager->getEntityData(
                SalaryListSave::class,
                [
                    'salaryList' => SalaryListEntity::class,
                ]
            );
            $data['salaryListId'] = $inputFilter->getValue('id');
            $this->salaryListModel->update($data);
            return new JsonResponse($response); 
        }
        return new JsonResponse($this->error->getMessages($inputFilter), 400);    
    }

    /**
     * @OA\Delete(
     *   path="/salarylists/delete/{listId}",
     *   tags={"Salary Lists"},
     *   summary="Delete salary list",
     *   operationId="salaryLists_delete",
     *
     *   @OA\Parameter(
     *       in="path",
     *       name="listId",
     *       required=true,
     *       description="Salary list uuid",
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
        $this->salaryListModel->delete($listId);
        return new JsonResponse([]);
    }
}
