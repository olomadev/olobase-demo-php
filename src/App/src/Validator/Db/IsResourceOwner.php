<?php

namespace App\Validator\Db;

use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Validator\Exception;
use Laminas\Validator\Db\AbstractDb;

/**
 * Confirms a record exist in a table.
 *
 * Uses Method Override to AbstractDb for CLIENT_ID
 */
class IsResourceOwner extends AbstractDb
{
    /**
     * Error constants
     */
    const ERROR_RECORD_OWNERSHIP = 'recordOwnershipError';

    /**
     * @var array Message templates
     */
    protected $messageTemplates = [
        self::ERROR_RESOURCE_OWNERSHIP => 'You are not authorized to modify a record that is not yours',
    ];

    /**
     * @var options
     */
    protected $options = [
        'where'  => '',
    ];
    
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

        if (empty($result[$this->field])) {
            $valid = false;
            $translator = $this->getTranslator();
            $message = $this->messageTemplates[Self::ERROR_RESOURCE_OWNERSHIP];
            $translatedMessage = $translator->translate($message, $this->getTranslatorTextDomain()); 
            throw new Exception\RuntimeException($translatedMessage);
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
        $where = $this->getOption('where');

        // Build select object
        $select          = new Select();
        $tableIdentifier = new TableIdentifier($this->table, $this->schema);
        $select->from($tableIdentifier)->columns([$this->field]);
        $select->where->equalTo($this->field, $this->value);
        if (! empty($where)) {
            foreach ($where as $key => $val) {
                $select->where->equalTo($key, $val);
            }
        }
        // echo $select->getSqlString($this->adapter->getPlatform());
        // die;
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
