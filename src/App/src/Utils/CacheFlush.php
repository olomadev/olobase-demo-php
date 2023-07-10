<?php

namespace App\Utils;

/**
 * @author Ersin Güvenç <eguvenc@gmail.com>
 *
 * Remove specific cache keys
 */
class CacheFlush
{
    use EnvTrait;

    protected $requestedClass;
    protected $requestedFuncArray = array();
    protected $debugOutput = false;

    public function __construct()
    {
        $env = getenv('APP_ENV') ?: 'local';
        $this->setEnv($env);
    }

    /**
     * Enable debugging
     * 
     * @return void
     */
    public function debugOutput()
    {
        $this->debugOutput = true;
    }
    
    /**
     * Remove keys
     * 
     * @param  string|array $requestedClass    fully qualified class name(s)
     * @param  string  $requestedFuncArray class method names
     * @return void
     */
    public function removeKeys($requestedClass, array $requestedFuncArray)
    {
        $data = array();
        $data['requestedClass'] = (array)$requestedClass;
        $data['requestedFuncArray'] = $requestedFuncArray;
        $data['allowedClientsConsultantId'] = $this->getAllowedClientsConsultantId();
        $queryParams = http_build_query($data);
        $projectRoot = str_replace('/mnt/c/www/', '/var/www/', PROJECT_ROOT);
        
        // https://stackoverflow.com/questions/6014819/how-to-get-output-of-proc-open
        // 
        $descriptorSpec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("file", PROJECT_ROOT."/data/error-output.txt", "a")
        );
        $command = 'php '.$projectRoot.'/bin/flush-cache.php '.$this->getEnv().' '.base64_encode(urldecode($queryParams)).' ./a a.out';
        $process = proc_open($command, $descriptorSpec, $pipes, $projectRoot.'/bin');

        if ($this->debugOutput) {
            echo stream_get_contents($pipes[1]);
        }
        fclose($pipes[1]);
        // proc_close($process);  // process close yapma yaparsak asenkron olmaz
        if ($this->debugOutput) {
            echo $command.PHP_EOL;
            print_r(urldecode($queryParams));
        }
    }

}