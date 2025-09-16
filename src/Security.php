<?php

namespace Mofei;

/**
 * 安全加密类
 * 提供URL安全的加密解密功能，支持多种加密引擎和模式
 */
class Security
{
    // 加密引擎常量
    const ENGINE_AUTO = 'auto';
    const ENGINE_SODIUM = 'sodium';
    const ENGINE_OPENSSL = 'openssl';

    // 加密模式常量
    const MODE_STANDARD = 'standard';
    const MODE_URL_SAFE = 'url_safe';
    const MODE_COMPACT = 'compact';

    // 默认加密算法
    const DEFAULT_ALGORITHM = 'AES-256-GCM';

    private string $key;
    private string $engine;
    private string $mode;

    /**
     * 构造函数
     *
     * @param string $key 加密密钥
     * @param string $engine 加密引擎
     * @param string $mode 加密模式
     */
    public function __construct(string $key, string $engine = self::ENGINE_AUTO, string $mode = self::MODE_STANDARD)
    {
        $this->key = $key;
        $this->engine = $this->selectEngine($engine);
        $this->mode = $mode;
    }

    /**
     * 获取默认密钥
     * @return string
     */
    public static function getDefaultKey(): string
    {
        return defined('SECURE_CRYPTO_KEY') ? SECURE_CRYPTO_KEY : 'default_secure_key_2024';
    }

    /**
     * 选择加密引擎
     *
     * @param string|null $engine
     * @return string
     */
    private function selectEngine(?string $engine = null): string
    {
        // 如果没有指定引擎，则使用实例的引擎设置
        if ($engine === null) {
            $engine = $this->engine;
        }

        if ($engine === self::ENGINE_SODIUM && extension_loaded('sodium')) {
            return self::ENGINE_SODIUM;
        } elseif ($engine === self::ENGINE_OPENSSL && extension_loaded('openssl')) {
            return self::ENGINE_OPENSSL;
        } elseif ($engine === self::ENGINE_AUTO) {
            if (extension_loaded('sodium')) {
                return self::ENGINE_SODIUM;
            } elseif (extension_loaded('openssl')) {
                return self::ENGINE_OPENSSL;
            }
        }

        throw new \RuntimeException('No available encryption engine found');
    }

    /**
     * 加密数据
     *
     * @param string $data 要加密的数据
     * @return string 加密后的字符串
     */
    public function encrypt(string $data): string
    {
        if ($this->engine === self::ENGINE_SODIUM) {
            return $this->encryptWithSodium($data);
        } else {
            return $this->encryptWithOpenSSL($data);
        }
    }

    /**
     * 解密数据
     *
     * @param string $encrypted 加密后的字符串
     * @return string 解密后的数据
     * @throws \Exception
     */
    public function decrypt(string $encrypted): string
    {
        $engine = $this->selectEngine($this->engine);

        if ($engine === self::ENGINE_SODIUM) {
            return $this->decryptWithSodium($encrypted);
        } else {
            return $this->decryptWithOpenSSL($encrypted);
        }
    }

    /**
     * OpenSSL加密
     * @param string $data 要加密的数据
     * @param string|null $key 密钥
     * @return string 加密后的数据
     * @throws \Exception
     */
    public static function openssl_encrypt(string $data, ?string $key = null): string
    {
        $key = $key ?? self::getDefaultKey();
        $key = hash('sha256', $key, true); // 确保密钥长度正确

        $ivLength = openssl_cipher_iv_length(self::DEFAULT_ALGORITHM);
        $iv = openssl_random_pseudo_bytes($ivLength);

        $tag = '';
        $encrypted = openssl_encrypt($data, self::DEFAULT_ALGORITHM, $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($encrypted === false) {
            throw new \Exception('Encryption failed: ' . openssl_error_string());
        }

        // 对于GCM模式，需要将IV、标签和加密数据一起返回
        return base64_encode($iv . $tag . $encrypted);
    }

    /**
     * OpenSSL解密
     * @param string $data 要解密的数据
     * @param string|null $key 密钥
     * @return string 解密后的数据
     * @throws \Exception
     */
    public static function openssl_decrypt(string $data, ?string $key = null): string
    {
        $key = $key ?? self::getDefaultKey();
        $key = hash('sha256', $key, true); // 确保密钥长度正确

        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length(self::DEFAULT_ALGORITHM);
        $tagLength = 16; // GCM标签长度

        $iv = substr($data, 0, $ivLength);
        $tag = substr($data, $ivLength, $tagLength);
        $encrypted = substr($data, $ivLength + $tagLength);

        $decrypted = openssl_decrypt($encrypted, self::DEFAULT_ALGORITHM, $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($decrypted === false) {
            throw new \Exception('Decryption failed: ' . openssl_error_string());
        }

        return $decrypted;
    }

    /**
     * Sodium加密（需要安装Sodium扩展）
     * @param string $data 要加密的数据
     * @param string|null $key 密钥
     * @return string 加密后的数据
     * @throws \Exception
     */
    public static function sodium_encrypt(string $data, ?string $key = null): string
    {
        if (!extension_loaded('sodium')) {
            throw new \Exception('Sodium extension is not loaded');
        }

        $key = $key ?? self::getDefaultKey();
        $key = hash('sha256', $key, true); // 确保密钥长度为32字节

        // 生成随机nonce
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES);

        // 加密数据
        $encrypted = sodium_crypto_aead_aes256gcm_encrypt($data, '', $nonce, $key);

        // 返回nonce和加密数据的组合
        return base64_encode($nonce . $encrypted);
    }

    /**
     * Sodium解密（需要安装Sodium扩展）
     * @param string $data 要解密的数据
     * @param string|null $key 密钥
     * @return string 解密后的数据
     * @throws \Exception
     */
    public static function sodium_decrypt(string $data, ?string $key = null): string
    {
        if (!extension_loaded('sodium')) {
            throw new \Exception('Sodium extension is not loaded');
        }

        $key = $key ?? self::getDefaultKey();
        $key = hash('sha256', $key, true); // 确保密钥长度为32字节

        $data = base64_decode($data);
        $nonceLength = SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES;

        $nonce = substr($data, 0, $nonceLength);
        $encrypted = substr($data, $nonceLength);

        $decrypted = sodium_crypto_aead_aes256gcm_decrypt($encrypted, '', $nonce, $key);

        if ($decrypted === false) {
            throw new \Exception('Decryption failed');
        }

        return $decrypted;
    }

    /**
     * 使用Sodium加密
     * @param string $data
     * @return string
     */
    private function encryptWithSodium(string $data): string
    {
        $key = hash('sha256', $this->key, true); // 确保密钥长度为32字节
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES);
        $encrypted = sodium_crypto_aead_aes256gcm_encrypt($data, '', $nonce, $key);
        $result = base64_encode($nonce . $encrypted);

        // 根据模式进行处理
        if ($this->mode === self::MODE_URL_SAFE) {
            $result = strtr($result, '+/', '-_');
            $result = rtrim($result, '=');
        } elseif ($this->mode === self::MODE_COMPACT) {
            // 转换为十六进制字符串，去除非十六进制字符
            $result = bin2hex(base64_decode($result));
        }

        return $result;
    }

    /**
     * 使用Sodium解密
     * @param string $encrypted
     * @return string
     * @throws \Exception
     */
    private function decryptWithSodium(string $encrypted): string
    {
        // 根据模式进行预处理
        if ($this->mode === self::MODE_URL_SAFE) {
            $encrypted = str_pad(strtr($encrypted, '-_', '+/'), strlen($encrypted) % 4, '=', STR_PAD_RIGHT);
        } elseif ($this->mode === self::MODE_COMPACT) {
            // 对于紧凑型，先将十六进制转回二进制
            $encrypted = hex2bin($encrypted);
        }

        // 对于URL_SAFE和STANDARD模式，需要base64解码
        if ($this->mode !== self::MODE_COMPACT) {
            $data = base64_decode($encrypted);
        } else {
            $data = $encrypted;
        }

        $key = hash('sha256', $this->key, true); // 确保密钥长度为32字节
        $nonceLength = SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES;

        if (strlen($data) < $nonceLength) {
            throw new \Exception('Invalid encrypted data');
        }

        $nonce = substr($data, 0, $nonceLength);
        $ciphertext = substr($data, $nonceLength);

        $decrypted = sodium_crypto_aead_aes256gcm_decrypt($ciphertext, '', $nonce, $key);

        if ($decrypted === false) {
            throw new \Exception('Decryption failed');
        }

        return $decrypted;
    }

    /**
     * 使用OpenSSL加密
     * @param string $data
     * @return string
     * @throws \Exception
     */
    private function encryptWithOpenSSL(string $data): string
    {
        $key = hash('sha256', $this->key, true); // 确保密钥长度正确
        $ivLength = openssl_cipher_iv_length(self::DEFAULT_ALGORITHM);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $tag = '';

        $encrypted = openssl_encrypt($data, self::DEFAULT_ALGORITHM, $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($encrypted === false) {
            throw new \Exception('Encryption failed: ' . openssl_error_string());
        }

        $result = base64_encode($iv . $tag . $encrypted);

        // 根据模式进行处理
        if ($this->mode === self::MODE_URL_SAFE) {
            $result = strtr($result, '+/', '-_');
            $result = rtrim($result, '=');
        } elseif ($this->mode === self::MODE_COMPACT) {
            // 转换为十六进制字符串，去除非十六进制字符
            $result = bin2hex(base64_decode($result));
        }

        return $result;
    }

    /**
     * 使用OpenSSL解密
     * @param string $encrypted
     * @return string
     * @throws \Exception
     */
    private function decryptWithOpenSSL(string $encrypted): string
    {
        // 根据模式进行预处理
        if ($this->mode === self::MODE_URL_SAFE) {
            $encrypted = str_pad(strtr($encrypted, '-_', '+/'), strlen($encrypted) % 4, '=', STR_PAD_RIGHT);
        } elseif ($this->mode === self::MODE_COMPACT) {
            // 对于紧凑型，先将十六进制转回二进制
            $data = hex2bin($encrypted);
        }

        // 对于URL_SAFE和STANDARD模式，需要base64解码
        if ($this->mode !== self::MODE_COMPACT) {
            $key = hash('sha256', $this->key, true); // 确保密钥长度正确
            $data = base64_decode($encrypted);
        }

        $ivLength = openssl_cipher_iv_length(self::DEFAULT_ALGORITHM);
        $tagLength = 16; // GCM标签长度

        if (strlen($data) < $ivLength + $tagLength) {
            throw new \Exception('Invalid encrypted data');
        }

        $iv = substr($data, 0, $ivLength);
        $tag = substr($data, $ivLength, $tagLength);
        $ciphertext = substr($data, $ivLength + $tagLength);

        $key = hash('sha256', $this->key, true); // 确保密钥长度正确
        $decrypted = openssl_decrypt($ciphertext, self::DEFAULT_ALGORITHM, $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($decrypted === false) {
            throw new \Exception('Decryption failed: ' . openssl_error_string());
        }

        return $decrypted;
    }

    /**
     * 验证数据签名
     * @param mixed $data 要验证的数据
     * @param string $signature 签名
     * @param string $publicKey 公钥
     * @param string $algorithm 签名算法
     * @return bool
     * @throws \Exception
     */
    public static function verifySignature($data, string $signature, string $publicKey, string $algorithm = OPENSSL_ALGO_SHA256): bool
    {
        if (is_array($data) || is_object($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        $signature = base64_decode($signature);
        if ($signature === false) {
            throw new \Exception('Invalid signature format');
        }

        $result = openssl_verify($data, $signature, $publicKey, $algorithm);
        if ($result === -1) {
            throw new \Exception('Verification failed: ' . openssl_error_string());
        }

        return $result === 1;
    }

    /**
     * 检查字符串是否为有效的JSON
     * @param string $string 要检查的字符串
     * @return bool
     */
    private static function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * 生成安全随机数
     *
     * @param int $length 随机数长度
     * @return string 安全随机数
     */
    public static function generateSecureRandom(int $length = 32): string
    {
        if (extension_loaded('sodium')) {
            return bin2hex(random_bytes($length));
        } elseif (extension_loaded('openssl')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        } else {
            throw new \RuntimeException('No available random generator found');
        }
    }

    /**
     * 安全比较两个字符串（防止时序攻击）
     *
     * @param string $knownString 已知字符串
     * @param string $userString 用户输入字符串
     * @return bool 是否相等
     */
    public static function secureCompare(string $knownString, string $userString): bool
    {
        return hash_equals($knownString, $userString);
    }

    /**
     * 获取加密信息
     *
     * @return array 加密信息
     */
    public function getInfo(): array
    {
        return [
            'engine' => $this->engine,
            'mode' => $this->mode,
            'sodium_available' => extension_loaded('sodium'),
            'openssl_available' => extension_loaded('openssl'),
            'openssl_cipher' => self::DEFAULT_ALGORITHM
        ];
    }

    /**
     * 加密数据（带过期时间）
     *
     * @param mixed $data 要加密的数据
     * @param int $exp 过期时间（秒），0表示永不过期
     * @param int|null $currentTime 用于测试的当前时间（可选）
     * @return string 加密后的字符串
     */
    public static function encryptWithExpiry($data, int $exp = 0, ?int $currentTime = null): string
    {
        $key = self::getDefaultKey();
        $crypto = new self($key, self::ENGINE_AUTO, self::MODE_URL_SAFE);
        
        // 使用传入的当前时间或实际当前时间
        $time = $currentTime ?? time();
        
        // 构建包含过期时间的数据
        $payload = json_encode([
            'data' => $data,
            'exp' => $exp > 0 ? $time + $exp : 0
        ]);
        
        return $crypto->encrypt($payload);
    }

    /**
     * 解密数据并检查过期时间
     *
     * @param string $encrypted 加密后的字符串
     * @return mixed 解密后的数据
     * @throws \Exception
     */
    public static function decryptWithExpiry(string $encrypted)
    {
        $key = self::getDefaultKey();
        $crypto = new self($key, self::ENGINE_AUTO, self::MODE_URL_SAFE);
        $data = $crypto->decrypt($encrypted);
        $json = json_decode($data, true);

        // 检查JSON解码是否成功
        if ($json === null) {
            throw new \Exception('Invalid token format');
        }

        // 检查是否过期，exp为0表示永不过期
        if (isset($json['exp']) && $json['exp'] > 0 && time() > $json['exp']) {
            throw new \Exception('Token已过期');
        }

        // 检查data字段是否存在
        if (!isset($json['data'])) {
            throw new \Exception('Missing data in token');
        }

        return $json['data'];
    }

    /**
     * URL安全的加密方法
     * 
     * @param string $data 要加密的数据
     * @param string|null $key 加密密钥
     * @return string 加密后的字符串
     */
    public static function encryptForUrl(string $data, ?string $key = null): string
    {
        $crypto = new self($key ?? self::getDefaultKey(), self::ENGINE_AUTO, self::MODE_URL_SAFE);
        return $crypto->encrypt($data);
    }
    
    /**
     * URL安全的解密方法
     * 
     * @param string $encrypted 加密后的字符串
     * @param string|null $key 解密密钥
     * @return string 解密后的数据
     * @throws \Exception
     */
    public static function decryptFromUrl(string $encrypted, ?string $key = null): string
    {
        $crypto = new self($key ?? self::getDefaultKey(), self::ENGINE_AUTO, self::MODE_URL_SAFE);
        return $crypto->decrypt($encrypted);
    }
    
    /**
     * Token加密方法
     * 
     * @param string $data 要加密的数据
     * @param string|null $key 加密密钥
     * @param int $expiry 过期时间（秒），0表示不过期，负数表示已过期
     * @param int|null $currentTime 用于测试的当前时间（可选）
     * @return string 加密后的Token
     */
    public static function encryptForToken(string $data, ?string $key = null, int $expiry = 0, ?int $currentTime = null): string
    {
        $timestamp = $currentTime ?? time();
        // 处理过期时间：如果为负数，则表示已过期
        if ($expiry < 0) {
            $exp = $timestamp + $expiry; // 负数的expiry会使exp小于当前时间
        } else {
            $exp = $expiry > 0 ? $timestamp + $expiry : 0;
        }
        
        $payload = [
            'data' => $data,
            'timestamp' => $timestamp,
            'exp' => $exp
        ];

        $jsonData = json_encode($payload);
        return self::encryptForUrl($jsonData, $key);
    }
    
    /**
     * Token解密方法
     *
     * @param string $encrypted
     * @param string|null $key
     * @return string
     * @throws \Exception
     */
    public static function decryptFromToken(string $encrypted, ?string $key = null): string
    {
        $crypto = new self($key ?? self::getDefaultKey(), self::ENGINE_AUTO, self::MODE_URL_SAFE);
        $data = $crypto->decrypt($encrypted);
        $json = json_decode($data, true);
        
        // 验证JSON解析
        if ($json === null) {
            throw new \Exception('Invalid token format');
        }
        
        // 验证数据字段存在
        if (!isset($json['data'])) {
            throw new \Exception('Missing data in token');
        }

        // 检查是否过期，exp为0表示永不过期
        if (isset($json['exp']) && $json['exp'] > 0 && time() > $json['exp']) {
            throw new \Exception('Token已过期');
        }

        return $json['data'];
    }
}
