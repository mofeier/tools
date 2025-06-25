<?php

require_once __DIR__ . '/../vendor/autoload.php';

use mofei\SecureCrypto;
use mofei\Utils;

echo "=== SecureCrypto 使用示例 ===\n\n";

// 1. URL参数加密示例
echo "1. URL参数加密示例\n";
echo "-------------------\n";

$userId = 12345;
$productId = 'PROD-2024-001';
$secretKey = 'my_website_secret_key_2024';

// 加密用户ID和产品ID
$encryptedUserId = Utils::util_encrypt_url((string)$userId, $secretKey);
$encryptedProductId = Utils::util_encrypt_url($productId, $secretKey);

echo "原始用户ID: {$userId}\n";
echo "加密后的用户ID: {$encryptedUserId}\n";
echo "原始产品ID: {$productId}\n";
echo "加密后的产品ID: {$encryptedProductId}\n";

// 构建安全的URL
$secureUrl = "https://example.com/product/details?u={$encryptedUserId}&p={$encryptedProductId}";
echo "安全URL: {$secureUrl}\n";

// 解密验证
$decryptedUserId = Utils::util_decrypt_url($encryptedUserId, $secretKey);
$decryptedProductId = Utils::util_decrypt_url($encryptedProductId, $secretKey);
echo "解密后的用户ID: {$decryptedUserId}\n";
echo "解密后的产品ID: {$decryptedProductId}\n\n";

// 2. Token系统示例
echo "2. Token系统示例\n";
echo "----------------\n";

// 创建用户会话Token
$userSession = [
    'user_id' => 123,
    'username' => 'john_doe',
    'role' => 'admin',
    'permissions' => ['read', 'write', 'delete'],
    'login_time' => date('Y-m-d H:i:s')
];

$sessionData = json_encode($userSession);
$jwtSecret = 'jwt_secret_key_2024';

// 创建30分钟有效期的Token
$token = Utils::util_encrypt_token($sessionData, $jwtSecret, 1800);
echo "生成的Token: {$token}\n";

// 验证和解析Token
try {
    $decryptedSession = Utils::util_decrypt_token($token, $jwtSecret);
    $sessionInfo = json_decode($decryptedSession, true);
    
    echo "Token验证成功!\n";
    echo "用户ID: {$sessionInfo['user_id']}\n";
    echo "用户名: {$sessionInfo['username']}\n";
    echo "角色: {$sessionInfo['role']}\n";
    echo "权限: " . implode(', ', $sessionInfo['permissions']) . "\n";
    echo "登录时间: {$sessionInfo['login_time']}\n\n";
} catch (Exception $e) {
    echo "Token验证失败: " . $e->getMessage() . "\n\n";
}

// 3. API密钥管理示例
echo "3. API密钥管理示例\n";
echo "------------------\n";

class ApiKeyManager {
    private const API_SECRET = 'api_master_secret_2024';
    
    public static function generateApiKey($clientId, $permissions, $expiry = 86400) {
        $keyData = [
            'client_id' => $clientId,
            'permissions' => $permissions,
            'issued_at' => time(),
            'key_id' => Utils::util_secure_random(16, true)
        ];
        
        return Utils::util_encrypt_token(
            json_encode($keyData), 
            self::API_SECRET, 
            $expiry
        );
    }
    
    public static function validateApiKey($apiKey) {
        try {
            $keyData = Utils::util_decrypt_token($apiKey, self::API_SECRET);
            return json_decode($keyData, true);
        } catch (Exception $e) {
            throw new Exception('无效的API密钥: ' . $e->getMessage());
        }
    }
}

// 生成API密钥
$clientId = 'client_12345';
$permissions = ['api:read', 'api:write'];
$apiKey = ApiKeyManager::generateApiKey($clientId, $permissions, 86400); // 24小时有效

echo "客户端ID: {$clientId}\n";
echo "权限: " . implode(', ', $permissions) . "\n";
echo "生成的API密钥: {$apiKey}\n";

// 验证API密钥
try {
    $keyInfo = ApiKeyManager::validateApiKey($apiKey);
    echo "API密钥验证成功!\n";
    echo "客户端ID: {$keyInfo['client_id']}\n";
    echo "密钥ID: {$keyInfo['key_id']}\n";
    echo "签发时间: " . date('Y-m-d H:i:s', $keyInfo['issued_at']) . "\n\n";
} catch (Exception $e) {
    echo "API密钥验证失败: " . $e->getMessage() . "\n\n";
}

// 4. 表单防重复提交示例
echo "4. 表单防重复提交示例\n";
echo "----------------------\n";

class FormTokenManager {
    private const FORM_SECRET = 'form_csrf_secret_2024';
    
    public static function generateFormToken($formId, $userId) {
        $tokenData = [
            'form_id' => $formId,
            'user_id' => $userId,
            'timestamp' => time(),
            'nonce' => Utils::util_secure_random(16, true)
        ];
        
        return Utils::util_encrypt_token(
            json_encode($tokenData), 
            self::FORM_SECRET, 
            1800 // 30分钟有效
        );
    }
    
    public static function validateFormToken($token, $formId, $userId) {
        try {
            $tokenData = json_decode(
                Utils::util_decrypt_token($token, self::FORM_SECRET), 
                true
            );
            
            return $tokenData['form_id'] === $formId && 
                   $tokenData['user_id'] === $userId;
        } catch (Exception $e) {
            return false;
        }
    }
}

// 生成表单Token
$formId = 'user_profile_form';
$currentUserId = 456;
$formToken = FormTokenManager::generateFormToken($formId, $currentUserId);

echo "表单ID: {$formId}\n";
echo "用户ID: {$currentUserId}\n";
echo "表单Token: {$formToken}\n";

// 验证表单Token
$isValidForm = FormTokenManager::validateFormToken($formToken, $formId, $currentUserId);
echo "表单Token验证结果: " . ($isValidForm ? '有效' : '无效') . "\n\n";

// 5. 加密引擎对比示例
echo "5. 加密引擎对比示例\n";
echo "------------------\n";

$testData = 'Performance test data for encryption engines';
$testKey = 'performance_test_key';

// Sodium引擎测试
if (extension_loaded('sodium')) {
    $sodiumCrypto = new SecureCrypto(
        $testKey,
        SecureCrypto::ENGINE_SODIUM,
        SecureCrypto::MODE_URL_SAFE
    );
    
    $start = microtime(true);
    $sodiumEncrypted = $sodiumCrypto->encrypt($testData);
    $sodiumDecrypted = $sodiumCrypto->decrypt($sodiumEncrypted);
    $sodiumTime = microtime(true) - $start;
    
    echo "Sodium引擎:\n";
    echo "  加密结果: {$sodiumEncrypted}\n";
    echo "  解密验证: " . ($sodiumDecrypted === $testData ? '成功' : '失败') . "\n";
    echo "  处理时间: " . number_format($sodiumTime * 1000, 3) . " ms\n";
} else {
    echo "Sodium引擎: 未安装\n";
}

// OpenSSL引擎测试
if (extension_loaded('openssl')) {
    $opensslCrypto = new SecureCrypto(
        $testKey,
        SecureCrypto::ENGINE_OPENSSL,
        SecureCrypto::MODE_URL_SAFE
    );
    
    $start = microtime(true);
    $opensslEncrypted = $opensslCrypto->encrypt($testData);
    $opensslDecrypted = $opensslCrypto->decrypt($opensslEncrypted);
    $opensslTime = microtime(true) - $start;
    
    echo "OpenSSL引擎:\n";
    echo "  加密结果: {$opensslEncrypted}\n";
    echo "  解密验证: " . ($opensslDecrypted === $testData ? '成功' : '失败') . "\n";
    echo "  处理时间: " . number_format($opensslTime * 1000, 3) . " ms\n";
} else {
    echo "OpenSSL引擎: 未安装\n";
}

echo "\n";

// 6. 安全工具示例
echo "6. 安全工具示例\n";
echo "---------------\n";

// 生成安全随机字符串
$randomHex = Utils::util_secure_random(32, false);
$randomUrlSafe = Utils::util_secure_random(32, true);

echo "随机十六进制字符串: {$randomHex}\n";
echo "URL安全随机字符串: {$randomUrlSafe}\n";

// 安全字符串比较
$secret1 = 'my_secret_token_123';
$secret2 = 'my_secret_token_123';
$secret3 = 'my_secret_token_456';

echo "安全比较 (相同): " . (Utils::util_secure_compare($secret1, $secret2) ? '匹配' : '不匹配') . "\n";
echo "安全比较 (不同): " . (Utils::util_secure_compare($secret1, $secret3) ? '匹配' : '不匹配') . "\n";

// 7. 系统信息
echo "\n7. 系统信息\n";
echo "-----------\n";

$crypto = new SecureCrypto('test_key');
$info = $crypto->getInfo();

echo "当前加密引擎: {$info['engine']}\n";
echo "Sodium可用: " . ($info['sodium_available'] ? '是' : '否') . "\n";
echo "OpenSSL可用: " . ($info['openssl_available'] ? '是' : '否') . "\n";
echo "OpenSSL算法: {$info['openssl_cipher']}\n";

echo "\n=== 示例完成 ===\n";