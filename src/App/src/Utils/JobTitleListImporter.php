<?php
declare(strict_types=1);

namespace App\Utils;

use function createGuid;

use Exception;
use App\Model\CommonModel;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\I18n\Translator\TranslatorInterface;
use Psr\SimpleCache\CacheInterface as SimpleCacheInterface;

class JobTitleListImporter
{
    protected $conn;
    protected $simpleCache;
    protected $jobTitles;

    public function __construct($container)
    {
        $this->commonModel = $container->get(CommonModel::class);
        $this->simpleCache = $container->get(SimpleCacheInterface::class);

        $this->adapter = $container->get(AdapterInterface::class);
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->jobTitles = new TableGateway('jobTitles', $this->adapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
        $this->jobTitleList = new TableGateway('jobTitleList', $this->adapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
    }

    public function import($data)
    {
        $fileKey = "";
        if (! empty($data['yearId']) && ! empty($data['listName'])) {
            
            $fileKey = $data['fileKey'];
            $import = $this->simpleCache->get($fileKey);

            if (! empty($import['data'][0])) {
                unset($import['data'][0]); // remove header
                $companies = $this->commonModel->findCompanyShortNamesByKey();

                try {
                    $this->conn->beginTransaction();
                    $insertData = array();
                    $jobTitleListId = createGuid();
                    //
                    // create list data
                    // 
                    $this->jobTitleList->insert(
                        [
                            'jobTitleListId' => $jobTitleListId,
                            'yearId' => $data['yearId'],
                            'listName' => trim($data['listName']),
                        ]
                    );
                    // create job titles
                    //
                    foreach ($import['data'] as $row) {
                        $insertData['jobTitleListId'] = $jobTitleListId;
                        $insertData['companyId'] = Self::getCompanyId($row['companyId']['value'], $companies);
                        $insertData['jobTitleId'] = createGuid();
                        $insertData['jobTitleName'] = trim($row['jobTitleId']['value']);
                        $insertData['createdAt'] = date("Y-m-d H:i:s");
                        $this->jobTitles->insert($insertData);
                    }
                    $this->conn->commit();
                } catch (Exception $e) {
                    $this->conn->rollback();
                    throw $e;
                }
                $this->simpleCache->delete($fileKey);
                $this->simpleCache->delete("jobtitlelist_parse");
                $this->simpleCache->delete("jobtitlelist_save");
                //
                // set status to follow progress
                //
                $this->simpleCache->set($fileKey.'_status2', ['status' => true, 'error' => null], 600);
            }
        }               

    } // end func
    
    public static function getCompanyId($key, $companies)
    {
        if (! empty($companies[$key])) {
            return $companies[$key];
        }
        return null;
    }

} // end class
