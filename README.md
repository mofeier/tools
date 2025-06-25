# mofei Tools

这是一个现代化的PHP工具包，提供了常用的工具函数、消息体处理和加密功能。使用PHP 8.1+特性优化，提供更好的性能和开发体验。

## 特性

- 🚀 **现代化设计** - 使用PHP 8.1+特性，支持联合类型、数组解包等
- 📦 **消息体构建** - 灵活的消息体构建和处理
- 🔐 **加密功能** - 字符串加解密，支持自定义盐值
- 🛡️ **安全加密** - 现代化加密解决方案，支持URL参数和Token加密
- 🔄 **双引擎支持** - 同时支持OpenSSL和Sodium加密引擎
- 📊 **状态码管理** - 完善的HTTP状态码管理
- 🔧 **字符串工具** - 驼峰、下划线等命名转换
- 🧮 **高精度计算** - 基于BCMath的高精度数学运算
- ⚡ **链式调用** - 支持优雅的链式调用语法
- 🎯 **静态/实例** - 同时支持静态和实例调用方式

## 环境要求

- PHP >= 8.1
- ext-json
- ext-mbstring
- ext-bcmath
- ext-openssl

## 安装

```bash
composer require mofeier/tools
```

### 手动安装

1. 下载源码
2. 将`src`目录复制到你的项目中
3. 引入自动加载文件

```php
require_once 'vendor/autoload.php';
```

## 快速开始

```php
<?php

use mofei\Message;
use mofei\Tools;
use mofei\Utils;
use mofei\StringConverter;
use mofei\MathCalculator;

// 创建消息体 - 支持多种方式
$message = Message::success(['user_id' => 123], '操作成功')
    ->add('timestamp', time());

echo $message->json(); // 输出JSON格式

// 使用工具类
$result = Tools::success(['data' => 'value']);
$error = Tools::error('参数错误', 400);

// 字符串转换
$camelCase = StringConverter::toCamelCase('user_name'); // userName
$snakeCase = StringConverter::toSnakeCase('userName'); // user_name
$pascalCase = StringConverter::toPascalCase('user_name'); // UserName

// 高精度计算
$sum = MathCalculator::add('0.1', '0.2', 2); // "0.30"
$result = MathCalculator::div('10', '3', 4); // "3.3333"
$isEqual = MathCalculator::equals('0.1', '0.10'); // true


// 工具函数
$json = Utils::util_json_encode(['key' => 'value']);
$encrypted = Utils::util_encrypt('hello world');
$hash = Utils::util_hash('password', 'my_salt');
```

## 详细使用说明

### Message 消息体类

#### 基本用法

```php
use mofei\Message;

// 创建基本消息 - 支持多种参数形式
$msg = Message::create(200, 'success', ['id' => 1]);
// 或者
$msg = Message::create(['code' => 200, 'msg' => 'success', 'data' => ['id' => 1]]);

// 快速创建成功消息
$success = Message::success(['user' => 'john'], '登录成功');

// 快速创建错误消息
$error = Message::error('用户不存在', 404);

// 链式调用
$message = Message::create()
    ->setCode(200)
    ->setMsg('操作成功')
    ->setData(['result' => true])
    ->add('timestamp', time());
```

#### 批量设置字段

```php
// 构造时设置字段
$message = Message::create([
    'total' => 100,
    'page' => 1,
    'limit' => 10
])->code(200);

// 运行时批量设置
$result = (new Message())
    ->setFields(['total' => 200, 'page' => 2, 'limit' => 10])
    ->code(200)
    ->result();
```

#### 字段操作

```php
// 添加字段（简化方法名）
$result = (new Message())
    ->add('time')  // 添加时间字段
    ->add('total', 100)  // 添加自定义字段
    ->code(200)
    ->result();

// 独立方法调用
$msg = new Message();
$msg->set('custom', 'value')  // 设置字段
    ->add('time');               // 添加时间字段

echo $msg->get('code');      // 获取字段值: 2000
echo $msg->has('custom');    // 检查字段存在: true
$msg->remove('custom');      // 移除字段
```

#### 字段映射和替换

```php
// 字段映射（简化方法名）
$result = (new Message())
    ->map([
        'code' => 'status',
        'msg' => 'message',
        'data' => 'result'
    ])
    ->code(200)
    ->result();

// 字段替换
$result = (new Message())
    ->code(200)
    ->replace(['code' => 'status', 'msg' => 'message'])
    ->result();
// 输出: ['status' => 200, 'message' => 'Success', 'data' => []]
```

#### 输出格式

```php
// JSON格式
$json = (new Message())->code(200)->json();

// XML格式
$xml = (new Message())->code(200)->xml();

// 通过Tools类直接输出
$json = Tools::json(['code' => 201, 'msg' => 'Created']);
$xml = Tools::xml(['code' => 200, 'data' => ['test' => 'value']]);
```

### StatusCodes 状态码类

```php
use mofei\StatusCodes;

// 获取状态码消息
$message = StatusCodes::getMessage(404); // "Not Found"

// 检查状态码是否存在
$exists = StatusCodes::exists(200); // true

// 设置自定义状态码
StatusCodes::setCustomCodes([
    9001 => '自定义错误',
    9002 => '业务异常'
]);

// 获取所有状态码
$allCodes = StatusCodes::getAllCodes();
```

### Tools 主工具类

```php
use mofei\Tools;

// 快速创建消息
$success = Tools::success(['data' => 'value'], '操作成功');
$error = Tools::error('操作失败', 500);
$custom = Tools::message(201, '创建成功', ['id' => 123]);
```

### StringConverter 字符串转换类

```php
use mofei\StringConverter;

// 驼峰命名转换
$camelCase = StringConverter::toCamelCase('user_name'); // "userName"
$camelCase = StringConverter::toCamelCase('user-name', '-'); // "userName"

// 下划线命名转换
$snakeCase = StringConverter::toSnakeCase('userName'); // "user_name"

// 短横线命名转换
$kebabCase = StringConverter::toKebabCase('userName'); // "user-name"

// 帕斯卡命名转换
$pascalCase = StringConverter::toPascalCase('user_name'); // "UserName"

// 字符串截取（支持中文）
$substr = StringConverter::substr('你好世界', 0, 2); // "你好"
```

#### 数组树形转换

```php
// 一维数组转树形（非递归）
$flatArray = [
    ['id' => 1, 'parent_id' => 0, 'name' => '根节点'],
    ['id' => 2, 'parent_id' => 1, 'name' => '子节点1'],
    ['id' => 3, 'parent_id' => 1, 'name' => '子节点2']
];
$tree = Tools::array_to_tree($flatArray);

// 树形转一维数组（非递归）
$flatAgain = Tools::tree_to_array($tree);
```

#### 编码转换

```php
// 十六进制转换
$hex = Tools::str_to_hex('hello');     // 68656c6c6f
$str = Tools::str_from_hex('68656c6c6f'); // hello

// 二进制转换
$binary = Tools::str_to_binary('A');   // 01000001
$str = Tools::str_from_binary('01000001'); // A
```

### Utils 工具函数类

```php
use mofei\Utils;

// JSON 操作
$json = Utils::util_json_encode(['key' => 'value']);
$array = Utils::util_json_decode($json);

// Base64 操作
$encoded = Utils::util_base64_encode(['data' => 'hello']);
$decoded = Utils::util_base64_decode($encoded);

// 哈希操作
$hash = Utils::util_hash('password', 'salt', 'sha256');
$isValid = Utils::util_verify_hash('password', $hash, 'salt');

// 密码哈希
$passwordHash = Utils::util_password_hash('mypassword');
$isCorrect = Utils::util_password_verify('mypassword', $passwordHash);

// URL 操作
$query = Utils::util_url_encode(['name' => 'john', 'age' => 25]);
$array = Utils::util_url_decode($query);

// 数组操作
$filtered = Utils::util_array_filter_empty(['a' => 1, 'b' => '', 'c' => null]);
$flattened = Utils::util_array_flatten(['a' => [1, 2], 'b' => [3, 4]]);

// 字符串操作
$isJson = Utils::util_is_json('{"key":"value"}'); // true
$random = Utils::util_random_string(10);
$uuid = Utils::util_generate_uuid();

// 时间操作
$formatted = Utils::util_format_time(time(), 'Y-m-d H:i:s');
$timestamp = Utils::util_parse_time('2023-01-01 12:00:00');
```

#### 数组操作

```php
// 深度合并
$merged = Tools::util_array_merge_deep($array1, $array2);

// 多维数组去重
$unique = Tools::util_array_unique_multi($array, 'id');

// 按键排序
$sorted = Tools::util_array_sort_by_key($array, 'created_at', SORT_DESC);

// 分组
$grouped = Tools::util_array_group_by($array, 'category');
```

#### 生成器

```php
// 生成UUID
$uuid = Tools::util_generate_uuid();

// 生成随机字符串，不要求唯一性的
$random = Tools::util_generate_random_string(10);

// 生成随机码，例如验证码，不要求唯一性的
$randomCode = Tools::util_generate_random_code(6， true, true); // 例如: 123456

// 安全随机数
$secureInt = Tools::util_secure_random_int(1, 100);
$secureBytes = Tools::util_secure_random_bytes(16);

// 生成唯一订单号
$orderNo = Tools::build_order_sn();  // 20250617150617123456
$orderNo = Tools::build_order_sn('ORD', '-', 24);  // ORD-20250617150622-123456

// 生成推荐码，唯一性高
$reCode = Tools::build_redcode(6); // 例如: 默认6位， K7N4P8

// 高随机性数字ID生成器（支持动态位数增长）
$number = Tools::build_number();          // 默认8-16位（动态增长）
$number = Tools::build_number(10, 20);    // 自定义范围10-20位
$number = Tools::build_number(6, 6, false);   // 固定6位（不增长）

// 生成许可证序列号license
$license = Tools::build_license();

```

#### 验证器

```php
// 邮箱验证
$isValid = Tools::util_validate_email('test@example.com');

// 手机号验证（中国）
$isValid = Tools::util_validate_mobile('13800138000');

// 身份证验证（中国）
$isValid = Tools::util_validate_id_card('110101199001011234');

// 国内车牌号验证（中国，含特殊车牌）
$isValid = Tools::validate_car_no('京A12345');
```

#### 格式化

```php
// 文件大小格式化
$size = Tools::util_format_file_size(1024 * 1024); // 1.00 MB

// 时间格式化
$timeAgo = Tools::util_format_time_ago(time() - 3600); // 1小时前
```

#### 密码处理

```php
// 密码加密
$hash = Tools::util_password_hash('password123');

// 密码验证
$isValid = Tools::util_password_verify('password123', $hash);
```

### MathCalculator 高精度数学计算类

```php
use mofei\MathCalculator;

// 基本运算 - 支持多种数据类型
$sum = MathCalculator::add('0.1', 0.2, 2); // "0.30"
$diff = MathCalculator::sub(1.0, '0.3', 2); // "0.70"
$product = MathCalculator::mul(0.1, 3, 2); // "0.30"
$quotient = MathCalculator::div('1', '3', 4); // "0.3333"

// 高级运算
$power = MathCalculator::pow('2', '3', 0); // "8"
$sqrt = MathCalculator::sqrt('9', 2); // "3.00"
$mod = MathCalculator::mod('10', '3'); // "1"

// 比较运算
$compare = MathCalculator::compare('0.1', '0.2'); // -1
$equals = MathCalculator::equals('0.1', '0.10'); // true
$greater = MathCalculator::greaterThan('0.2', '0.1'); // true
$less = MathCalculator::lessThan('0.1', '0.2'); // true

// 格式化
$formatted = MathCalculator::format('1.2000'); // "1.2"
```

#### 基本数学运算

```php
// 基本运算
$sum = Tools::math_add(10, 20);        // 30
$power = Tools::math_pow(2, 8);        // 256
$sqrt = Tools::math_sqrt(16);          // 4
$abs = Tools::math_abs(-10);           // 10

// 取整
$ceil = Tools::math_ceil(4.3);         // 5
$floor = Tools::math_floor(4.7);       // 4
$round = Tools::math_round(4.567, 2);  // 4.57
```

#### 统计函数

```php
$numbers = [1, 2, 3, 4, 5];

// 基本统计
$avg = Tools::math_average($numbers);           // 3
$median = Tools::math_median($numbers);         // 3
$variance = Tools::math_variance($numbers);     // 2
$stdDev = Tools::math_standard_deviation($numbers); // 1.41...

// 百分比计算
$percentage = Tools::math_percentage(25, 100);  // 25
```

#### 高级数学

```php
// 阶乘
$factorial = Tools::math_factorial(5);  // 120

// 最大公约数和最小公倍数
$gcd = Tools::math_gcd(12, 18);        // 6
$lcm = Tools::math_lcm(12, 18);        // 36

// 三角函数
$sin = Tools::math_sin(M_PI / 2);      // 1
$cos = Tools::math_cos(0);             // 1

// 对数和指数
$log = Tools::math_log(10, 10);        // 1
$exp = Tools::math_exp(1);             // 2.718...
```

## 帮助信息

```php
// 获取帮助信息
$help = Tools::help();
print_r($help);

// 获取所有可用方法
$methods = Tools::getMethods();
print_r($methods);

// 获取版本信息
$version = Tools::version();
echo $version; // 1.0.0
```

## 运行示例

```bash
php examples/usage.php
```

## 系统要求

- PHP 8.0 或更高版本
- BCMath 扩展（用于高精度计算）
- MBString 扩展（用于多字节字符串处理）

## 许可证

MulanPSL-2.0

## 贡献

欢迎提交Issue和Pull Request来改进这个工具包。

## 作者

- 莫斐 (zyk96321@163.com)


### SecureCrypto 安全加密工具（推荐）
- 具体可查看docs文档中的用法

```php
use mofei\SecureCrypto;
use mofei\Utils;

// URL安全加密 - 适合URL参数传递
$userId = 12345;
$encryptedId = SecureCrypto::encryptForUrl((string)$userId, 'my_secret_key');
echo "URL参数: ?user=" . $encryptedId;

// URL安全解密
$decryptedId = SecureCrypto::decryptFromUrl($encryptedId, 'my_secret_key');
echo "用户ID: " . $decryptedId; // 输出: 12345

// Token加密 - 支持过期时间
$userData = json_encode(['user_id' => 123, 'role' => 'admin']);
$token = SecureCrypto::encryptForToken($userData, 'jwt_secret', 3600); // 1小时后过期
echo "Token: " . $token;

// Token解密和验证
try {
    $decryptedData = SecureCrypto::decryptFromToken($token, 'jwt_secret');
    $user = json_decode($decryptedData, true);
    echo "用户角色: " . $user['role'];
} catch (Exception $e) {
    echo "Token无效或已过期: " . $e->getMessage();
}

// 使用Utils工具类的便捷方法
$encryptedUrl = Utils::util_encrypt_url('sensitive_data');
$decryptedUrl = Utils::util_decrypt_url($encryptedUrl);

$tokenWithExpiry = Utils::util_encrypt_token('session_data', null, 1800); // 30分钟
$sessionData = Utils::util_decrypt_token($tokenWithExpiry);

// 生成安全随机字符串
$randomKey = Utils::util_secure_random(32, true); // URL安全的随机字符串
echo "随机密钥: " . $randomKey;

// 安全字符串比较（防时序攻击）
$isEqual = Utils::util_secure_compare($expectedToken, $userToken);
var_dump($isEqual);

// 高级用法 - 自定义配置
$crypto = new SecureCrypto(
    'master_key',
    SecureCrypto::ENGINE_SODIUM,  // 使用Sodium引擎
    SecureCrypto::MODE_URL_SAFE   // URL安全模式
);

$encrypted = $crypto->encrypt('data');
$decrypted = $crypto->decrypt($encrypted);

// 获取加密信息
$info = $crypto->getInfo();
print_r($info);
```