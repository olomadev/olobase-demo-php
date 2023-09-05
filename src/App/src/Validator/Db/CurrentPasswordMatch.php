<?php

namespace App\Validator\Db;

use Laminas\Validator\Exception;
use Laminas\Validator\AbstractValidator;

/**
 * Confirms a record matched in a table.
 */
class CurrentPasswordMatch extends AbstractValidator
{
    /**
     * Error constants
     */
    const ERROR_NO_CURRENT_PASSWORD_MATCHED = 'noCurrentPasswordMatched';

    /**
     * @var array Message templates
     */
    protected $messageTemplates = [
        self::ERROR_NO_CURRENT_PASSWORD_MATCHED => 'Current password is not correct',
    ];

    /**
     * @var options
     */
    protected $options = [
        'userId'  => '',
        'adapter'  => '',
    ];

    /**
     * Returns true if and only if the password match with $value
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        $userId  = $this->getOption('userId');
        $adapter = $this->getOption('adapter');
        /**
         * Check for an adapter being defined. If not, throw an exception.
         */
        if (null === $adapter) {
            throw new Exception\RuntimeException('No database adapter present');
        }

        $valid = true;
        $this->setValue($value);

        $statement = $adapter->createStatement('SELECT password FROM users WHERE userId = ?');
        $resultSet = $statement->execute([$userId]);
        $row = $resultSet->current();

        if (empty($row)) {
            $valid = false;
            $this->error(self::ERROR_NO_CURRENT_PASSWORD_MATCHED);
        }
        $hash = $row['password'];
        $verify = password_verify($value, $hash);
        if (false == $verify) {
            $valid = false;
            $this->error(self::ERROR_NO_CURRENT_PASSWORD_MATCHED);
        }
        return $valid;
    }
}
