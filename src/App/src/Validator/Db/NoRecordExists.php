<?php

namespace App\Validator\Db;

use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Validator\Exception;
use Laminas\Validator\Db\AbstractDb;

/**
 * Confirms a record does not exist in a table.
 *
 * Uses Method Override to AbstractDb for CLIENT_ID
 */
class NoRecordExists extends AbstractDb
{
    public function isValid($value)
    {
        /*
         * Check for an adapter being defined. If not, throw an exception.
         */
        if (null === $this->adapter) {
            throw new Exception\RuntimeException('No database adapter present');
        }

        $valid = true;
        $this->setValue($value);

        $result = $this->query($value);
        if ($result) {
            $valid = false;
            $this->error(self::ERROR_RECORD_FOUND);
        }

        return $valid;
    }

    /**
     * Method Override to AbstractDb for CLIENT_ID
     */
    public function getSelect()
    {
        if ($this->select instanceof Select) {
            return $this->select;
        }
        // Build select object
        // 
        $select          = new Select();
        $tableIdentifier = new TableIdentifier($this->table, $this->schema);
        $select->from($tableIdentifier)->columns([$this->field]);
        $select->where->equalTo($this->field, null);

        if ($this->exclude !== null) {
            if (is_array($this->exclude)) {
                $select->where->notEqualTo(
                    $this->exclude['field'],
                    $this->exclude['value']
                );
            } else {
                $select->where($this->exclude);
            }
        }
        $this->select = $select;

        return $this->select;
    }
}
