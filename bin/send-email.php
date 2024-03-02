<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

use Laminas\Mime\Part as MimePart;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;

$args = $_SERVER['argv'];
if (empty($args[1]) || empty($args[2])) {
    echo "Mail arguments cannot be empty !".PHP_EOL;
}
putenv("APP_ENV=$args[1]");
//
// WARNING !
// 
// config container must be declared after putenv("APP_ENV=$args[1]")
// functions.
//
$container = require dirname(__DIR__).'/config/container.php';
$config = $container->get('config');

$decodedString = base64_decode($args[2]);
if (false == $decodedString) {
    echo("\033[31mBase64 decode error ! \033[0m");
    throw new Exception('Send email base64 decode error !');
    echo PHP_EOL;
    exit(1);
}
parse_str($decodedString, $params);

//--------------------------------------------------------------------------
// Build mail parameters
//
$isHtml = (bool)$params['isHtml'];
$from = str_replace(['<','>'], '', $params['from']);
$fromName = isset($params['fromName']) ? $params['fromName'] : null;
$body = empty($params['body']) ? '' : (string)$params['body'];
$subject = empty($params['subject']) ? '' : (string)$params['subject'];

// https://discourse.laminas.dev/t/zend-smtp-dkim-not-passing/1194
// 
// Setup SMTP transport
// 
$transport = new SmtpTransport();
$options   = new SmtpOptions([
    'host' => $config['smtp']['host'],
    'port' => $config['smtp']['port'],
    'connection_class'  => 'login', // 'plain',
    'connection_config' => [
        'username' => $config['smtp']['username'],
        'password' => $config['smtp']['password'],
        'ssl'      => 'tls',
    ],
]);
$transport->setOptions($options);

// Build messsage

$html = new MimePart($body);
$html->charset = "UTF-8";
if ($isHtml) { 
    $html->type = "text/html";
} else {
    $html->type = "text/plain";
}
$body = new MimeMessage();
$body->addPart($html);
// $body->setParts(array($html));

$message = new Message();
$message->setEncoding('UTF-8');
foreach ($params['to'] as $toEmailStr) {
    if (isset($params['name'][$toEmailStr])) {
        $message->addTo($toEmailStr, $params['name'][$toEmailStr]);
    } else {
        $message->addTo($toEmailStr);
    }
}
if (! empty($params['cc'])) {
    foreach ($params['cc'] as $ccEmailStr) {
        if (isset($params['name'][$ccEmailStr])) {
            $message->addCc($ccEmailStr, $params['name'][$ccEmailStr]);
        } else {
            $message->addCc($ccEmailStr);
        }
    }
}
if (! empty($params['bcc'])) {
    foreach ($params['bcc'] as $bccEmailStr) {
        if (isset($params['name'][$bccEmailStr])) {
            $message->addBcc($bccEmailStr, $params['name'][$bccEmailStr]);
        } else {
            $message->addBcc($bccEmailStr);
        }
    }    
}
$message->addFrom($from, $fromName);
$message->setSubject($subject);
$message->setBody($body);

// Send transport
// 
$transport->send($message);