<?php
declare(strict_types=1);

namespace App\Model;

use Exception;
use Olobase\Mezzio\ColumnFiltersInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;

class EmployeeModel
{
    private $conn;
    private $adapter;
    private $employees;
    private $employeeChildren;
    private $employeeFiles;
    private $files;
    private $columnFilters;
    private $childrenFunction;

    public function __construct(
        TableGatewayInterface $employees,
        TableGatewayInterface $employeeChildren,
        TableGatewayInterface $employeeFiles,
        TableGatewayInterface $files,
        ColumnFiltersInterface $columnFilters
    ) {
        $this->adapter = $employees->getAdapter();
        $this->employees = $employees;
        $this->employeeChildren = $employeeChildren;
        $this->employeeFiles = $employeeFiles;
        $this->files = $files;
        $this->conn = $this->adapter->getDriver()->getConnection();
        $this->columnFilters = $columnFilters;
    }

    public function findAllBySearch(array $get)
    {
        $platform = $this->adapter->getPlatform();
        $concat = "CONCAT_WS(' ', ";
            $concat.= " NULLIF( e.name , '' ) ,";
            $concat.= " NULLIF( e.surname , '' ) ";
        $concat.= ")";
        $concatFunction = $platform->quoteIdentifierInFragment($concat, 
            ['(',')','CONCAT_WS','\'',',','IFNULL','-']
        );
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'employeeId',
            'name' => new Expression($concatFunction),
        ]);
        $select->from(['e' => 'employees']);

        // autocompleter search query
        //
        if (! empty($get['q']) && strlen($get['q']) > 2) {
            $nest = $select->where->nest();
            $exp = explode(" ", $get['q']);
            foreach ($exp as $str) {
                $nest = $nest->or->nest();
                    $nest->or->like('name', '%'.$str.'%');
                    $nest->or->like('surname', '%'.$str.'%');
                $nest = $nest->unnest();
            }
            $nest->unnest();
        }
        // filter by employeeNumber
        // 
        if (! empty($get['employeeNumber'])) {
            $select->where(['e.employeeNumber' => $get['employeeNumber']]);
        }
        if (! empty($get['id'])) {
            if (is_array($get['id'])) {
                $values = array();
                foreach ($get['id'] as $val) {
                    if (!empty($val['id'])) {
                        $values[] = $val['id'];
                    }
                }
                $select->where(['e.employeeId' => $values]);
            } else {
                $select->where(['e.employeeId' => $get['id']]);    
            }
        }
        $select->limit(100); // default limit for auto completer

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $results = iterator_to_array($resultSet);
        return $results;
    }

    public function findOneById(string $employeeId)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'employeeId',
                'name',
                'surname',
                'employeeNumber',
                'employmentStartDate',
                'employmentEndDate',
                'createdAt'
            ]
        );
        $select->from(['e' => 'employees']);
        $select->join(['c' => 'companies'], 'c.companyId = e.companyId', 
            [
                'companyId' => new Expression("JSON_OBJECT('id', c.companyId, 'name', c.companyShortName)"),
            ],
        $select::JOIN_LEFT);
        $select->join(['d' => 'departments'], 'd.departmentId = e.departmentId', 
            [
                'departmentId' => new Expression("JSON_OBJECT('id', d.departmentId, 'name', d.departmentName)"),
            ],
        $select::JOIN_LEFT);
        $select->join(['j' => 'jobTitles'], 'j.jobTitleId = e.jobTitleId', 
            [
                'jobTitleId' => new Expression("JSON_OBJECT('id', j.jobTitleId, 'name', j.jobTitleName)"),
            ],
        $select::JOIN_LEFT);
        $select->join(['eg' => 'employeeGrades'], 'eg.gradeId = e.gradeId', 
            [
                'gradeId' => new Expression("JSON_OBJECT('id', eg.gradeId, 'name', eg.gradeName)"),
            ],
        $select::JOIN_LEFT);
        $select->where(['e.employeeId' => $employeeId]);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();

        // children
        // 
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'childId',
                'childName',
                'childBirthdate',
            ]
        );
        $select->from('employeeChildren');
        $select->where(['employeeId' => $employeeId]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $employeeChildren = iterator_to_array($resultSet);
        $statement->getResource()->closeCursor();
        $row['employeeChildren'] = $employeeChildren;

        // files
        // 
        $sql    = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns(
            [
                'id' => 'fileId',
                'name' => 'fileName',
                'size' => 'fileSize',
                'type' => 'fileType',
            ]
        );
        $select->from(['ef' => 'employeeFiles']);
        $select->join(['f' => 'files'], 'f.fileId = ef.fileId', 
            [
                'data' => new Expression("CONCAT('data:', ef.fileType, ';base64,', TO_BASE64(data))")
            ],
        $select::JOIN_LEFT);
        $select->where(['employeeId' => $employeeId]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row['files'] = iterator_to_array($resultSet);
        $statement->getResource()->closeCursor();
        return $row;
    }

    public function findAll()
    {
        $platform = $this->adapter->getPlatform();
        $child = "JSON_ARRAYAGG(";
        $child.= "JSON_OBJECT(";
        $child.= "'childId' , ec.childId , ";
        $child.= "'childName' , ec.childName , ";
        $child.= "'childBirthdate' , ec.childBirthdate  ";
        $child.= "))";
        $this->childrenFunction = $platform->quoteIdentifierInFragment(
            "(SELECT $child FROM employeeChildren ec WHERE ec.employeeId = e.employeeId)",
            [
                '(',
                ')',
                'childId',
                'childName',
                'childBirthdate',
                'SELECT',
                'FROM',
                'AS',
                'as',
                'ec',
                'e',
                ',',
                '[',
                ']',
                'JSON_ARRAYAGG',
                'JSON_OBJECT',
                'WHERE',
                ';',
                'CONCAT',
                'id',
                'name',
                '"',
                '\'',
                '\"', '=', '?', 'JOIN', 'ON', 'AND', 'LEFT', ','
            ]
        );
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->columns([
            'id' => 'employeeId',
            'employeeNumber',
            'name',
            'surname',
            'employmentStartDate',
            'employmentEndDate',
            'employeeChildren' => new Expression($this->childrenFunction),
            'createdAt',
        ]);
        $select->from(['e' => 'employees']);
        $select->join(['c' => 'companies'], 'c.companyId = e.companyId', 
            [
                'companyId' => new Expression(
                    "if(c.companyId IS NULL, null, JSON_OBJECT('id', c.companyId, 'name', c.companyShortName))"
                ),
            ],
        $select::JOIN_LEFT);
        $select->join(['d' => 'departments'], 'd.departmentId = e.departmentId', 
            [
                'departmentId' => new Expression("JSON_OBJECT('id', d.departmentId, 'name', d.departmentName)"),
            ],
        $select::JOIN_LEFT);
        $select->join(['j' => 'jobTitles'], 'j.jobTitleId = e.jobTitleId', 
            [
                'jobTitleId' => new Expression(
                    "if(j.jobTitleId IS NULL, null, JSON_OBJECT('id', j.jobTitleId, 'name', j.jobTitleName))"
                ),
            ],
        $select::JOIN_LEFT);
        $select->join(['g' => 'employeeGrades'], 'g.gradeId = e.gradeId', 
            [
                'gradeId' => new Expression(
                    "if(g.gradeId IS NULL, null, JSON_OBJECT('id', g.gradeId, 'name', g.gradeName))"
                ),
            ],
        $select::JOIN_LEFT);

        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;

        return $select;
    }

    public function findAllByPaging(array $get)
    {
        $select = $this->findAll();
        $this->columnFilters->clear();
        $this->columnFilters->setAlias('companyId', 'c.companyId');
        $this->columnFilters->setAlias('jobTitleId', 'j.jobTitleId');
        $this->columnFilters->setAlias('gradeId', 'g.gradeId');
        $this->columnFilters->setColumns([
            'companyId',
            'employeeNumber',
            'name',
            'surname',
            'companyId',
            'jobTitleId',
            'gradeId'
        ]);
        $this->columnFilters->setLikeColumns(
            [
                'employeeNumber',
                'name',
                'surname',
            ]
        );
        $this->columnFilters->setWhereColumns(
            [
                'companyId',
                'jobTitleId',
                'gradeId',
                'departmentId',
            ]
        );
        $this->columnFilters->setData($get);
        $this->columnFilters->setSelect($select);

        if ($this->columnFilters->searchDataIsNotEmpty()) {
            $nest = $select->where->nest();
            foreach ($this->columnFilters->getSearchData() as $col => $words) {
                $nest = $nest->or->nest();
                foreach ($words as $str) {
                    $nest->or->like(new Expression($col), '%'.$str.'%');
                }
                $nest = $nest->unnest();
            }
            $nest->unnest();
        }
        if ($this->columnFilters->likeDataIsNotEmpty()) {
            foreach ($this->columnFilters->getLikeData() as $column => $value) {
                if (is_array($value)) {
                    $nest = $select->where->nest();
                    foreach ($value as $val) {
                        $nest->or->like(new Expression($column), '%'.$val.'%');
                    }
                    $nest->unnest();
                } else {
                    $select->where->like(new Expression($column), '%'.$value.'%');
                }
            }   
        }
        if ($this->columnFilters->whereDataIsNotEmpty()) {
            foreach ($this->columnFilters->getWhereData() as $column => $value) {
                if (is_array($value)) {
                    $nest = $select->where->nest();
                    foreach ($value as $val) {
                        $nest->or->equalTo(new Expression($column), $val);
                    }
                    $nest->unnest();
                } else {
                    $select->where->equalTo(new Expression($column), $value);
                }
            }
        }
        if ($this->columnFilters->orderDataIsNotEmpty()) {
            foreach ($this->columnFilters->getOrderData() as $order) {
                $select->order(new Expression($order));
            }
        }
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
        $paginatorAdapter = new DbSelect(
            $select,
            $this->adapter
        );
        $paginator = new Paginator($paginatorAdapter);
        return $paginator;
    }

    public function create(array $data)
    {
        $employeeId = $data['id'];
        try {
            $this->conn->beginTransaction();
            $data['employees']['employeeId'] = $employeeId;
            $data['employees']['createdAt'] = date('Y-m-d H:i:s');
            $this->employees->insert($data['employees']);

            // children
            if (! empty($data['employeeChildren'])) {
                foreach ($data['employeeChildren'] as $val) {
                    $val['employeeId'] = $employeeId;
                    $this->employeeChildren->insert($val);
                }
            }
            // files 
            foreach ($data['files'] as $row) {
                $fileId = $row['id'];
                $blobData = $row['data'];
                $this->files->insert(['fileId' => $fileId, 'data' => $blobData]);
                $this->employeeFiles->insert(
                    [
                        'employeeId' => $employeeId,
                        'fileId' => $fileId,
                        'fileName' => $row['name'],
                        'fileSize' => $row['size'],
                        'fileType' => $row['type'],
                    ]
                );
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function update(array $data)
    {
        $employeeId = $data['id'];
        try {
            $this->conn->beginTransaction();
            $this->employees->update($data['employees'], ['employeeId' => $employeeId]);

            // delete children
            // 
            $this->employeeChildren->delete(['employeeId' => $employeeId]);
            if (! empty($data['employeeChildren'])) {
                foreach ($data['employeeChildren'] as $val) {
                    $val['employeeId'] = $employeeId;
                    $this->employeeChildren->insert($val);
                }
            }
            // delete files
            // 
            $this->deleteFiles($employeeId);
            //
            // update employee files 
            // 
            foreach ($data['files'] as $row) {
                $fileId = $row['id'];
                $blobData = $row['data'];
                $this->files->insert(['fileId' => $fileId, 'data' => $blobData]);
                $this->employeeFiles->insert(
                    [
                        'employeeId' => $employeeId,
                        'fileId' => $fileId,
                        'fileName' => $row['name'],
                        'fileSize' => $row['size'],
                        'fileType' => $row['type'],
                    ]
                );
            }
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function delete(string $employeeId)
    {
        try {
            $this->conn->beginTransaction();
            $this->employees->delete(['employeeId' => $employeeId]);
            $this->employeeChildren->delete(['employeeId' => $employeeId]);
            $this->deleteFiles($employeeId);
            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function deleteFiles($employeeId)
    {
        // select delete files
        //
        $sql = new Sql($this->adapter);
        $select = $sql->select()
            ->from('employeeFiles')
            ->where(['employeeId' => $employeeId]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $files = iterator_to_array($resultSet);
        $statement->getResource()->closeCursor();
        //
        // delete files & employee files
        // 
        foreach ($files as $file) {
            $this->files->delete(['fileId' => $file['fileId']]);
        }
        $this->employeeFiles->delete(['employeeId' => $employeeId]);
    }

    public function getAdapter() : AdapterInterface
    {
        return $this->adapter;
    }

}
