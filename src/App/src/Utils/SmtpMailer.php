<?php

namespace App\Utils;

use Laminas\I18n\Translator\TranslatorInterface;

/**
 * @author Oloma <support@oloma.dev>
 *
 * Mail sender
 */
class SmtpMailer
{
    protected $to = array();
    protected $cc = array();
    protected $bcc = array();
    protected $name = array();
    protected $from = null;
    protected $fromName = null;
    protected $subject;
    protected $body;
    protected $isHtml = true;
    protected $attachments = array();
    protected $config = array();
    protected $translator;
    protected $debugOutput = false;

    public function __construct(array $config, TranslatorInterface $translator)
    {
        $this->config = $config;
        $this->from = $config['smtp']['from'];
        $this->fromName = $config['smtp']['from_name'];
        $this->translator = $translator;
        $this->env = getenv('APP_ENV') ?: 'local';
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
        $this->isHtml = true;
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
        $this->isHtml = true;
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

        if (false == $this->debugOutput) {
            //
            // send email in the background
            // 
            $queryParams = http_build_query($data);
            $command = 'php '.PROJECT_ROOT.'/bin/send-email.php '.$this->env.' '.base64_encode(urldecode($queryParams));
            exec($command . " > /dev/null &"); 
        } else {
            // This will write error output to "/data/tmp/error-output.txt"
            //
            // https://stackoverflow.com/questions/6014819/how-to-get-output-of-proc-open       
            //  
            $descriptorSpec = array(
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
                2 => array("file", PROJECT_ROOT."/data/tmp/error-output.txt", "a")
            );
            $command = 'php '.PROJECT_ROOT.'/bin/send-email.php '.$this->env.' '.base64_encode(urldecode($queryParams)).' ./a a.out';
            $process = proc_open($command, $descriptorSpec, $pipes, PROJECT_ROOT.'/bin');
            // echo stream_get_contents($pipes[1]);
            fclose($pipes[1]);
        } 
    }
}