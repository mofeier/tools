<?php

/**
 * Mofeier工具包使用示例
 * 展示各种功能的使用方法
 */

// 引入Composer自动加载文件
require_once __DIR__ . '/../vendor/autoload.php';

// 引入Facade别名文件（如果需要使用简短别名）
// require_once __DIR__ . '/../src/facade_aliases.php';

use Mofei\Message;
use Mofei\StatusCodes;
use Mofei\StringUtils;
use Mofei\Utils;
use Mofei\Maths;
use Mofei\Security;
use Mofei\Tools;

// 1. 消息体处理示例
echo "=== 消息体处理示例 ===\n";

// 基本使用
$result = Message::result();
print_r($result);

// 设置状态码和消息
$result = Message::code(2002)->result();
print_r($result);

// 设置状态码、消息和数据
$result = Message::code(2003)->msg('cancel')->data(['name' => 'mofeier'])->result();
print_r($result);

// 添加自定义字段
$result = Message::addFiled('times')->result();
print_r($result);

// 使用链式调用设置分页数据
$result = Message::total(100)->page(1)->limit(15)->result();
print_r($result);

// 字段替换
$result = Message::replaceFields(['code' => 'codes', 'msg' => 'message', 'data' => 'datas'])->result();
print_r($result);

echo "\n";

// 2. 状态码管理示例
echo "=== 状态码管理示例 ===\n";

// 获取预定义状态码消息
$message = StatusCodes::getMessage(200);
echo "状态码200的消息: $message\n";

$message = StatusCodes::getMessage(2002);
echo "状态码2002的消息: $message\n";

// 添加自定义状态码
StatusCodes::addCode(3001, '自定义错误');
$message = StatusCodes::getMessage(3001);
echo "自定义状态码3001的消息: $message\n";

echo "\n";

// 3. 字符串转换示例
echo "=== 字符串转换示例 ===\n";

// 基本类型转换
$intValue = StringUtils::to_int('123');
echo "字符串'123'转整数: $intValue\n";

$floatValue = StringUtils::to_float('123.45');
echo "字符串'123.45'转浮点数: $floatValue\n";

$boolValue = StringUtils::to_bool('true');
echo "字符串'true'转布尔值: " . ($boolValue ? 'true' : 'false') . "\n";

$arrayValue = StringUtils::to_array('["a", "b", "c"]');
echo "JSON字符串转数组: ";
print_r($arrayValue);

// 命名转换
$camelCase = StringUtils::to_camel_case('snake_case_string');
echo "下划线命名转驼峰命名: $camelCase\n";

$snakeCase = StringUtils::to_snake_case('camelCaseString');
echo "驼峰命名转下划线命名: $snakeCase\n";

// 数组和树形结构转换
$tree = StringUtils::array_to_tree([
    ['id' => 1, 'parent_id' => 0, 'name' => 'Root'],
    ['id' => 2, 'parent_id' => 1, 'name' => 'Child 1'],
    ['id' => 3, 'parent_id' => 1, 'name' => 'Child 2'],
    ['id' => 4, 'parent_id' => 2, 'name' => 'Grandchild 1']
], 'id', 'parent_id', 'children');
echo "数组转树形结构:\n";
print_r($tree);

$array = StringUtils::tree_to_array($tree, 'children');
echo "树形结构转数组:\n";
print_r($array);

// 字符串处理
$trimmed = StringUtils::trim('  hello world  ');
echo "去除字符串两端空格: '$trimmed'\n";

$lower = StringUtils::to_lower('HELLO WORLD');
echo "字符串转小写: $lower\n";

$upper = StringUtils::to_upper('hello world');
echo "字符串转大写: $upper\n";

$ucfirst = StringUtils::ucfirst('hello world');
echo "首字母大写: $ucfirst\n";

$limited = StringUtils::limit('This is a long string', 10);
echo "限制字符串长度: $limited\n";

$sanitized = StringUtils::sanitize('<script>alert("xss")</script>');
echo "清理HTML标签: $sanitized\n";

$random = StringUtils::random(10);
echo "生成随机字符串: $random\n";

// 字符串检查
$contains = StringUtils::contains('hello world', 'world');
echo "检查字符串是否包含子串: " . ($contains ? '是' : '否') . "\n";

$startsWith = StringUtils::starts_with('hello world', 'hello');
echo "检查字符串是否以指定子串开头: " . ($startsWith ? '是' : '否') . "\n";

$endsWith = StringUtils::ends_with('hello world', 'world');
echo "检查字符串是否以指定子串结尾: " . ($endsWith ? '是' : '否') . "\n";

echo "\n";

// 4. 工具函数示例
echo "=== 工具函数示例 ===\n";

// JSON处理
$json = Utils::json_encode(['name' => 'mofeier']);
echo "数组转JSON: $json\n";

$array = Utils::json_decode('{"name":"mofeier"}');
echo "JSON转数组: ";
print_r($array);

// Base64处理
$base64 = Utils::base64_encode(['name' => 'mofeier']);
echo "数组转Base64: $base64\n";

$array = Utils::base64_decode($base64);
echo "Base64转数组: ";
print_r($array);

// URL处理
$url = Utils::url_encode(['name' => 'mofeier', 'age' => 30]);
echo "数组转URL参数: $url\n";

$array = Utils::url_decode('name=mofeier&age=30');
echo "URL参数转数组: ";
print_r($array);

// 加密解密
$encrypted = Utils::encrypt_url('sensitive data');
echo "URL安全加密: $encrypted\n";

$decrypted = Utils::decrypt_url($encrypted);
echo "URL安全解密: $decrypted\n";

$token = Utils::encrypt_token('user_id:123', null, 3600); // 1小时过期
echo "Token加密: $token\n";

$decrypted = Utils::decrypt_token($token);
echo "Token解密: $decrypted\n";

// 安全功能
$random = Utils::secure_random(32);
echo "生成安全随机字符串: $random\n";

$equals = Utils::secure_compare('known_string', 'known_string');
echo "安全字符串比较(相同): " . ($equals ? '是' : '否') . "\n";

$equals = Utils::secure_compare('known_string', 'different_string');
echo "安全字符串比较(不同): " . ($equals ? '是' : '否') . "\n";

$salt = Utils::generate_salt(32);
echo "生成随机盐值: $salt\n";

// 数组处理
$merged = Utils::array_merge_deep(['a' => ['b' => 1]], ['a' => ['c' => 2]]);
echo "数组深度合并: ";
print_r($merged);

$unique = Utils::array_unique_multi([
    ['id' => 1, 'name' => 'A'],
    ['id' => 2, 'name' => 'B'],
    ['id' => 1, 'name' => 'A']
], 'id');
echo "多维数组去重: ";
print_r($unique);

$sorted = Utils::array_sort_by_key([
    ['name' => 'C', 'age' => 30],
    ['name' => 'A', 'age' => 25],
    ['name' => 'B', 'age' => 35]
], 'age');
echo "数组按指定键排序: ";
print_r($sorted);

$grouped = Utils::array_group_by([
    ['type' => 'A', 'name' => 'Item 1'],
    ['type' => 'B', 'name' => 'Item 2'],
    ['type' => 'A', 'name' => 'Item 3']
], 'type');
echo "数组分组: ";
print_r($grouped);

echo "\n";

// 5. 数学计算示例
echo "=== 数学计算示例 ===\n";

// 基本运算（支持高精度）
$sum = Maths::add('10.5', '20.3');
echo "加法运算 (10.5 + 20.3): $sum\n";

$difference = Maths::sub('50.0', '20.5');
echo "减法运算 (50.0 - 20.5): $difference\n";

$product = Maths::mul('10.5', '2');
echo "乘法运算 (10.5 * 2): $product\n";

$quotient = Maths::div('100', '3');
echo "除法运算 (100 / 3): $quotient\n";

// 比较
$equals = Maths::compare('10.5', '10.50');
echo "比较运算 (10.5 == 10.50): $equals\n";

$greater = Maths::compare('10.6', '10.5');
echo "比较运算 (10.6 > 10.5): $greater\n";

$less = Maths::compare('10.4', '10.5');
echo "比较运算 (10.4 < 10.5): $less\n";

// 其他运算
$power = Maths::pow('2', '3');
echo "幂运算 (2^3): $power\n";

$sqrt = Maths::sqrt('16');
echo "平方根运算 (√16): $sqrt\n";

$mod = Maths::mod('10', '3');
echo "取模运算 (10 % 3): $mod\n";

echo "\n";

// 6. 安全加密示例
echo "=== 安全加密示例 ===\n";

// OpenSSL加密
$encrypted = Security::openssl_encrypt('sensitive data');
echo "OpenSSL加密: $encrypted\n";

$decrypted = Security::openssl_decrypt($encrypted);
echo "OpenSSL解密: $decrypted\n";

// Sodium加密（需要安装Sodium扩展）
if (extension_loaded('sodium')) {
    $encrypted = Security::sodium_encrypt('sensitive data');
    echo "Sodium加密: $encrypted\n";

    $decrypted = Security::sodium_decrypt($encrypted);
    echo "Sodium解密: $decrypted\n";
} else {
    echo "Sodium扩展未安装，跳过Sodium加密示例\n";
}

// 哈希
$hash = Security::hash('password');
echo "哈希: $hash\n";

$verified = Security::verify_hash('password', $hash);
echo "验证哈希(正确): " . ($verified ? '是' : '否') . "\n";

$verified = Security::verify_hash('wrong_password', $hash);
echo "验证哈希(错误): " . ($verified ? '是' : '否') . "\n";

// 密码处理
$hashed = Security::password_hash('password');
echo "密码哈希: $hashed\n";

$verified = Security::password_verify('password', $hashed);
echo "验证密码哈希(正确): " . ($verified ? '是' : '否') . "\n";

$verified = Security::password_verify('wrong_password', $hashed);
echo "验证密码哈希(错误): " . ($verified ? '是' : '否') . "\n";

echo "\n";

// 7. Facade模式使用示例
echo "=== Facade模式使用示例 ===\n";

// 使用Facade模式进行静态调用
// 注意：需要先引入facade_aliases.php文件才能使用简短别名
// require_once __DIR__ . '/../src/facade_aliases.php';

/*
use Message;
use Str;
use Utils;
use Math;
use Security;
use Status;

// 消息处理
$result = Message::code(200)->msg('success')->data(['user' => 'mofeier'])->result();
print_r($result);

// 字符串处理
$camelCase = Str::to_camel_case('snake_case_string');
echo "使用Facade进行字符串转换: $camelCase\n";

// 工具函数
$json = Utils::json_encode(['name' => 'mofeier']);
echo "使用Facade进行JSON编码: $json\n";

// 数学计算
$sum = Math::add('10.5', '20.3');
echo "使用Facade进行数学计算: $sum\n";

// 安全加密
$encrypted = Security::openssl_encrypt('sensitive data');
echo "使用Facade进行加密: $encrypted\n";

// 状态码
$message = Status::getMessage(200);
echo "使用Facade获取状态码消息: $message\n";
*/

echo "\n";

// 8. 主工具类使用示例
echo "=== 主工具类使用示例 ===\n";

// 创建工具实例
$tools = new Tools();

// 链式调用各种方法
$result = $tools->to_camel_case('snake_case_string')
    ->json_encode()
    ->encrypt_url()
    ->result();
echo "链式调用结果: ";
print_r($result);

// 使用lastResult作为参数
$result = $tools->to_array('["a", "b", "c"]')
    ->array_sort_by_key('a')
    ->json_encode()
    ->result();
echo "使用lastResult作为参数的链式调用结果: ";
print_r($result);

echo "\n";

echo "所有示例运行完成！\n";
