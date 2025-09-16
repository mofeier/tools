<?php

namespace Mofei\Tests;

use PHPUnit\Framework\TestCase;
use Mofei\Security;
use Mofei\Utils;
use Exception;

class SecureCryptoTest extends TestCase
{
    private string $testData = 'Hello, SecureCrypto!';
    private string $testKey = 'test_encryption_key_2024';
    
    /**
     * 测试URL安全加密解密
     */
    public function testUrlSafeEncryption()
    {
        // 测试静态方法
        $encrypted = Security::encryptForUrl($this->testData, $this->testKey);
        $this->assertNotEmpty($encrypted);
        $this->assertNotEquals($this->testData, $encrypted);
        
        // 验证URL安全字符
        $this->assertStringNotContainsString('+', $encrypted);
        $this->assertStringNotContainsString('/', $encrypted);
        $this->assertStringNotContainsString('=', $encrypted);
        
        $decrypted = Security::decryptFromUrl($encrypted, $this->testKey);
        $this->assertEquals($this->testData, $decrypted);
        
        // 测试Utils工具方法
        $encrypted2 = Utils::util_encrypt_url($this->testData, $this->testKey);
        $decrypted2 = Utils::util_decrypt_url($encrypted2, $this->testKey);
        $this->assertEquals($this->testData, $decrypted2);
    }
    
    /**
     * 测试Token加密解密（无过期时间）
     */
    public function testTokenEncryptionWithoutExpiry()
    {
        $encrypted = Security::encryptForToken($this->testData, $this->testKey);
        $this->assertNotEmpty($encrypted);
        
        $decrypted = Security::decryptFromToken($encrypted, $this->testKey);
        $this->assertEquals($this->testData, $decrypted);
        
        // 测试Utils工具方法
        $encrypted2 = Utils::util_encrypt_token($this->testData, $this->testKey);
        $decrypted2 = Utils::util_decrypt_token($encrypted2, $this->testKey);
        $this->assertEquals($this->testData, $decrypted2);
    }
    
    /**
     * 测试Token加密解密（有过期时间）
     */
    public function testTokenEncryptionWithExpiry()
    {
        // 测试有效期内的Token
        $encrypted = Security::encryptForToken($this->testData, $this->testKey, 3600); // 1小时
        $decrypted = Security::decryptFromToken($encrypted, $this->testKey);
        $this->assertEquals($this->testData, $decrypted);
        
        // 测试Utils工具方法
        $encrypted2 = Utils::util_encrypt_token($this->testData, $this->testKey, 3600);
        $decrypted2 = Utils::util_decrypt_token($encrypted2, $this->testKey);
        $this->assertEquals($this->testData, $decrypted2);
    }
    
    /**
     * 测试过期Token
     */
    public function testExpiredToken()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Token已过期');
        
        // 创建已过期的Token（-1秒表示1秒前过期）
        $encrypted = Security::encryptForToken($this->testData, $this->testKey, -1);
        
        // 等待确保过期
        sleep(1);
        
        Security::decryptFromToken($encrypted, $this->testKey);
    }
    
    /**
     * 测试不同加密引擎
     */
    public function testDifferentEngines()
    {
        // 测试Sodium引擎
        if (extension_loaded('sodium')) {
            $sodiumCrypto = new Security(
                $this->testKey,
                Security::ENGINE_SODIUM,
                Security::MODE_STANDARD
            );
            
            $encrypted = $sodiumCrypto->encrypt($this->testData);
            $decrypted = $sodiumCrypto->decrypt($encrypted);
            $this->assertEquals($this->testData, $decrypted);
        }
        
        // 测试OpenSSL引擎
        if (extension_loaded('openssl')) {
            $opensslCrypto = new Security(
                $this->testKey,
                Security::ENGINE_OPENSSL,
                Security::MODE_STANDARD
            );
            
            $encrypted = $opensslCrypto->encrypt($this->testData);
            $decrypted = $opensslCrypto->decrypt($encrypted);
            $this->assertEquals($this->testData, $decrypted);
        }
    }
    
    /**
     * 测试不同加密模式
     */
    public function testDifferentModes()
    {
        $modes = [
            Security::MODE_STANDARD,
            Security::MODE_URL_SAFE,
            Security::MODE_COMPACT
        ];
        
        foreach ($modes as $mode) {
            $crypto = new Security($this->testKey, Security::ENGINE_AUTO, $mode);
            
            $encrypted = $crypto->encrypt($this->testData);
            $this->assertNotEmpty($encrypted);
            
            $decrypted = $crypto->decrypt($encrypted);
            $this->assertEquals($this->testData, $decrypted);
            
            // 验证不同模式的编码格式
            if ($mode === Security::MODE_URL_SAFE) {
                $this->assertStringNotContainsString('+', $encrypted);
                $this->assertStringNotContainsString('/', $encrypted);
            } elseif ($mode === Security::MODE_COMPACT) {
                $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $encrypted);
            }
        }
    }
    
    /**
     * 测试自动引擎选择
     */
    public function testAutoEngine()
    {
        $crypto = new Security($this->testKey, Security::ENGINE_AUTO);
        
        $encrypted = $crypto->encrypt($this->testData);
        $decrypted = $crypto->decrypt($encrypted);
        $this->assertEquals($this->testData, $decrypted);
        
        $info = $crypto->getInfo();
        $this->assertArrayHasKey('engine', $info);
        $this->assertContains($info['engine'], [Security::ENGINE_SODIUM, Security::ENGINE_OPENSSL]);
    }
    
    /**
     * 测试安全随机字符串生成
     */
    public function testSecureRandomGeneration()
    {
        // 测试默认长度
        $random1 = Security::generateSecureRandom();
        $random2 = Security::generateSecureRandom();
        $this->assertNotEquals($random1, $random2);
        $this->assertEquals(64, strlen($random1)); // 32字节 = 64个十六进制字符
        
        // 测试自定义长度
        $random3 = Security::generateSecureRandom(16);
        $this->assertEquals(32, strlen($random3)); // 16字节 = 32个十六进制字符
        
        // 测试URL安全模式
        $random4 = Security::generateSecureRandom(32, true);
        $this->assertStringNotContainsString('+', $random4);
        $this->assertStringNotContainsString('/', $random4);
        $this->assertStringNotContainsString('=', $random4);
        
        // 测试Utils工具方法
        $random5 = Utils::util_secure_random(16, true);
        $this->assertNotEmpty($random5);
    }
    
    /**
     * 测试安全字符串比较
     */
    public function testSecureCompare()
    {
        $string1 = 'test_string_123';
        $string2 = 'test_string_123';
        $string3 = 'test_string_456';
        
        // 相同字符串
        $this->assertTrue(Security::secureCompare($string1, $string2));
        $this->assertTrue(Utils::util_secure_compare($string1, $string2));
        
        // 不同字符串
        $this->assertFalse(Security::secureCompare($string1, $string3));
        $this->assertFalse(Utils::util_secure_compare($string1, $string3));
    }
    
    /**
     * 测试加密信息获取
     */
    public function testGetInfo()
    {
        $crypto = new Security($this->testKey);
        $info = $crypto->getInfo();
        
        $this->assertArrayHasKey('engine', $info);
        $this->assertArrayHasKey('mode', $info);
        $this->assertArrayHasKey('sodium_available', $info);
        $this->assertArrayHasKey('openssl_available', $info);
        $this->assertArrayHasKey('openssl_cipher', $info);
        
        $this->assertIsBool($info['sodium_available']);
        $this->assertIsBool($info['openssl_available']);
    }
    
    /**
     * 测试错误密钥解密
     */
    public function testWrongKeyDecryption()
    {
        $this->expectException(Exception::class);
        
        $encrypted = Security::encryptForUrl($this->testData, $this->testKey);
        Security::decryptFromUrl($encrypted, 'wrong_key');
    }
    
    /**
     * 测试无效数据解密
     */
    public function testInvalidDataDecryption()
    {
        $this->expectException(Exception::class);
        
        $crypto = new Security($this->testKey);
        $crypto->decrypt('invalid_encrypted_data');
    }
    
    /**
     * 测试大数据加密
     */
    public function testLargeDataEncryption()
    {
        $largeData = str_repeat('Large data test. ', 1000); // 约18KB数据
        
        $encrypted = Security::encryptForUrl($largeData, $this->testKey);
        $decrypted = Security::decryptFromUrl($encrypted, $this->testKey);
        
        $this->assertEquals($largeData, $decrypted);
    }
    
    /**
     * 测试特殊字符加密
     */
    public function testSpecialCharactersEncryption()
    {
        $specialData = '特殊字符测试: !@#$%^&*()_+-=[]{}|;:",./<>?`~';
        
        $encrypted = Security::encryptForUrl($specialData, $this->testKey);
        $decrypted = Security::decryptFromUrl($encrypted, $this->testKey);
        
        $this->assertEquals($specialData, $decrypted);
    }
    
    /**
     * 测试JSON数据加密
     */
    public function testJsonDataEncryption()
    {
        $jsonData = json_encode([
            'user_id' => 123,
            'username' => 'testuser',
            'permissions' => ['read', 'write'],
            'metadata' => [
                'last_login' => '2024-01-01 12:00:00',
                'ip_address' => '192.168.1.1'
            ]
        ]);
        
        $encrypted = Utils::util_encrypt_token($jsonData, $this->testKey, 3600);
        $decrypted = Utils::util_decrypt_token($encrypted, $this->testKey);
        
        $this->assertEquals($jsonData, $decrypted);
        
        // 验证JSON格式
        $decodedData = json_decode($decrypted, true);
        $this->assertIsArray($decodedData);
        $this->assertEquals(123, $decodedData['user_id']);
    }
    
    /**
     * 测试并发加密（相同数据应产生不同密文）
     */
    public function testConcurrentEncryption()
    {
        $encrypted1 = Security::encryptForUrl($this->testData, $this->testKey);
        $encrypted2 = Security::encryptForUrl($this->testData, $this->testKey);

        // 相同数据的不同加密结果应该不同（因为使用了随机nonce/IV）
        $this->assertNotEquals($encrypted1, $encrypted2);

        // 但解密结果应该相同
        $decrypted1 = Security::decryptFromUrl($encrypted1, $this->testKey);
        $decrypted2 = Security::decryptFromUrl($encrypted2, $this->testKey);
        
        $this->assertEquals($this->testData, $decrypted1);
        $this->assertEquals($this->testData, $decrypted2);
        $this->assertEquals($decrypted1, $decrypted2);
    }
}
