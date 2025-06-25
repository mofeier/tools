# 加密工具使用指南

## 概述

本项目提供了两套加密解决方案：

1. **Crypto.php** - 基于OpenSSL的传统加密类
2. **SecureCrypto.php** - 新一代安全加密类，支持OpenSSL和Sodium双引擎

## OpenSSL vs Sodium 对比

### OpenSSL 特点

**优势：**
- 广泛支持，几乎所有PHP环境都有
- 算法选择丰富（AES-256-GCM, AES-256-CBC等）
- 成熟稳定，经过长期验证
- 支持多种加密模式和填充方式
- 适合大文件和流式加密

**劣势：**
- API相对复杂，容易出错
- 需要手动处理IV、Tag等参数
- 性能相对较低
- 安全配置要求较高

**适用场景：**
- 文件加密存储
- 数据库敏感字段加密
- 需要兼容旧系统的场景
- 大数据量加密处理
- 需要特定算法支持的场景

### Sodium 特点

**优势：**
- 现代化密码学库，安全性更高
- API简单易用，不容易出错
- 性能优异，针对现代CPU优化
- 内置防时序攻击保护
- 自动处理nonce和认证
- PHP 7.2+内置支持

**劣势：**
- 相对较新，部分环境可能不支持
- 算法选择有限（主要是ChaCha20-Poly1305和XSalsa20-Poly1305）
- 不适合超大文件加密

**适用场景：**
- URL参数加密
- Token和JWT加密
- API密钥加密
- 实时通信加密
- 现代Web应用
- 移动应用后端

## SecureCrypto 使用指南

### 基本概念

#### 加密引擎
- `ENGINE_OPENSSL` - 使用OpenSSL引擎
- `ENGINE_SODIUM` - 使用Sodium引擎
- `ENGINE_AUTO` - 自动选择（优先Sodium）

#### 加密模式
- `MODE_STANDARD` - 标准模式，Base64编码
- `MODE_URL_SAFE` - URL安全模式，适合URL参数
- `MODE_TOKEN` - Token模式，支持过期时间
- `MODE_COMPACT` - 紧凑模式，十六进制编码

### 快速开始

#### 1. URL参数加密

```php
use mofei\SecureCrypto;
use mofei\Utils;

// 加密用户ID用于URL传递
$userId = 12345;
$encryptedId = SecureCrypto::encryptForUrl((string)$userId, 'my_secret_key');
echo "加密后的URL参数: " . $encryptedId;

// 在另一个页面解密
$decryptedId = SecureCrypto::decryptFromUrl($encryptedId, 'my_secret_key');
echo "解密后的用户ID: " . $decryptedId;

// 使用Utils工具类
$encryptedData = Utils::util_encrypt_url('sensitive_data');
$decryptedData = Utils::util_decrypt_url($encryptedData);
```

#### 2. Token加密（支持过期）

```php
// 创建30分钟后过期的Token
$userData = json_encode(['user_id' => 123, 'role' => 'admin']);
$token = SecureCrypto::encryptForToken($userData, 'jwt_secret', 1800); // 30分钟

// 验证和解密Token
try {
    $decryptedData = SecureCrypto::decryptFromToken($token, 'jwt_secret');
    $user = json_decode($decryptedData, true);
    echo "用户ID: " . $user['user_id'];
} catch (Exception $e) {
    echo "Token无效或已过期: " . $e->getMessage();
}

// 使用Utils工具类
$token = Utils::util_encrypt_token('user_session_data', null, 3600); // 1小时
$sessionData = Utils::util_decrypt_token($token);
```

#### 3. 高级用法

```php
// 创建自定义配置的加密实例
$crypto = new SecureCrypto(
    'my_master_key',
    SecureCrypto::ENGINE_SODIUM,
    SecureCrypto::MODE_URL_SAFE
);

// 加密敏感数据
$encrypted = $crypto->encrypt('sensitive_information');
$decrypted = $crypto->decrypt($encrypted);

// 获取加密信息
$info = $crypto->getInfo();
print_r($info);
```

### 实际应用场景

#### 1. 电商网站用户ID加密

```php
// 商品详情页URL加密用户ID
function generateSecureProductUrl($productId, $userId) {
    $encryptedUserId = Utils::util_encrypt_url((string)$userId, 'product_key');
    return "/product/{$productId}?u={$encryptedUserId}";
}

// 解析用户ID
function parseUserFromUrl($encryptedUserId) {
    try {
        return (int)Utils::util_decrypt_url($encryptedUserId, 'product_key');
    } catch (Exception $e) {
        return null; // 无效的用户ID
    }
}
```

#### 2. API Token系统

```php
class ApiTokenManager {
    private const TOKEN_SECRET = 'api_token_secret_2024';
    
    public static function generateToken($userId, $permissions, $expiry = 3600) {
        $payload = [
            'user_id' => $userId,
            'permissions' => $permissions,
            'issued_at' => time()
        ];
        
        return Utils::util_encrypt_token(
            json_encode($payload), 
            self::TOKEN_SECRET, 
            $expiry
        );
    }
    
    public static function validateToken($token) {
        try {
            $payload = Utils::util_decrypt_token($token, self::TOKEN_SECRET);
            return json_decode($payload, true);
        } catch (Exception $e) {
            throw new Exception('无效的API Token: ' . $e->getMessage());
        }
    }
}

// 使用示例
$token = ApiTokenManager::generateToken(123, ['read', 'write'], 7200);
$userInfo = ApiTokenManager::validateToken($token);
```

#### 3. 表单防重复提交

```php
class FormTokenManager {
    public static function generateFormToken($formId, $userId) {
        $data = [
            'form_id' => $formId,
            'user_id' => $userId,
            'timestamp' => time(),
            'nonce' => Utils::util_secure_random(16, true)
        ];
        
        return Utils::util_encrypt_token(
            json_encode($data), 
            'form_secret', 
            1800 // 30分钟有效
        );
    }
    
    public static function validateFormToken($token, $formId, $userId) {
        try {
            $data = json_decode(
                Utils::util_decrypt_token($token, 'form_secret'), 
                true
            );
            
            return $data['form_id'] === $formId && 
                   $data['user_id'] === $userId;
        } catch (Exception $e) {
            return false;
        }
    }
}
```

### 安全最佳实践

#### 1. 密钥管理

```php
// ❌ 错误：硬编码密钥
$key = 'my_secret_key';

// ✅ 正确：从环境变量读取
$key = $_ENV['ENCRYPTION_KEY'] ?? throw new Exception('未设置加密密钥');

// ✅ 正确：从配置文件读取
$key = config('app.encryption_key');
```

#### 2. 错误处理

```php
// ✅ 正确的错误处理
try {
    $decrypted = Utils::util_decrypt_url($encrypted, $key);
    // 处理解密后的数据
} catch (Exception $e) {
    // 记录错误日志
    error_log('解密失败: ' . $e->getMessage());
    
    // 返回安全的错误信息
    throw new Exception('数据格式错误');
}
```

#### 3. 时序攻击防护

```php
// ✅ 使用安全比较
if (Utils::util_secure_compare($expectedToken, $userToken)) {
    // Token有效
} else {
    // Token无效
}

// ❌ 避免使用普通比较
if ($expectedToken === $userToken) { // 可能存在时序攻击
    // ...
}
```

### 性能考虑

#### 1. 选择合适的引擎

```php
// 高频率、小数据量：优选Sodium
$crypto = new SecureCrypto('key', SecureCrypto::ENGINE_SODIUM);

// 大文件、兼容性要求：选择OpenSSL
$crypto = new SecureCrypto('key', SecureCrypto::ENGINE_OPENSSL);
```

#### 2. 选择合适的模式

```php
// URL参数：使用URL_SAFE模式
$urlParam = Utils::util_encrypt_url($data);

// 存储空间敏感：使用COMPACT模式
$crypto = new SecureCrypto('key', SecureCrypto::ENGINE_AUTO, SecureCrypto::MODE_COMPACT);
```

### 迁移指南

#### 从旧版Crypto迁移

```php
// 旧版本
$encrypted = Crypto::encrypt($data, $salt);
$decrypted = Crypto::decrypt($encrypted, $salt);

// 新版本（兼容）
$encrypted = Utils::util_encrypt($data, $salt);
$decrypted = Utils::util_decrypt($encrypted, $salt);

// 新版本（推荐）
$encrypted = Utils::util_encrypt_url($data, $key);
$decrypted = Utils::util_decrypt_url($encrypted, $key);
```

### 常见问题

#### Q: 如何选择OpenSSL还是Sodium？
A: 
- 新项目推荐Sodium（性能好、安全性高）
- 需要兼容旧系统时选择OpenSSL
- 大文件加密选择OpenSSL
- URL/Token加密选择Sodium

#### Q: 加密后的数据能在不同模式间解密吗？
A: 不能。不同模式的编码格式不同，需要使用相同的模式进行解密。

#### Q: 如何处理密钥轮换？
A: 建议在加密数据中包含版本信息，支持多版本密钥并行使用。

#### Q: 性能如何优化？
A: 
- 使用Sodium引擎
- 选择合适的编码模式
- 避免频繁创建实例
- 考虑使用缓存

### 扩展阅读

- [PHP Sodium文档](https://www.php.net/manual/en/book.sodium.php)
- [OpenSSL最佳实践](https://www.openssl.org/docs/)
- [现代密码学原理](https://crypto.stanford.edu/~dabo/cryptobook/)