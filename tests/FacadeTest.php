<?php

/**
 * Facade门面系统测试
 * 验证Facade模式的各种调用方式是否正常工作
 */

// 引入自动加载文件
require_once __DIR__ . '/../vendor/autoload.php';

// 引入Facade别名
require_once __DIR__ . '/../src/facade_aliases.php';

// 使用命名空间
use Mofei\Facade;
use Mofei\Message;
use Mofei\Utils;
use Mofei\Maths;

// 测试函数
function test($name, callable $testCase) {
    echo "\n===== 测试: {$name} =====\n";
    try {
        $testCase();
        echo "✅ 测试通过\n";
    } catch (Exception $e) {
        echo "❌ 测试失败: " . $e->getMessage() . "\n";
        echo "错误位置: " . $e->getFile() . " 第" . $e->getLine() . "行\n";
    }
}

// 1. 测试主Facade类的基本功能
test('主Facade类的基本功能', function() {
    // 测试消息体功能
    $response = Facade::message()->code(2000)->msg('成功')->data(['test' => 1])->result();
    assert(is_array($response) && $response['code'] === 2000 && $response['msg'] === '成功', '消息体创建失败');
    echo "消息体结果: " . json_encode($response) . "\n";

    // 测试字符串转换功能
    $camelCase = Facade::string()->to_camel_case('user_name');
    assert($camelCase === 'userName', '字符串转换失败');
    echo "字符串转换结果: {$camelCase}\n";

    // 测试工具函数功能
    $json = Facade::utils()->util_json_encode(['key' => 'value']);
    assert($json === '{"key":"value"}', 'JSON编码失败');
    echo "JSON编码结果: {$json}\n";

    // 测试数学计算功能
    $sum = Facade::math()->add('0.1', '0.2', 2);
    assert($sum === '0.30', '数学计算失败');
    echo "数学计算结果: {$sum}\n";
});

// 2. 测试单独类的功能
test('单独类的功能', function() {
    // 测试Message
    $message = Message::code(2000)->msg('成功')->data(['id' => 123])->result();
    assert(is_array($message) && $message['code'] === 2000, 'Message测试失败');
    echo "Message结果: " . json_encode($message) . "\n";

    // 测试Utils类
    $array = Utils::util_json_decode('{"key":"value"}');
    assert(is_array($array) && $array['key'] === 'value', 'Utils类测试失败');
    echo "Utils类结果: " . json_encode($array) . "\n";

    // 测试Maths类
    $difference = Maths::sub('10', '3.333', 2);
    assert($difference === '6.66', 'Maths测试失败');
    echo "Maths结果: {$difference}\n";
});

// 3. 测试静态+链式调用
test('静态+链式调用功能', function() {
    // 消息体的静态链式调用
    $jsonResponse = Message::code(2000)->msg('链式调用成功')->data(['chain' => true])->json();
    $arrayResponse = json_decode($jsonResponse, true);
    assert(is_array($arrayResponse) && $arrayResponse['code'] === 2000, '消息体静态链式调用失败');
    echo "消息体静态链式调用结果: {$jsonResponse}\n";

    // 复杂链式调用示例 - 使用useLastResult方法
    $original = 'user_profile_setting';
    $pascalCase = Facade::string()->to_camel_case($original, true);  // UserProfileSetting
    $camelCase = Facade::string()->useLastResult()->to_camel_case();  // userProfileSetting
    $result = Facade::string()->useLastResult()->to_snake_case();  // user_profile_setting
    
    assert($result === $original, '复杂链式调用失败');
    echo "PascalCase结果: {$pascalCase}\n";
    echo "CamelCase结果: {$camelCase}\n";
    echo "复杂链式调用结果: {$result}\n";
});

// 4. 测试Facade别名功能
test('Facade别名功能', function() {
    // 测试各种别名
    $message = Message::code(2000)->data(['data' => 'value']);
    $utilResult = Utils::util_json_encode(['key' => 'value']);
    $mathResult = Maths::add('1', '2', 2);
    
    assert(is_array($message->result()) && $message->result()['code'] === 2000, 'Message别名测试失败');
    assert($utilResult === '{"key":"value"}', 'Utils别名测试失败');
    assert($mathResult === '3.00', 'Maths别名测试失败');
    
    echo "Message别名结果: " . json_encode($message->result()) . "\n";
    echo "Utils别名结果: {$utilResult}\n";
    echo "Maths别名结果: {$mathResult}\n";
});

// 5. 测试参数自动传递功能
test('参数自动传递功能', function() {
    // 设置初始值
    $jsonStr = Facade::utils()->util_json_encode(['test' => 'data']);
    
    // 使用上一次的结果作为参数
    $decoded = Facade::utils()->useLastResult()->util_json_decode();
    assert(is_array($decoded) && $decoded['test'] === 'data', '参数自动传递失败');
    echo "参数自动传递结果: " . json_encode($decoded) . "\n";
    echo "JSON字符串: {$jsonStr}\n";
});

// 6. 测试组合操作功能
test('组合操作功能', function() {
    // 创建一个复杂的操作链
    $arrayData = ['user' => ['id' => 1, 'name' => 'mofei']];
    
    // 1. 将数组进行Base64编码
    $base64Str = Facade::utils()->util_base64_encode($arrayData);
    
    // 3. 放入消息体返回
    $result = Facade::message(200)
        ->msg('组合操作成功')
        ->data(['encoded' => $base64Str])
        ->json();
    
    assert(is_string($result) && strpos($result, '"code":200') !== false, '组合操作失败');
    echo "Base64字符串: {$base64Str}\n";
    echo "组合操作结果: {$result}\n";
    
    // 4. 反向操作示例 - 验证数据完整性
    $message = json_decode($result, true);
    $decodedArray = Facade::utils()->util_base64_decode($message['data']['encoded']);
    assert($decodedArray == $arrayData, '数据完整性验证失败');
    echo "数据完整性验证成功!\n";
});

// 运行所有测试
echo "\n===== 所有测试运行完毕 =====\n";
echo "请检查测试结果，确保Facade系统正常工作！\n";
