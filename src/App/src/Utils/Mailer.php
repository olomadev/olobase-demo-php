<?php
declare(strict_types=1);

namespace App\Utils;

use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;

/**
 * @author Oloma <support@oloma.dev>
 *
 * Mail sender
 */
class Mailer
{
	use EnvTrait;

	protected $to = array();
	protected $cc = array();
	protected $bcc = array();
    protected $name = array();
	protected $from = 'bildirimler@pernet.com.tr';
	protected $fromName;
	protected $subject;
	protected $body;
	protected $isHtml = false;
	protected $attachments = array();
	
	private $debugOutput = false;

    public function __construct(TranslatorInterface $translator)
    {
    	$this->translator = $translator;
        $env = getenv('APP_ENV') ?: 'local';
        $this->setEnv($env);
    }
    
    public function setLocale(string $locale = null)
    {
    	if (! empty($locale)) {
			$this->translator->setLocale($locale);
    	}
    }
    
	public function from(string $from, string $fromName = null)
	{
		$this->from = trim($from);
		$this->fromName = trim($fromName);
	}

	public function to(string $to, $name = null)
	{
		$this->to[] = trim($to);
        $this->name[$to] = $name;
	}

	public function cc(string $cc, $name = null)
	{
		$this->cc[] = trim($cc);
        $this->name[$cc] = $name;
	}

	public function bcc(string $bcc, $name = null)
	{
		$this->bcc[] = trim($bcc);
        $this->name[$bcc] = $name;
	}

	public function subject(string $subject)
	{
		$this->subject = trim($subject);
	}

	public function getTemplate(string $name, array $data = array())
	{
		$translator = $this->translator;
		extract($data, EXTR_SKIP);
		ob_start();
		require PROJECT_ROOT. '/data/templates/'.$name.'.php';
		$content = ob_get_clean();
		return $content;
	}

	public function body(string $body)
	{
		$this->body = $body;
	}

	public function attachment(string $path, string $filename = null)
	{
		$this->attachments[] = ['filePath' => $path, 'fileName' => $filename];
	}

	public function isHtml($bool = false)
	{
		$this->isHtml = $bool;
	}

	public function debugOutput()
	{
		$this->debugOutput = true;
	}

	public function clear()
	{
		$this->isHtml = false;
		$this->to = array();
		$this->cc = array();
		$this->bcc = array();
		$this->subject = null;
		$this->body = null;
		$this->attachments = array();
	}

    /**
     * Send email in background
     */
	public function send()
	{
		$queryParams = array();
		$data = get_object_vars($this);
		unset($data['translator'], $data['variables'], $data['debugOutput']);

		$queryParams = http_build_query($data);
		$projectRoot = PROJECT_ROOT;

		// $command = 'php '.$projectRoot.'/bin/send-email.php '.$this->getEnv().' '.base64_encode(urldecode($queryParams));
		// exec($command . " > /dev/null &"); 
		
		// https://stackoverflow.com/questions/6014819/how-to-get-output-of-proc-open
		
		$descriptorSpec = array(
		    0 => array("pipe", "r"),
		    1 => array("pipe", "w"),
		    2 => array("file", PROJECT_ROOT."/data/error-output.txt", "a")
		);
	    $command = 'php '.$projectRoot.'/bin/send-email-smtp.php '.$this->getEnv().' '.base64_encode(urldecode($queryParams)).' ./a a.out';
		$process = proc_open($command, $descriptorSpec, $pipes, $projectRoot.'/bin');

		if ($this->debugOutput) {
			echo stream_get_contents($pipes[1]);
		}
		fclose($pipes[1]);
		// proc_close($process);  // process close yapma yaparsak asenktron olmaz
	    if ($this->debugOutput) {
			echo $command.PHP_EOL;
	    	print_r(urldecode($queryParams));
	    }
	}
}