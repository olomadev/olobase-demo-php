<?php

namespace App\Utils;

use Predis\ClientInterface as Predis;
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
    protected $from = 'example@example.com';
    protected $fromName = 'Example From Name';
    protected $subject;
    protected $body;
    protected $isHtml = true;
    protected $attachments = array();
    protected $config = array();
    
    private $debugOutput = false;

    public function __construct(
        array $config,
        Predis $predis,
        TranslatorInterface $translator
    )
    {
        $this->config = $config;
        $this->predis = $predis;
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
        $this->subject = $this->translator->translate(trim($subject), 'templates');
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
        $data = array();
        $data['from'] = $this->from;
        $data['fromName'] = $this->fromName;
        $data['to'] = $this->to;
        $data['cc'] = $this->cc;
        $data['bcc'] = $this->bcc;
        $data['subject'] = $this->subject;
        $data['body'] = $this->body;
        $data['attachments'] = $this->attachments;
        $data['isHtml'] = true;
        //
        // send to queue
        // https://www.vultr.com/docs/implement-redis-queue-and-worker-with-php-on-ubuntu-20-04/
        // 
        $this->predis->rpush(
            "mailer", 
            json_encode($data)
        );
        if ($this->debugOutput) {
            print_r($data);
        }
    }
}