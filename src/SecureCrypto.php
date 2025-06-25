<?php

namespace mofei;

/**
 * 安全加密工具类
 * 整合OpenSSL和Sodium的优势，提供多种加密方案
 * 特别针对URL参数和Token场景优化
 * 使用PHP8.1+特性
 */
class SecureCrypto
{
    /**
     * 加密引擎类型
     */
    public const ENGINE_OPENSSL = 'openssl';
    public const ENGINE_SODIUM = 'sodium';
    public const ENGINE_AUTO = 'auto';
    
    /**
     * 加密模式
     */
    public const MODE_STANDARD = 'standard';     // 标准加密，适合文件、数据库存储
    public const MODE_URL_SAFE = 'url_safe';     // URL安全，适合URL参数
    public const MODE_TOKEN = 'token';           // Token模式，适合JWT等场景
    public const MODE_COMPACT = 'compact';       // 紧凑模式，最小化输出长度
    
    /**
     * OpenSSL默认配置
     */
    private const OPENSSL_CIPHER = 'AES-256-GCM';
    private const OPENSSL_TAG_LENGTH = 16;
    
    /**
     * 默认配置
     */
    private const DEFAULT_SALT = 'mofei_secure_2024';
    
    /**
     * 实例配置
     */
    private string $masterKey;
    private string $defaultSalt;
    private string $engine;
    private string $mode;
    
    /**
     * 构造函数
     */
    public function __construct(
        string $masterKey = self::DEFAULT_SALT,
        string $engine = self::ENGINE_AUTO,
        string $mode = self::MODE_STANDARD
    ) {
        $this->masterKey = $masterKey;
        $this->defaultSalt = self::DEFAULT_SALT;
        $this->engine = $engine;
        $this->mode = $mode;
        
        // 检查扩展支持
        $this->validateExtensions();
    }
    
    /**
     * 静态快速加密 - URL安全
     */
    public static function encryptForUrl(string $data, ?string $key = null): string
    {
        $crypto = new self($key ?? self::DEFAULT_SALT, self::ENGINE_SODIUM, self::MODE_URL_SAFE);
        return $crypto->encrypt($data);
    }
    
    /**
     * 静态快速解密 - URL安全
     */
    public static function decryptFromUrl(string $encrypted, ?string $key = null): string
    {
        $crypto = new self($key ?? self::DEFAULT_SALT, self::ENGINE_SODIUM, self::MODE_URL_SAFE);
        return $crypto->decrypt($encrypted);
    }
    
    /**
     * 静态快速加密 - Token模式
     */
    public static function encryptForToken(string $data, ?string $key = null, int $expiry = 0): string
    {
        $crypto = new self($key ?? self::DEFAULT_SALT, self::ENGINE_SODIUM, self::MODE_TOKEN);
        
        // Token模式支持过期时间
        if ($expiry > 0) {
            $payload = [
                'data' => $data,
                'exp' => time() + $expiry,
                'iat' => time()
            ];
            $data = json_encode($payload);
        }
        
        return $crypto->encrypt($data);
    }
    
    /**
     * 静态快速解密 - Token模式
     */
    public static function decryptFromToken(string $encrypted, ?string $key = null): string
    {
        $crypto = new self($key ?? self::DEFAULT_SALT, self::ENGINE_SODIUM, self::MODE_TOKEN);
        $decrypted = $crypto->decrypt($encrypted);
        
        // 尝试解析JSON格式的Token
        $payload = json_decode($decrypted, true);
        if (is_array($payload) && isset($payload['data'], $payload['exp'], $payload['iat'])) {
            // 检查过期时间
            if ($payload['exp'] > 0 && time() > $payload['exp']) {
                throw new \Exception('Token已过期');
            }
            return $payload['data'];
        }
        
        return $decrypted;
    }
    
    /**
     * 主加密方法
     */
    public function encrypt(string $data, ?string $salt = null): string
    {
        $salt = $salt ?? $this->defaultSalt;
        $engine = $this->determineEngine();
        
        return match($engine) {
            self::ENGINE_SODIUM => $this->encryptWithSodium($data, $salt),
            self::ENGINE_OPENSSL => $this->encryptWithOpenSSL($data, $salt),
            default => throw new \InvalidArgumentException('不支持的加密引擎')
        };
    }
    
    /**
     * 主解密方法
     */
    public function decrypt(string $encrypted, ?string $salt = null): string
    {
        $salt = $salt ?? $this->defaultSalt;
        
        // 自动检测加密引擎（通过前缀）
        $engine = $this->detectEngine($encrypted);
        
        return match($engine) {
            self::ENGINE_SODIUM => $this->decryptWithSodium($encrypted, $salt),
            self::ENGINE_OPENSSL => $this->decryptWithOpenSSL($encrypted, $salt),
            default => throw new \InvalidArgumentException('无法识别的加密格式')
        };
    }
    
    /**
     * 使用Sodium加密
     */
    private function encryptWithSodium(string $data, string $salt): string
    {
        // 生成密钥
        $key = $this->deriveKey($salt, SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
        
        // 生成随机nonce
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        
        // 加密
        $ciphertext = sodium_crypto_secretbox($data, $nonce, $key);
        
        // 清理敏感数据
        sodium_memzero($key);
        
        // 组合数据
        $combined = $nonce . $ciphertext;
        
        // 根据模式编码
        return $this->encodeOutput('s:' . $combined);
    }
    
    /**
     * 使用Sodium解密
     */
    private function decryptWithSodium(string $encrypted, string $salt): string
    {
        // 解码数据
        $decoded = $this->decodeInput($encrypted);
        
        // 移除前缀
        if (!str_starts_with($decoded, 's:')) {
            throw new \Exception('无效的Sodium加密格式');
        }
        $data = substr($decoded, 2);
        
        // 分离nonce和密文
        $nonce = substr($data, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($data, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        
        // 生成密钥
        $key = $this->deriveKey($salt, SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
        
        // 解密
        $decrypted = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
        
        // 清理敏感数据
        sodium_memzero($key);
        
        if ($decrypted === false) {
            throw new \Exception('Sodium解密失败');
        }
        
        return $decrypted;
    }
    
    /**
     * 使用OpenSSL加密
     */
    private function encryptWithOpenSSL(string $data, string $salt): string
    {
        // 生成密钥
        $key = $this->deriveKey($salt, 32);
        
        // 生成随机IV
        $iv = random_bytes(openssl_cipher_iv_length(self::OPENSSL_CIPHER));
        
        // 加密（GCM模式支持认证）
        $tag = '';
        $ciphertext = openssl_encrypt(
            $data,
            self::OPENSSL_CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::OPENSSL_TAG_LENGTH
        );
        
        if ($ciphertext === false) {
            throw new \Exception('OpenSSL加密失败');
        }
        
        // 组合数据：IV + Tag + 密文
        $combined = $iv . $tag . $ciphertext;
        
        // 根据模式编码
        return $this->encodeOutput('o:' . $combined);
    }
    
    /**
     * 使用OpenSSL解密
     */
    private function decryptWithOpenSSL(string $encrypted, string $salt): string
    {
        // 解码数据
        $decoded = $this->decodeInput($encrypted);
        
        // 移除前缀
        if (!str_starts_with($decoded, 'o:')) {
            throw new \Exception('无效的OpenSSL加密格式');
        }
        $data = substr($decoded, 2);
        
        // 分离IV、Tag和密文
        $ivLength = openssl_cipher_iv_length(self::OPENSSL_CIPHER);
        $iv = substr($data, 0, $ivLength);
        $tag = substr($data, $ivLength, self::OPENSSL_TAG_LENGTH);
        $ciphertext = substr($data, $ivLength + self::OPENSSL_TAG_LENGTH);
        
        // 生成密钥
        $key = $this->deriveKey($salt, 32);
        
        // 解密
        $decrypted = openssl_decrypt(
            $ciphertext,
            self::OPENSSL_CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
        
        if ($decrypted === false) {
            throw new \Exception('OpenSSL解密失败');
        }
        
        return $decrypted;
    }
    
    /**
     * 密钥派生
     */
    private function deriveKey(string $salt, int $length): string
    {
        // 使用HKDF进行密钥派生
        return hash_hkdf('sha256', $this->masterKey, $length, 'mofei-crypto', $salt);
    }
    
    /**
     * 根据模式编码输出
     */
    private function encodeOutput(string $data): string
    {
        return match($this->mode) {
            self::MODE_URL_SAFE => rtrim(strtr(base64_encode($data), '+/', '-_'), '='),
            self::MODE_TOKEN => rtrim(strtr(base64_encode($data), '+/', '-_'), '='),
            self::MODE_COMPACT => bin2hex($data),
            default => base64_encode($data)
        };
    }
    
    /**
     * 根据模式解码输入
     */
    private function decodeInput(string $encoded): string
    {
        // return match($this->mode) {
        //     self::MODE_URL_SAFE, self::MODE_TOKEN => {
        //         // 补齐padding
        //         $padded = $encoded . str_repeat('=', (4 - strlen($encoded) % 4) % 4);
        //         $decoded = base64_decode(strtr($padded, '-_', '+/'), true);
        //         if ($decoded === false) {
        //             throw new \Exception('URL安全解码失败');
        //         }
        //         return $decoded;
        //     },
        //     self::MODE_COMPACT => {
        //         $decoded = hex2bin($encoded);
        //         if ($decoded === false) {
        //             throw new \Exception('十六进制解码失败');
        //         }
        //         return $decoded;
        //     },
        //     default => {
        //         $decoded = base64_decode($encoded, true);
        //         if ($decoded === false) {
        //             throw new \Exception('Base64解码失败');
        //         }
        //         return $decoded;
        //     }
        // };
        return match ($this->mode) {
            self::MODE_URL_SAFE, self::MODE_TOKEN => (function () use ($encoded) {
                $padded = $encoded . str_repeat('=', (4 - strlen($encoded) % 4) % 4);
                $decoded = base64_decode(strtr($padded, '-_', '+/'), true);
                if ($decoded === false) {
                    throw new \Exception('URL安全解码失败');
                }
                return $decoded;
            })(),
            self::MODE_COMPACT => (function () use ($encoded) {
                $decoded = hex2bin($encoded);
                if ($decoded === false) {
                    throw new \Exception('十六进制解码失败');
                }
                return $decoded;
            })(),
            default => (function () use ($encoded) {
                $decoded = base64_decode($encoded, true);
                if ($decoded === false) {
                    throw new \Exception('Base64解码失败');
                }
                return $decoded;
            })(),
        };
    }
    
    /**
     * 确定使用的加密引擎
     */
    private function determineEngine(): string
    {
        return match($this->engine) {
            self::ENGINE_AUTO => extension_loaded('sodium') ? self::ENGINE_SODIUM : self::ENGINE_OPENSSL,
            default => $this->engine
        };
    }
    
    /**
     * 检测加密引擎（通过前缀）
     */
    private function detectEngine(string $encrypted): string
    {
        try {
            $decoded = $this->decodeInput($encrypted);
            return match(true) {
                str_starts_with($decoded, 's:') => self::ENGINE_SODIUM,
                str_starts_with($decoded, 'o:') => self::ENGINE_OPENSSL,
                default => throw new \Exception('无法识别加密引擎')
            };
        } catch (\Exception) {
            throw new \Exception('无效的加密数据格式');
        }
    }
    
    /**
     * 验证扩展支持
     */
    private function validateExtensions(): void
    {
        if ($this->engine === self::ENGINE_SODIUM && !extension_loaded('sodium')) {
            throw new \Exception('Sodium扩展未安装');
        }
        
        if ($this->engine === self::ENGINE_OPENSSL && !extension_loaded('openssl')) {
            throw new \Exception('OpenSSL扩展未安装');
        }
    }
    
    /**
     * 生成安全的随机字符串
     */
    public static function generateSecureRandom(int $length = 32, bool $urlSafe = false): string
    {
        $bytes = random_bytes($length);
        
        if ($urlSafe) {
            return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
        }
        
        return bin2hex($bytes);
    }
    
    /**
     * 安全比较字符串（防时序攻击）
     */
    public static function secureCompare(string $known, string $user): bool
    {
        return hash_equals($known, $user);
    }
    
    /**
     * 获取加密信息
     */
    public function getInfo(): array
    {
        return [
            'engine' => $this->determineEngine(),
            'mode' => $this->mode,
            'sodium_available' => extension_loaded('sodium'),
            'openssl_available' => extension_loaded('openssl'),
            'openssl_cipher' => self::OPENSSL_CIPHER,
        ];
    }
}