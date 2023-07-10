<?php

declare(strict_types=1);

/**
 * Works for every 5 minute
 */
require dirname(__DIR__).'/vendor/autoload.php';

use App\Utils\Mailer;
use App\Utils\ErrorMailer;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\I18n\Translator\TranslatorInterface;

date_default_timezone_set('Europe/Istanbul'); // default time zone set

define("SMTP_ERROR_MAILER", false);
define("TABLE_EMPLOYEE_EDUCATIONS", "employeeEducations");
define("TABLE_EMPLOYEE_AGREEMENTS", "employeeAgreements");
define("TABLE_EMPLOYEE_HEALTHDOCS", "employeeHealthDocs");

set_error_handler(function($errno, $errstr, $errfile, $errline ){
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});
$args = $_SERVER['argv'];
putenv("APP_ENV=$args[1]"); // set environment

$container = require dirname(__DIR__).'/config/container.php';

$mailer = $container->get(Mailer::class);
$adapter = $container->get(AdapterInterface::class);
$errors = new TableGateway('errors', $adapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
$translator = $container->get(TranslatorInterface::class);
$platform = $adapter->getPlatform();
$connection = $adapter->getDriver()->getConnection();

// unmark processed records if one day has passed
//-------------------------------------------------------------------
unmarkCompleted($adapter);
//-------------------------------------------------------------------

if (SMTP_ERROR_MAILER) {
    set_exception_handler(function($e) use($container) {
        $errorMailer = $container->get(ErrorMailer::class);
        $errorMailer->setException($e);
        $errorMailer->send();
    });    
}
$sql = new Sql($adapter);
$select = $sql->select();
$platform = $adapter->getPlatform();

$user = "JSON_ARRAYAGG(";
    $user.= "JSON_OBJECT(";
        $user.= "'firstname' , u.firstname , ";
        $user.= "'lastname' , u.lastname , ";
        $user.= "'email' , u.email";
    $user.= ")";
$user.= ")";
$notificationUsersFunction = $platform->quoteIdentifierInFragment(
    "(SELECT $user FROM notificationUsers nu JOIN users as u ON u.userId = nu.userId WHERE nu.notifyId = n.notifyId)",
    [
        '(',')',
        'SELECT',
        'FROM',
        'AS',
        'as',
        ',',
        '[',
        ']',
        'LEFT',
        'WHERE',
        'JSON_ARRAYAGG',
        'JSON_OBJECT',
        'firstname',
        'lastname',
        'email',
        '"',
        '\'',
        '\"', '=', '?', 'JOIN', 'ON', 'AND', ','
    ]
);
$select->columns(
	[
		'notifyId',
        'notifyName',
		'dateId',
		'days',
		'dayType',
		'sameDay',
		'atTime',
		'notifyType',
		'message',
		'createdAt',
        'processedAt',
        'users' => new Expression($notificationUsersFunction), 
	]
);
$select->from(['n' => 'notifications']);
$select->join(
    ['nm' => 'notificationModules'], 'n.moduleId = nm.moduleId',
    [
    	'tableName',
    ],
    $select::JOIN_LEFT
);
$select->where(['active' => 1, 'processed' => 0]);

// echo $select->getSqlString($adapter->getPlatform());
// die;
$statement = $sql->prepareStatementForSqlObject($select);
$resultSet = $statement->execute();
$results = iterator_to_array($resultSet);
$statement->getResource()->closeCursor();

foreach($results as $row) {
    $tableName = $row['tableName'];
    switch ($tableName) {
        case TABLE_EMPLOYEE_EDUCATIONS:
            $concat = "CONCAT_WS(' ', ";
                $concat.= " NULLIF( e.name , '' ) ,";
                $concat.= " NULLIF( e.middleName , '' ) ,";
                $concat.= " NULLIF( e.surname , '' ) ,";
                $concat.= " NULLIF( e.secondSurname , '' )";
            $concat.= ")";
            $sql = new Sql($adapter);
            $select = $sql->select();
            $select->columns([
                'employeeName' => new Expression($concat),
                'educationValidityEnd',
            ]);
            $select->from(['ed' => 'employeeEducations']);
            $select->join(
                ['edt' => 'educations'], 'edt.educationId = ed.educationId',
                [
                    'educationName',
                    'educationValidity',
                ],
                $select::JOIN_LEFT
            );
            $select->join(
                ['jt' => 'jobTitles'], 'jt.jobTitleId = ed.jobTitleId',
                [
                    'jobTitleName',
                ],
                $select::JOIN_LEFT
            );
            $select->join(
                ['e' => 'employees'], 'ed.employeeId = e.employeeId',
                [
                    'pernetNumber',
                ],
                $select::JOIN_LEFT
            );
            // date type
            // 
            $select = buildDateSql($select, $row, $row['dateId']);

            $statement = $sql->prepareStatementForSqlObject($select);
            $resultSet = $statement->execute();
            $employeeResults = iterator_to_array($resultSet);
            $statement->getResource()->closeCursor();
            if (! empty($row['atTime']) && ! empty($employeeResults)) {
                $now = strtotime(date("Y-m-d H:i"));
                $atTime = strtotime(date("Y-m-d")." ".$row["atTime"]); 
                if ($now > $atTime) {
                    // process the job
                    //
                    $subject = trim($row['notifyName']);
                    $message = buildEmployeeEducationList($row, $employeeResults);
                    sendMailToUsers($container, $subject, $message, $row['users']);

                    // mark processed record
                    //-------------------------------------------------------------------
                    markCompleted($row, $adapter);
                    
                    // echo date("Y-m-d H:i", $now).PHP_EOL;
                    // echo date("Y-m-d H:i", $atTime).PHP_EOL;
                }
            }
            // var_dump($row);
            // var_dump($employeeResults);
            break;

        case TABLE_EMPLOYEE_AGREEMENTS:            
            $concat = "CONCAT_WS(' ', ";
            $concat.= " NULLIF( e.name , '' ) ,";
            $concat.= " NULLIF( e.middleName , '' ) ,";
            $concat.= " NULLIF( e.surname , '' ) ,";
            $concat.= " NULLIF( e.secondSurname , '' )";
            $concat.= ")";
            $sql = new Sql($adapter);
            $select = $sql->select();
            $select->columns([
                'employeeName' => new Expression($concat),
                'startDate',
                'endDate',
                'earlyEndDate',
            ]);
            $select->from(['ea' => 'employeeAgreements']);
            $select->join(
                ['jt' => 'jobTitles'], 'jt.jobTitleId = ea.jobTitleId',
                [
                    'jobTitleName',
                ],
                $select::JOIN_LEFT
            );
            $select->join(
                ['c' => 'customers'], 'c.customerId = ea.customerId',
                [
                    'customerShortName',
                ],
                $select::JOIN_LEFT
            );
            $select->join(
                ['e' => 'employees'], 'ea.employeeId = e.employeeId',
                [
                    'pernetNumber',
                ],
                $select::JOIN_LEFT
            );
            $select->where(['isEnd' => 0]);

            // date type
            // 
            $select = buildDateSql($select, $row, $row['dateId']);

            $statement = $sql->prepareStatementForSqlObject($select);
            $resultSet = $statement->execute();
            $employeeResults = iterator_to_array($resultSet);
            $statement->getResource()->closeCursor();

            // echo $select->getSqlString($adapter->getPlatform());
            // die;

            if (! empty($row['atTime']) && ! empty($employeeResults)) {
                $now = strtotime(date("Y-m-d H:i"));
                $atTime = strtotime(date("Y-m-d")." ".$row["atTime"]); 
                if ($now > $atTime) {
                    // process the job
                    //
                    $subject = trim($row['notifyName']);
                    $message = buildEmployeeAgreementList($row, $employeeResults);
                    sendMailToUsers($container, $subject, $message, $row['users']);

                    // mark processed record
                    //-------------------------------------------------------------------
                    markCompleted($row, $adapter);
                    
                    // echo date("Y-m-d H:i", $now).PHP_EOL;
                    // echo date("Y-m-d H:i", $atTime).PHP_EOL;
                }
            }
            // var_dump($row);
            // var_dump($employeeResults);
            break;

        case TABLE_EMPLOYEE_HEALTHDOCS:
            $concat = "CONCAT_WS(' ', ";
            $concat.= " NULLIF( e.name , '' ) ,";
            $concat.= " NULLIF( e.middleName , '' ) ,";
            $concat.= " NULLIF( e.surname , '' ) ,";
            $concat.= " NULLIF( e.secondSurname , '' )";
            $concat.= ")";
            $sql = new Sql($adapter);
            $select = $sql->select();
            $select->columns([
                'employeeName' => new Expression($concat),
                'healthDocDate',
                'healthDocDoctorDate',
                'healthDocValidityEnd',
            ]);
            $select->from(['eh' => 'employeeHealthDocs']);
            $select->join(
                ['jt' => 'jobTitles'], 'jt.jobTitleId = eh.jobTitleId',
                [
                    'jobTitleName',
                ],
                $select::JOIN_LEFT
            );
            $select->join(
                ['h' => 'healthDocs'], 'h.healthDocId = eh.healthDocId',
                [
                    'healthDocName',
                    'healthDocValidity',
                ],
                $select::JOIN_LEFT
            );
            $select->join(
                ['e' => 'employees'], 'eh.employeeId = e.employeeId',
                [
                    'pernetNumber',
                ],
                $select::JOIN_LEFT
            );
            // date type
            // 
            $select = buildDateSql($select, $row, $row['dateId']);

            $statement = $sql->prepareStatementForSqlObject($select);
            $resultSet = $statement->execute();
            $employeeResults = iterator_to_array($resultSet);
            $statement->getResource()->closeCursor();

            // echo $select->getSqlString($adapter->getPlatform());
            // die;

            if (! empty($row['atTime']) && ! empty($employeeResults)) {
                $now = strtotime(date("Y-m-d H:i"));
                $atTime = strtotime(date("Y-m-d")." ".$row["atTime"]); 
                if ($now > $atTime) {
                    // process the job
                    //
                    $subject = trim($row['notifyName']);
                    $message = buildEmployeeHealthDocList($row, $employeeResults);
                    sendMailToUsers($container, $subject, $message, $row['users']);

                    // mark processed record
                    //-------------------------------------------------------------------
                    markCompleted($row, $adapter);
                    
                    // echo date("Y-m-d H:i", $now).PHP_EOL;
                    // echo date("Y-m-d H:i", $atTime).PHP_EOL;
                }
            }
            // var_dump($row);
            // var_dump($employeeResults);
            break;
    }
}

// print_r($results);
// die;

function buildDateSql($select, $row, $field) {
    if ($row['sameDay']) {
        $select->where([$field => date("Y-m-d")]);
    }
    if ($row['dayType'] == 'dayBefore' && $row['days'] > 0) {
        $days = ($row['days'] == 1) ? 'day' : 'days';
        $dayBefore = date('Y-m-d', strtotime("+".$row['days']." ".$days));
        $select->where([$field => $dayBefore]);
    }
    if ($row['dayType'] == 'dayAfter' && $row['days'] > 0) {
        $days = ($row['days'] == 1) ? 'day' : 'days';
        $dayAfter = date('Y-m-d', strtotime("-".$row['days']." ".$days));
        $select->where([$field => $dayAfter]);
    }
    return $select;
}
function buildEmployeeAgreementList($row, $employeeResults) {
    $message = $row['message'];
    $message.= "<table border=\"1\" style=\"border-collapse:collapse;\" width=\"100%\">";
    $message.= "<thead>";
    $message.= "<tr>";
    $message.= "<th>Çalışan Adı</th>";
    $message.= "<th>Pernet No</th>";
    $message.= "<th>Görev Tanımı</th>";
    $message.= "<th>Müşteri</th>";
    $message.= "<th>Sözleşme Başlangıcı</th>";
    $message.= "<th>Sözleşme Sonu</th>";
    $message.= "<th>Erken Bitiş Tarihi</th>";
    $message.= "</tr>";
    $message.= "</thead>";
    $message.= "<tbody>";
    foreach ($employeeResults as $edRow) {
        $message.= "<tr>";
        $message.= "<td>".$edRow['employeeName']."</td>";
        $message.= "<td>".$edRow['pernetNumber']."</td>";
        $message.= "<td>".$edRow['jobTitleName']."</td>";
        $message.= "<td>".$edRow['customerShortName']."</td>";
        $message.= "<td>".date('d-m-Y', strtotime($edRow['startDate']))."</td>";
        $message.= "<td>".date('d-m-Y', strtotime($edRow['endDate']))."</td>";
        $message.= "<td>".date('d-m-Y', strtotime($edRow['earlyEndDate']))."</td>";
        $message.= "</tr>";
    }
    $message.= "</tbody>";
    $message.= "</table>";
    return $message;    
}
function buildEmployeeEducationList($row, $employeeResults) {
    $message = $row['message'];
    $message.= "<table border=\"1\" style=\"border-collapse:collapse;\" width=\"100%\">";
    $message.= "<thead>";
    $message.= "<tr>";
    $message.= "<th>Çalışan Adı</th>";
    $message.= "<th>Pernet No</th>";
    $message.= "<th>Görev Tanımı</th>";
    $message.= "<th>Eğitim / Sertifika</th>";
    $message.= "<th>Geçerlilik</th>";
    $message.= "<th>Geçerlilik Sonu</th>";
    $message.= "</tr>";
    $message.= "</thead>";
    $message.= "<tbody>";
    foreach ($employeeResults as $edRow) {
        $message.= "<tr>";
        $message.= "<td>".$edRow['employeeName']."</td>";
        $message.= "<td>".$edRow['pernetNumber']."</td>";
        $message.= "<td>".$edRow['jobTitleName']."</td>";
        $message.= "<td>".$edRow['educationName']."</td>";
        $message.= "<td>".$edRow['educationValidity']." ay</td>";
        $message.= "<td>".date('d-m-Y', strtotime($edRow['educationValidityEnd']))."</td>";
        $message.= "</tr>";
    }
    $message.= "</tbody>";
    $message.= "</table>";
    return $message;    
}
function buildEmployeeHealthDocList($row, $employeeResults) {
    $message = $row['message'];
    $message.= "<table border=\"1\" style=\"border-collapse:collapse;\" width=\"100%\">";
    $message.= "<thead>";
    $message.= "<tr>";
    $message.= "<th>Çalışan Adı</th>";
    $message.= "<th>Pernet No</th>";
    $message.= "<th>Görev Tanımı</th>";
    $message.= "<th>Evrak Adı</th>";
    $message.= "<th>Geçerlilik</th>";
    $message.= "<th>Geçerlilik Sonu</th>";
    $message.= "</tr>";
    $message.= "</thead>";
    $message.= "<tbody>";
    foreach ($employeeResults as $edRow) {
        $message.= "<tr>";
        $message.= "<td>".$edRow['employeeName']."</td>";
        $message.= "<td>".$edRow['pernetNumber']."</td>";
        $message.= "<td>".$edRow['jobTitleName']."</td>";
        $message.= "<td>".$edRow['healthDocName']."</td>";
        $message.= "<td>".$edRow['healthDocValidity']." ay</td>";
        $message.= "<td>".date('d-m-Y', strtotime($edRow['healthDocValidityEnd']))."</td>";
        $message.= "</tr>";
    }
    $message.= "</tbody>";
    $message.= "</table>";
    return $message;   
}
function unmarkCompleted($adapter) {
    $connection = $adapter->getDriver()->getConnection();
    $date = date('Y-m-d', strtotime("+1 day")); // add one day
    try {
        $connection->beginTransaction();
        $notifications = new TableGateway('notifications', $adapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
        $notifications->update(['processed' => 0], "processedAt >= '".$date."'");
        $connection->commit();
    } catch (Exception $e) {
        $connection->rollback();
        echo $e->getMessage().PHP_EOL;
    }
}
function markCompleted($row, $adapter) {
    $connection = $adapter->getDriver()->getConnection();
    try {
        $connection->beginTransaction();
        $notifications = new TableGateway('notifications', $adapter, null, new ResultSet(ResultSet::TYPE_ARRAY));
        $notifications->update(['processed' => 1, 'processedAt' => date("Y-m-d H:i:s")], ['notifyId' => $row['notifyId']]);
        $connection->commit();
    } catch (Exception $e) {
        $connection->rollback();
        echo $e->getMessage().PHP_EOL;
    }
}
function sendMailToUsers($container, $subject, $message, $users) {
    if (empty($users)) {
        return;
    }
    $notifyUsers = json_decode($users, true);
    $mailer = $container->get(Mailer::class);
    // $mailer->debugOutput();
    $mailer->clear();
    $mailer->isHtml(true);
    $to = "";
    foreach ($notifyUsers as $user) {
        $firstname = $user['firstname'];
        $lastname =  $user['lastname'];
        if (empty($firstname) || empty($lastname)) {
            $mailer->to($user['email']);
        } else {
            $name = $firstname." ".$lastname;
            $mailer->to($user['email'], $name);
        }
    }
    $mailer->subject($subject);
    $data = [
        'themeColor' => "#DC143C", // red
        'subject' => $subject,
        'message' => $message,
    ];
    $body = $mailer->getTemplate('systemNotification', $data);
    $mailer->body($body);
    $mailer->send();
}