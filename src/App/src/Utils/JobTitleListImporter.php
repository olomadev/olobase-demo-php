<?php
declare(strict_types=1);

namespace App\Utils;

use function createGuid;

use Exception;
use App\Model\CommonModel;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Predis\ClientInterface as Predis;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;

class JobTitleListImporter
{
    protected $cache;
    protected $conn;
    protected $predis;
    protected $jobTitles;

    public function __construct($container)
    {
        $this->predis = $container->get(Predis::class);
        $this->commonModel = $container->get(CommonModel::class);
        $this->cache = $container->get(StorageInterface::class);

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
            $import = $this->cache->getItem($fileKey);

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
                $this->cache->removeItem($fileKey);
                $this->cache->removeItem("jobtitlelist_parse");
                $this->cache->removeItem("jobtitlelist_save");

                $this->cache->setItem($fileKey.'_status2', ['status' => true, 'error' => null]);
                $this->predis->expire($fileKey.'_status2', 200);
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
