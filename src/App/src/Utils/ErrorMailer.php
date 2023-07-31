<?php
declare(strict_types=1);

namespace App\Utils;

use Laminas\Db\Sql\Sql;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\I18n\Translator\TranslatorInterface;

class ErrorMailer
{
	protected $uri;
	protected $mailer;
	protected $server;
	protected $errors;
	protected $details;
	protected $exception;

	public function __construct(Mailer $mailer, TableGatewayInterface $errors)
	{
		$this->mailer = $mailer;
		$this->errors = $errors;
		$this->adapter = $errors->getAdapter();
	}

	public function setUri(string $uri)
	{
		$this->uri = $uri;
	}

	public function setServerParams($server)
	{
		$this->server = $server;
	}

    public function setEnv($env)
    {
        $this->mailer->setEnv($env);
    }

	public function setException($e)
	{
		$this->exception = $e;
	}

	public function getException()
	{
		return $this->exception;
	}

	public function setDetails($details)
	{
		$this->details = $details;
	}

	public function send()
	{
		$e = $this->getException();
		$errorId = md5($e->getFile().$e->getLine().date('Y-m-d'));

		// if the "errorId" is not in the db, let's send an e-mail and save the error to the db.
		//
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        $select->from('errors');
        $select->where(['errorId' => $errorId]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = $statement->execute();
        $row = $resultSet->current();
        $statement->getResource()->closeCursor();

        if (false == $row) {
		    $data = $e->getTrace();
		    $trace = array_map(
		        function ($a) {
		            if (isset($a['file'])) {
		                $a['file'] = str_replace(PROJECT_ROOT, '', $a['file']);
		            }
		            return $a;
		        },
		        $data
		    );
		    $title = get_class($e);
			$filename = str_replace(PROJECT_ROOT, '', $e->getFile());
			$line = $e->getLine();
			$message = $e->getMessage();
		    $json = [
		        'title' => $title,
		        'file'  => $filename,
		        'line'  => $line,
		        'error' => $message,
		        'trace' => $trace,
		    ];
		    $errorString = print_r($json, true);

			$this->mailer->clear();
		    $this->mailer->isHtml(true);
		    $this->mailer->to('ersin.guvenc@pernet.com.tr');
		    $this->mailer->subject('Pernet CRM Hatası: #'.$filename.' #'.$line);
		    $body = '<b>Url:</b>'.$this->uri.'<br>';
		    $body.= '<b>Error id:</b> '.$errorId.'<br>';
		    $body.= '<b>Tarih: '.date('d-m-Y H:i:s').'</b>'.'<br><br>';
		   	$body.= '<pre>'.print_r($this->server, true).'<pre><br>';
		    $body.= '<pre>'.$errorString.'<pre><br>';
		    if (! empty($this->details)) {
		    	$body.='<pre>'.(string)$this->details.'</pre>';
		    }
		    $this->mailer->body($body);
		    $this->mailer->send();

		    $data = array();
		    $data['errorId'] = (string)$errorId;
		    $data['errorTitle'] = (string)$title;
		    $data['errorFile'] = (string)$filename;
		    $data['errorLine'] = $line;
		    $data['errorMessage'] = (string)$message;
		    $data['errorDate'] = date('Y-m-d');
		    $this->errors->insert($data);
        }
	}
}