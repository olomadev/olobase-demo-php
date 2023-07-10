<?php

namespace App\Utils;

use Exception;
use Laminas\Db\Sql\SqlInterface;
use Laminas\Db\Adapter\AdapterInterface;

/**
 * @author Oloma <support@oloma.dev>
 *
 * Column filters
 */
class ColumnFilters
{
    const DATE_QUERY_TYPE_BETWEEN = 'between';
    const DATE_QUERY_TYPE_EQUALITY = 'equality';

    protected $adapter;
    protected $select;
    protected $data = array();
    protected $alias = array();
    protected $columns = array();
    protected $parentColumns = array();
    protected $columnData = array();
    protected $searchData = array();
    protected $likeColumns = array();
    protected $whereColumns = array();
    protected $likeData = array();
    protected $whereData = array();
    protected $orderData = array();

    /**
     * Constructor
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Reset column filter object
     *
     * @return void
     */
    public function clear()
    {
        $this->data = array();
        $this->columns = array();
        $this->parentColumns = array();
        $this->alias = array();
        $this->columnData = array();
        $this->searchData = array();
        $this->likeColumns = array();
        $this->whereColumns = array();
        $this->orderData = array();
        return $this;
    }

    /**
     * Set columns
     *
     * @param object $select
     */
    public function setSelect(SqlInterface $select)
    {
        $this->select = $select;
        return $this;
    }

    /**
     * Set columns
     *
     * @param object $select
     */
    public function getSelect() : SqlInterface
    {
        return $this->select;
    }

    /**
     * Set columns
     *
     * @param array $columns columns
     */
    public function setColumns(array $columns)
    {
        foreach ($columns as $name) {
            $this->columns[(string)$name] = (string)$name;
        }
        return $this;
    }

    /**
     * Set like columns
     * 
     * @param array $columns
     */
    public function setLikeColumns(array $columns)
    {
        foreach ($columns as $name) {
            $this->likeColumns[(string)$name] = (string)$name;
        }
        return $this;
    }

    /**
     * Set where columns
     * 
     * @param array $columns
     */
    public function setWhereColumns(array $columns)
    {
        foreach ($columns as $name) {
            $this->whereColumns[(string)$name] = (string)$name;
        }
        return $this;
    }

    /**
     * Unset columns
     * 
     * @param  array  $columns columns
     */
    public function unsetColumns(array $columns)
    {
        foreach ($columns as $name) {
            unset($this->columns[$name]);
        }
        return $this;
    }

    /**
     * Returns to colum names
     *
     * @return array
     */
    public function getColumns() : array
    {
        return $this->columns;
    }

    /**
     * Set sql alias : CONCAT(u.firstname ,' ', u.lastname) AS name
     *
     * @param string $name  requested column name
     * @param string $alias
     */
    public function setAlias(string $name, string $alias)
    {
        $this->alias[$name] = $alias;
        return $this;
    }

    /**
     * Set parent columns
     * 
     * @param string $parent  parent object
     * @param array  $columns column names
     */
    public function setParentColumns(string $parent, array $columns)
    {
        foreach ($columns as $name) {
            $this->parentColumns[$name] = $parent;    
        }
        return $this;
    }

    /**
     * Returns to normalized data
     * 
     * @return array
     */
    public function getRawData(): array
    {
        $data = $this->getData();
        $newData = array();
        if (! empty($this->columns)) {
            foreach ($this->columns as $name => $value) {
                if (empty($name)) {
                    break;
                }
                if (isset($this->parentColumns[$name])) { // search support for array columns
                    $name = $this->parentColumns[$col['name']];
                }
                if (empty($value) != '') {  // filter columns
                    $newData[$name] = $value;
                }
            }
        }
        return $newData;
    }

    /**
     * Set filter data (GET or POST)
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $searchWords = array();
        if (! empty($data['q']) && strlen($data['q']) > 0) {
            $searchStr   = urldecode($data['q']);
            $searchWords = explode(' ', $searchStr);
        }
        $this->data = $data;
        $platform = $this->adapter->getPlatform();
        // Search data
        // 
        foreach ($this->columns as $name) {
            if (! empty($searchWords)) {  // search data for all columns
                if (isset($this->alias[$name])) { // sql function support
                    $this->searchData[$this->alias[$name]] = $searchWords;
                } else {
                    $colName = $platform->quoteIdentifier($name);
                    $this->searchData[$colName] = $searchWords;
                }
            }
        }
        // Like data
        // 
        foreach ($this->likeColumns as $name) {
            if (! empty($data[$name])) {
                if (isset($this->parentColumns[$name])) { // search support for array columns
                    $name = $this->parentColumns[$name];
                }
                if (isset($this->alias[$name])) { // sql function support
                    $funcName = $this->alias[$name];
                    if ($data[$name] == "true") { // boolean support
                        $this->likeData[$funcName] = 1;
                    } else if ($data[$name] == "false") {
                        $this->likeData[$funcName] = 0;
                    } else {
                        $this->likeData[$funcName] = $data[$name];
                    }
                } else {
                    $colName = $platform->quoteIdentifier($name);
                    if ($data[$name] == "true") { // boolean support
                        $this->likeData[$colName] = 1;
                    } else if ($data[$name] == "false") {
                        $this->likeData[$colName] = 0;
                    } else {
                        $this->likeData[$colName] = $data[$name];
                    }
                }
            }
        }
        // Where data
        // 
        foreach ($this->whereColumns as $name) {
            if (! empty($data[$name])) {
                if (isset($this->parentColumns[$name])) { // search support for array columns
                    $name = $this->parentColumns[$name];
                }
                if (isset($this->alias[$name])) { // sql function support
                    $funcName = $this->alias[$name];
                    if ($data[$name] == "true") { // boolean support
                        $this->whereData[$funcName] = 1;
                    } else if ($data[$name] == "false") {
                        $this->whereData[$funcName] = 0;
                    } else {
                        $this->whereData[$funcName] = $data[$name];
                    }
                } else {
                    $colName = $platform->quoteIdentifier($name);
                    if ($data[$name] == "true") { // boolean support
                        $this->whereData[$colName] = 1;
                    } else if ($data[$name] == "false") {
                        $this->whereData[$colName] = 0;
                    } else {
                        $this->whereData[$colName] = $data[$name];
                    }
                }
            }
        }
        // print_r($this->searchData);
        // die;

        if (! empty($data['_sort'])) {
            $o = 0;
            foreach ($data['_sort'] as $colName) {
                if (! empty($colName) && isset($this->columns[$colName]) && ! empty($data['_order'])) {
                    $direction = (strtolower($data['_order'][$o]) == 'asc') ? 'ASC' : 'DESC';
                    $formattedColName = empty($this->alias[$colName]) ? $colName : $this->alias[$colName];
                    $this->orderData[$o] = $formattedColName.' '.$direction;
                    ++$o;
                }
            }
        }
    }

    /**
     * Set date filter for date columns
     * 
     * @param string $dateColumn column name
     * @param string $endDate if exists
     */
    public function setDateFilter($dateColumn, $endDate = null)
    {
        $this->checkSelect();
        $data = $this->getData();

        if (isset($this->alias[$dateColumn])) {
            $dateColumn = $this->alias[$dateColumn];
        }
        if (isset($this->alias[$endDate])) {
            $endDate = $this->alias[$endDate];
        }
        $columnStart = $dateColumn.'Start';
        $columnEnd = $dateColumn.'End';

        // Between date filter
        // 
        if (empty($endDate)) {
            if (! empty($data[$columnStart]) && empty($data[$columnEnd])) {
                $nest = $this->select->where->nest();
                    $nest->and->equalTo($dateColumn, $data[$columnStart]);
                $nest->unnest();
            } else if (! empty($data[$columnEnd]) && empty($data[$columnStart])) {
                $nest = $this->select->where->nest();
                    $nest->and->equalTo($dateColumn, $data[$columnEnd]);
                $nest->unnest();
            } else if (! empty($data[$columnEnd]) && ! empty($data[$columnStart])) {
                $nest = $this->select->where->nest();
                    $nest->and->between($dateColumn, $data[$columnStart], $data[$columnEnd]);
                $nest->unnest();    
            }
        } else {  // equality date filter
            $columnStart = $dateColumn;
            $columnEnd = $endDate;
            $startKey = Self::removeAlias($columnStart);
            $endKey = Self::removeAlias($columnEnd);
            if (! empty($data[$startKey]) && empty($data[$endKey])) {
                $nest = $this->select->where->nest();
                    $nest->and->equalTo($columnStart, $data[$startKey]);
                $nest->unnest();
            } else if (! empty($data[$endKey]) && empty($data[$startKey])) {
                $nest = $this->select->where->nest();
                    $nest->and->equalTo($columnEnd, $data[$endKey]);
                $nest->unnest();
            } else if (! empty($data[$startKey]) && ! empty($data[$endKey])) {
                $nest = $this->select->where->nest();
                    $nest->and->lessThanOrEqualTo($columnStart, $data[$endKey])
                         ->and->greaterThanOrEqualTo($columnEnd, $data[$startKey]);
                $nest->unnest();    
            }
        }
    }

    protected function checkSelect()
    {
        if (empty($this->select)) {
            throw new Exception(
                sprintf(
                    'Coumn filters class "$select" object could not be null. Please use: %s',
                    '$this->columnFilters->setSelect($select)'
                )
            );
        }
    }

    /**
     * Returns to filtered column => value
     *
     * @return array
     */
    public function getColumnData() : array
    {
        return $this->columnData;
    }

    /**
     * Returns to "like" data column => value
     *
     * @return array
     */
    public function getLikeData() : array
    {
        return $this->likeData;
    }

    /**
     * Returns to "where" data column => value
     *
     * @return array
     */
    public function getWhereData() : array
    {
        return $this->whereData;
    }

    /**
     * Returns to unfiltered data
     *
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * Returns to filtered order data: [name ASC, email DESC]
     *
     * @return array
     */
    public function getOrderData() : array
    {
        return $this->orderData;
    }

    /**
     * Returns to search data: columns => array('str1', 'str2')
     *
     * @return array
     */
    public function getSearchData() : array
    {
        return $this->searchData;
    }

    /**
     * Returns to true if not empty otherwise false
     *
     * @return boolean
     */
    public function searchDataIsNotEmpty()
    {
        if (! empty($this->searchData)) {
            return true;
        }
        return false;
    }

    /**
     * Returns to true if empty otherwise false
     *
     * @return boolean
     */
    public function searchDataEmpty()
    {
        if (empty($this->searchData)) {
            return true;
        }
        return false;
    }

    /**
     * Returns to true if not empty otherwise false
     *
     * @return boolean
     */
    public function likeDataIsEmpty()
    {
        if (empty($this->likeData)) {
            return true;
        }
        return false;
    }

    /**
     * Returns to true if not empty otherwise false
     *
     * @return boolean
     */
    public function likeDataIsNotEmpty()
    {
        if (! empty($this->likeData)) {
            return true;
        }
        return false;
    }

    /**
     * Returns to true if empty otherwise false
     *
     * @return boolean
     */
    public function whereDataIsEmpty()
    {
        if (empty($this->whereData)) {
            return true;
        }
        return false;
    }
    /**
     * Returns to true if not empty otherwise false
     *
     * @return boolean
     */
    public function whereDataIsNotEmpty()
    {
        if (! empty($this->whereData)) {
            return true;
        }
        return false;
    }

    /**
     * Returns to true if not empty otherwise false
     *
     * @return boolean
     */
    public function orderDataIsNotEmpty()
    {
        if (! empty($this->orderData)) {
            return true;
        }
        return false;
    }

    /**
     * Remove key
     * 
     * @param  string $key 
     */
    protected static function removeAlias($key)
    {
        $key = str_replace(["'","`"], "", $key);
        if (strpos($key, ".") > 0) {
            $exp = explode(".", $key);
            if (is_array($exp) && count($exp) > 0) {
                $key = end($exp);
            }
        }
        return $key;
    }

}
