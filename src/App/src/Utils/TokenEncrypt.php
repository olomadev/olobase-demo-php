<?php

declare(strict_types=1);

namespace App\Utils;

class TokenEncrypt
{
    private const CIPHER = "AES-256-CTR";

    protected $enabled = false;
    protected $secretKey;

    /**
     * Constructor
     * 
     * @param array $config framework config
     */
    public function __construct(array $config)
    {
        $this->enabled = $config['token']['encryption']['enabled'];
        $this->secretKey = $config['token']['encryption']['secret_key'];
    }

    /**
     * Encrypt data
     * 
     * @param  string $data data
     * @return string
     */
    public function encrypt(string $data)
    {
        if (! $this->enabled) {
            return $data;
        }
        $plaintext = trim($data);
        $ivlen = openssl_cipher_iv_length(Self::CIPHER);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $cipherTextRaw = openssl_encrypt($plaintext, Self::CIPHER, $this->secretKey, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $cipherTextRaw, $this->secretKey, $asBinary = true);
        $cipherText = base64_encode($iv . $hmac . $cipherTextRaw);
        return $cipherText;
    }

    /**
     * Decrypt data
     * 
     * @param  string $data data
     * @return string
     */
    public function decrypt(string $data)
    {
        if (! $this->enabled) {
            return $data;
        }
        $c = base64_decode(trim($data));
        $ivlen = openssl_cipher_iv_length(Self::CIPHER);
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $cipherTextRaw = substr($c, $ivlen + $sha2len);
        $originalPlaintext = openssl_decrypt($cipherTextRaw, Self::CIPHER, $this->secretKey, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $cipherTextRaw, $this->secretKey, $asBinary = true);
        if (hash_equals($hmac, $calcmac)) {
            return $originalPlaintext;
        }
    }

}


