<?php

/**
 * Facade类测试文件
 * 用于验证Facade模式的所有功能是否正常工作
 */

// 引入自动加载文件
require __DIR__ . '/../vendor/autoload.php';

use Mofei\Facade;
use Mofei\Security;

// 创建测试结果数组
$results = [];

// 测试1: 基本消息体功能测试
$results['message_test'] = Facade::message()->code(200)->msg('Success')->data(['name' => 'mofei'])->result();

// 测试2: 工具函数测试
$results['utils_test'] = Facade::utils()->array_to_tree([
    ['id' => 1, 'parent_id' => 0, 'name' => 'Root'],
    ['id' => 2, 'parent_id' => 1, 'name' => 'Child 1'],
    ['id' => 3, 'parent_id' => 2, 'name' => 'Grandchild 1']
]);

// 测试3: 数学计算测试
$results['math_test'] = Facade::math()->add('10.5', '5.25', 2);

// 测试4: 加密解密测试 - 这是我们修复的重点
$test_data = 'Hello, this is a test message for encryption!';
// 使用静态方法调用方式
$encrypted = Security::encryptForUrl($test_data);
$decrypted = Security::decryptFromUrl($encrypted);
$results['crypto_test'] = [
    'original' => $test_data,
    'encrypted' => $encrypted,
    'decrypted' => $decrypted,
    'success' => $test_data === $decrypted
];

// 测试5: 链式调用测试
$chain_result = Facade::message()
    ->code(200)
    ->msg('Chain test successful')
    ->add('timestamp', time())
    ->json();
$results['chain_test'] = $chain_result;

// 测试6: 状态码管理测试
$results['status_test'] = Facade::status()->getMessage(200);

// 测试7: 重构后的Facade加密解密测试 - 使用链式调用方式
$test_string = 'Testing refactored Facade encryption!';
$facade_crypto = Facade::crypto();
$encrypted_result = $facade_crypto->encrypt($test_string); // encrypt返回Facade对象
$encrypted_value = $facade_crypto->getResult(); // 使用getResult获取加密后的字符串
$decrypted_value = $facade_crypto->decrypt($encrypted_value)->getResult(); // 解密并获取结果
$results['facade_crypto_test'] = [
    'original' => $test_string,
    'encrypted' => $encrypted_value,
    'decrypted' => $decrypted_value,
    'success' => $test_string === $decrypted_value
];

// 输出测试结果
echo "<pre>";
print_r($results);
echo "</pre>";

// 判断加密解密测试是否成功
if ($results['crypto_test']['success']) {
    echo "\n\n加密解密测试成功！";
    echo "\n\n所有测试完成。";
} else {
    echo "\n\n加密解密测试失败！";
}