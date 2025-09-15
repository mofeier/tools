<?php

/**
 * Mofeier Tools 完整使用示例
 * 展示所有功能和使用方法
 */

// 引入自动加载文件
require_once __DIR__ . '/../vendor/autoload.php';

// 加载别名（可选）
require_once __DIR__ . '/../src/facade_aliases.php';

use mofei\Message;
use mofei\StringUtils;
use mofei\Utils;
use mofei\Maths;
use mofei\Security;
use mofei\StatusCodes;
use mofei\Facade;

echo "=== Mofeier Tools 完整使用示例 ===\n\n";

// 1. Message类使用示例
echo "1. Message类使用示例\n";
echo str_repeat("-", 50) . "\n";

// 基本使用
$response = Message::success(['user' => 'mofei']);
echo "成功响应: " . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n";

$error = Message::error('参数错误', 2001);
echo "错误响应: " . json_encode($error, JSON_UNESCAPED_UNICODE) . "\n";

// 链式调用
$custom = Message::create()
    ->code(200)
    ->msg('操作成功')
    ->data(['id' => 123])
    ->add('timestamp', time())
    ->result();
echo "自定义响应: " . json_encode($custom, JSON_UNESCAPED_UNICODE) . "\n";

// JSON输出
$jsonOutput = Message::create()->code(200)->msg('JSON输出')->json();
echo "JSON输出: $jsonOutput\n";

// 字段映射
$mapped = Message::create()
    ->code(200)
    ->msg('字段映射')
    ->data(['test' => 'data'])
    ->map(['code' => 'status', 'msg' => 'message'])
    ->result();
echo "字段映射结果: " . json_encode($mapped, JSON_UNESCAPED_UNICODE) . "\n\n";

// 2. StringUtils类使用示例
echo "2. StringUtils类使用示例\n";
echo str_repeat("-", 50) . "\n";

// 类型转换
$int = StringUtils::to_int('123');
$float = StringUtils::to_float('123.45');
$bool = StringUtils::to_bool('true');
echo "类型转换: int=$int, float=$float, bool=" . ($bool ? 'true' : 'false') . "\n";

// 命名格式转换
$camel = StringUtils::to_camel_case('user_name');
$snake = StringUtils::to_snake_case('userName');
$pascal = StringUtils::to_camel_case('user_name', true);
echo "命名转换: camel=$camel, snake=$snake, pascal=$pascal\n";

// 数组结构转换
$items = [
    ['id' => 1, 'name' => '根节点', 'parent_id' => 0],
    ['id' => 2, 'name' => '子节点1', 'parent_id' => 1],
    ['id' => 3, 'name' => '子节点2', 'parent_id' => 1],
    ['id' => 4, 'name' => '孙节点1', 'parent_id' => 2]
];
$tree = StringUtils::array_to_tree($items);
echo "树形结构: " . json_encode($tree, JSON_UNESCAPED_UNICODE) . "\n";

$flatArray = StringUtils::tree_to_array($tree);
echo "扁平化数组: " . json_encode($flatArray, JSON_UNESCAPED_UNICODE) . "\n\n";

// 3. Utils类使用示例
echo "3. Utils类使用示例\n";
echo str_repeat("-", 50) . "\n";

// JSON处理
$data = ['key' => 'value', '中文' => '测试'];
$json = Utils::util_json_encode($data);
$array = Utils::util_json_decode($json);
echo "JSON处理: json=$json\n";
echo "JSON解码: " . json_encode($array, JSON_UNESCAPED_UNICODE) . "\n";

// Base64处理
$base64 = Utils::util_base64_encode($data);
$decoded = Utils::util_base64_decode($base64);
echo "Base64编码: $base64\n";
echo "Base64解码: " . json_encode($decoded, JSON_UNESCAPED_UNICODE) . "\n";

// URL处理
$urlData = ['name' => 'mofei', 'age' => 25, 'city' => '北京'];
$urlEncoded = Utils::util_url_encode($urlData);
$urlDecoded = Utils::util_url_decode($urlEncoded);
echo "URL编码: $urlEncoded\n";
echo "URL解码: " . json_encode($urlDecoded, JSON_UNESCAPED_UNICODE) . "\n";

// URL安全加密
$secretData = 'This is a secret message';
$encrypted = Utils::util_encrypt_url($secretData);
$decrypted = Utils::util_decrypt_url($encrypted);
echo "URL安全加密: encrypted=$encrypted, decrypted=$decrypted\n";

// 安全随机字符串
$random = Utils::util_secure_random(16);
$urlSafeRandom = Utils::util_secure_random(16, true);
echo "安全随机字符串: $random\n";
echo "URL安全随机字符串: $urlSafeRandom\n\n";

// 4. Maths类使用示例
echo "4. Maths类使用示例\n";
echo str_repeat("-", 50) . "\n";

// 基本运算
$sum = Maths::add('0.1', '0.2', 2);
$diff = Maths::sub('10', '3.333', 2);
$product = Maths::mul('2.5', '4', 2);
$quotient = Maths::div('10', '3', 4);
echo "基本运算: sum=$sum, diff=$diff, product=$product, quotient=$quotient\n";

// 比较操作
$comparison = Maths::compare('10.5', '10.50', 2);
echo "比较操作: 10.5 vs 10.50 = $comparison (0=相等, 1=大于, -1=小于)\n";

// 数组运算
$numbers = ['1.1', '2.2', '3.3', '4.4'];
$sumArray = Maths::sum($numbers, 2);
$avg = Maths::average($numbers, 2);
echo "数组运算: sum=$sumArray, average=$avg\n\n";

// 5. Security类使用示例
echo "5. Security类使用示例\n";
echo str_repeat("-", 50) . "\n";

// 加密解密
$key = 'my-secret-key';
$plainText = 'This is a secret message';
$encrypted = Security::encrypt($plainText, $key);
$decrypted = Security::decrypt($encrypted, $key);
echo "加密解密: original='$plainText', encrypted=$encrypted, decrypted=$decrypted\n";

// 密码哈希
$password = 'MySecurePassword123';
$hash = Security::passwordHash($password);
$verified = Security::passwordVerify($password, $hash);
echo "密码哈希: hash=$hash, verified=" . ($verified ? 'true' : 'false') . "\n\n";

// 6. StatusCodes类使用示例
echo "6. StatusCodes类使用示例\n";
echo str_repeat("-", 50) . "\n";

// 获取状态码对应的消息
$msg = StatusCodes::getMessage(2001);
echo "状态码2001: $msg\n";

// 设置自定义状态码
StatusCodes::setCustomCodes([9000 => '自定义成功', 9001 => '自定义失败']);
$customMsg = StatusCodes::getMessage(9000);
echo "自定义状态码9000: $customMsg\n\n";

// 7. Facade门面模式使用示例
echo "7. Facade门面模式使用示例\n";
echo str_repeat("-", 50) . "\n";

// 主Facade类
$response = Facade::message()->code(200)->msg('操作成功')->data(['user' => 'mofeier'])->result();
echo "主Facade: " . json_encode($response, JSON_UNESCAPED_UNICODE) . "\n";

// 链式调用示例
$original = 'user_profile_setting';
$camelCase = Facade::string()->str_to_camel_case($original, true);  // UserProfileSetting
$lowerCamel = Facade::string()->str_to_camel_case($original);  // userProfileSetting
$snakeCase = Facade::string()->str_to_snake_case($camelCase); // userProfile_setting
echo "命名转换: original=$original, PascalCase=$camelCase, camelCase=$lowerCamel, snake_case=$snakeCase\n";

// 组合操作示例
$jsonData = Facade::utils()->util_json_encode(['user' => ['id' => 1, 'name' => 'mofei']]);
$base64Data = Facade::utils()->util_base64_encode($jsonData);
$response = Facade::message()
    ->code(200)
    ->msg('操作成功')
    ->data(['encoded' => $base64Data])
    ->json();
echo "组合操作结果: $response\n";

// 单独的Facade类
$message = \Message::success(['data' => 'value']);
$snakeCase = \Str::str_to_snake_case('UserName');
$utilResult = \Util::util_json_encode(['key' => 'value']);
$mathResult = \Math::add('1', '2', 2);
echo "Facade别名: message=" . json_encode($message, JSON_UNESCAPED_UNICODE) . "\n";
echo "Facade别名: snake_case=$snakeCase\n";
echo "Facade别名: util_result=$utilResult\n";
echo "Facade别名: math_result=$mathResult\n\n";

echo "=== 所有示例运行完成 ===\n";
