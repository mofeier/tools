# Mofeier Tools - 通用工具包

这是一个基于PHP8+的通用工具包，提供消息体统一API返回格式、字符转换、实用工具和数学计算等功能。

## 特性

- **消息体管理**: 统一的API响应格式，支持链式调用和静态调用
- **字符串转换**: 各种数据类型转换，包括数组树形转换等
- **实用工具**: 常用的编码解码、验证、格式化等功能
- **数学计算**: 支持高精度计算和统计函数
- **现代架构**: 基于PHP8+，支持命名空间和自动加载
- **易于扩展**: 模块化设计，便于后续功能扩展

## 安装

### 通过Composer安装

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
require_once 'vendor/autoload.php';

use Mofeier\Tools\Tools;
use Mofeier\Tools\Message;

// 消息体处理 - 基本用法
$result = Message::create()->code(200)->msg('成功')->data(['user' => 'mofeier'])->result();
echo json_encode($result, JSON_UNESCAPED_UNICODE);
// 输出: {"code":200,"msg":"成功","data":{"user":"mofeier"}}

// 消息体处理 - 构造时设置字段
$message = Message::create(['total' => 100, 'page' => 1])->code(200)->json();
echo $message;
// 输出: {"code":200,"msg":"Success","data":[],"total":100,"page":1}

// 消息体处理 - 静态调用
$json = Message::code(200)->data(['test' => 'static'])->json();

// 消息体处理 - Tools简化调用
$result = Tools::code(200)->data(['from' => 'tools'])->json();
$direct = Tools::json(['code' => 201, 'msg' => 'Created']);

// 字符串转换
$tree = Tools::array_to_tree([
    ['id' => 1, 'parent_id' => 0, 'name' => '根节点'],
    ['id' => 2, 'parent_id' => 1, 'name' => '子节点']
]);

// 实用工具
$json = Tools::util_json_encode(['name' => 'mofeier']);
$array = Tools::util_json_decode($json);

// 数学计算
$result = Tools::math_bcadd('10.5', '20.3', 2); // 30.80
```

## 详细使用说明

### 1. 消息体 (Message)

消息体类提供统一的API响应格式，支持链式调用、静态调用和独立方法调用。

#### 基本用法

```php
use Mofeier\Tools\Message;

// 基本使用
$message = new Message();
$result = $message->result();
// 输出: ['code' => 2000, 'msg' => 'Success', 'data' => []]

// 链式调用
$result = (new Message())
    ->code(200)
    ->msg('操作成功')
    ->data(['user_id' => 123])
    ->result();

// 静态调用
$result = Message::code(200)->msg('成功')->data(['test' => 'value'])->result();

// 静态创建实例
$message = Message::create(['total' => 100, 'page' => 1]);
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

#### 自定义状态码

```php
// 设置自定义状态码（使用独立的StatusCodes类）
Message::setCustomCodes([
    3001 => '自定义成功',
    3002 => '自定义失败'
]);

$result = Message::code(3001)->result();
// 输出: ['code' => 3001, 'msg' => '自定义成功', 'data' => []]

// 检查状态码是否存在
if (Message::codeExists(3001)) {
    echo '状态码存在';
}

// 获取所有状态码
$allCodes = Message::getAllCodes();
```

#### Tools类简化调用

```php
use Mofeier\Tools\Tools;

// 简化的消息体调用
$result = Tools::code(200)->data(['from' => 'tools'])->json();
$message = Tools::msg(['total' => 100])->code(200)->result();

// 直接输出格式
$json = Tools::json(['code' => 201, 'msg' => 'Created']);
$xml = Tools::xml(['code' => 200, 'data' => ['test' => 'value']]);
```

### 2. 字符串转换 (StringConverter)

所有字符串转换函数以`str_`开头。

#### 基本类型转换

```php
// 类型转换
$int = Tools::str_to_int('123');        // 123
$float = Tools::str_to_float('123.45'); // 123.45
$bool = Tools::str_to_bool('true');     // true

// 字符串操作
$snake = Tools::str_camel_to_snake('userName');  // user_name
$camel = Tools::str_snake_to_camel('user_name'); // userName
$array = Tools::str_to_array('a,b,c', ',');     // ['a', 'b', 'c']
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

### 3. 实用工具 (Utils)

所有实用工具函数以`util_`开头。

#### 编码解码

```php
// JSON操作
$json = Tools::util_json_encode($data);
$array = Tools::util_json_decode($json);

// Base64操作
$base64 = Tools::util_base64_encode($data);
$decoded = Tools::util_base64_decode($base64);

// URL操作
$query = Tools::util_url_encode(['name' => '张三', 'age' => 25]);
$array = Tools::util_url_decode('name=张三&age=25');
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

### 4. 数学计算 (MathCalculator)

所有数学函数以`math_`开头。

#### 高精度计算

```php
// 高精度四则运算
$sum = Tools::math_bcadd('0.1', '0.2', 4);      // 0.3000
$diff = Tools::math_bcsub('1.0', '0.3', 4);     // 0.7000
$product = Tools::math_bcmul('0.1', '0.3', 4);  // 0.0300
$quotient = Tools::math_bcdiv('1', '3', 6);     // 0.333333

// 高精度比较
$compare = Tools::math_bccomp('0.1', '0.2', 2); // -1 (小于)
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

## 更新日志

### v1.0.0
- 初始版本发布
- 实现消息体管理功能
- 实现字符串转换功能
- 实现实用工具功能
- 实现数学计算功能