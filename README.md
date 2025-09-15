# Mofeier - PHP通用工具包

一个简洁、高效、功能丰富的PHP通用工具包，为项目开发提供基础功能支持。

## 功能特点

- 统一的API返回格式管理（消息体）
- 状态码统一管理与自定义
- 字符串处理（多字节支持）
- 数组操作（树结构转换、深度合并等）
- 安全加密（支持OpenSSL和Sodium多种加密引擎）
- URL安全的加密解密功能
- JSON/XML格式转换
- 高精度数学计算工具
- 链式调用和静态调用支持
- 符合PSR标准的命名规范
- Facade模式支持

## 系统要求

- PHP 8.1+
- Mbstring扩展（多字节字符串处理）
- JSON扩展
- OpenSSL扩展（加密功能）
- Sodium扩展（可选，提供更安全的加密）
- BC Math扩展（高精度计算）

## 安装

```bash
composer require mofei/mofeier
```

## 使用示例

### 1. 消息体处理（Message类）

统一API返回格式，支持链式调用和静态调用。

#### 基础使用

```php
use Mofei\Message;

// 基本成功响应
echo json_encode(Message::success());
// 输出: {"code":200,"msg":"成功","time":"0.000123"}

// 带数据的成功响应
echo json_encode(Message::success(['id' => 1, 'name' => 'mofeier']));
// 输出: {"code":200,"msg":"成功","data":{"id":1,"name":"mofeier"},"time":"0.000123"}

// 错误响应
echo json_encode(Message::error('操作失败', 5001));
// 输出: {"code":5001,"msg":"操作失败","time":"0.000123"}
```

#### 链式调用

```php
// 设置状态码和消息
Message::code(2002)->msg('用户不存在')->result();

// 设置数据
Message::code(2003)->data(['name' => 'mofeier'])->result();

// 自定义消息
Message::code(2003)->msg('取消操作')->data(['name' => 'mofeier'])->result();
```

#### 扩展字段

```php
// 添加时间字段
Message::add('times')->result();

// 添加分页信息
Message::total(100)->page(1)->limit(15)->result();

// 混合使用
Message::code(2005)->msg('未登录')->total(100)->limit(15)->result();
```

#### 字段映射（替换默认字段名）

```php
// 实例级映射
Message::code(200)->map(['code' => 'status', 'msg' => 'message', 'data' => 'result'])->result();

// 全局映射（应用于所有实例）
Message::setGlobalMapping(['code' => 'status', 'msg' => 'message']);
```

#### 格式转换

```php
// 直接获取JSON格式
Message::success(['id' => 1])->json();

// 直接获取XML格式
Message::success(['id' => 1])->xml();
```

#### 独立方法调用

```php
// 创建实例后单独调用方法
$message = new Message();
$message->setCode(200);
$message->setMsg('自定义消息');
$message->setData(['key' => 'value']);
$message->add('custom_field', 'custom_value');

// 检查字段是否存在
if ($message->has('custom_field')) {
    // 获取字段值
    $value = $message->get('custom_field');
    // 移除字段
    $message->remove('custom_field');
}

// 获取最终结果
$result = $message->result();
```

### 2. 状态码管理（StatusCodes类）

统一管理系统状态码和对应消息提示。

```php
use Mofei\StatusCodes;

// 获取状态码对应的消息
$msg = StatusCodes::getMessage(200); // 成功

// 检查状态码是否存在
$exists = StatusCodes::exists(2001);

// 设置自定义状态码
StatusCodes::setCustomCodes([
    6000 => '自定义错误',
    6001 => '业务逻辑错误'
]);

// 获取所有状态码（默认+自定义）
$allCodes = StatusCodes::getAllCodes();

// 清空自定义状态码
StatusCodes::clearCustomCodes();

// 删除指定的自定义状态码
StatusCodes::removeCustomCode(6000);
```

### 3. 工具函数（Utils类）

提供各种实用工具方法。

#### 数组操作

```php
use Mofei\Utils;

// 一维数组转树形结构（非递归实现）
$tree = Utils::array_to_tree($data, 'id', 'parent_id', 'children');

// 树形结构转一维数组（非递归实现）
$array = Utils::tree_to_array($tree, 'children');

// 数组深度合并
$merged = Utils::array_merge_recursive($array1, $array2);

// 获取数组指定键的值
$value = Utils::array_get($array, 'key', 'default');

// 检查数组是否包含指定键
$hasKeys = Utils::array_has_keys($array, ['key1', 'key2']);

// 移除数组中的指定键
$filtered = Utils::array_remove_keys($array, ['key1', 'key2']);

// 过滤数组中的空值
$nonEmpty = Utils::array_filter_empty($array);
```

#### 字符串处理

```php
// 计算字符串长度（支持多字节）
$length = Utils::str_length('你好世界');

// 截取字符串（支持多字节）
$substr = Utils::str_substring('你好世界', 0, 2);

// 转换字符串大小写
$lower = Utils::str_case('HELLO', MB_CASE_LOWER);
$upper = Utils::str_case('hello', MB_CASE_UPPER);
$title = Utils::str_case('hello world', MB_CASE_TITLE);

// 查找字符串位置
$pos = Utils::str_pos('hello world', 'world');
$lastPos = Utils::str_last_pos('hello world', 'o');

// 检查字符串是否以指定字符开始/结束/包含
$startsWith = Utils::str_starts_with('hello world', 'hello');
$endsWith = Utils::str_ends_with('hello world', 'world');
$contains = Utils::str_contains('hello world', 'o');
```

#### 编码解码

```php
// JSON编码（带错误处理）
$json = Utils::jsonEncode(['key' => 'value']);

// JSON解码（带错误处理）
$data = Utils::jsonDecode($json);

// 数组转换为base64字符串
$base64 = Utils::base64_encode(['key' => 'value']);

// base64字符串转换为数组
$array = Utils::base64_decode($base64);

// 数组转换为URL查询字符串
$urlQuery = Utils::url_encode(['key' => 'value']);

// URL查询字符串转换为数组
$array = Utils::url_decode($urlQuery);

// 解析URL
$parts = Utils::parse_url('https://example.com/path?query=value');
```

#### 加密解密工具方法

```php
// URL安全的加密方法（通过Utils调用）
$encrypted = Utils::util_encrypt_url('要加密的数据', 'your-secret-key');
$decrypted = Utils::util_decrypt_url($encrypted, 'your-secret-key');

// Token加密方法
$token = Utils::util_encrypt_token('用户数据', 'your-secret-key', 3600); // 有效期1小时
$decrypted = Utils::util_decrypt_token($token, 'your-secret-key');
```

#### 许可证生成

```php
// 生成许可证密钥
$license = Utils::build_license('MOFEI');
// 输出示例: MOFEI-ABCD-EFGH-IJKL-MNOP
```

#### 安全工具

```php
// 生成安全随机数
$random = Utils::generateSecureRandom(16);

// 安全比较两个字符串（防止时序攻击）
$isEqual = Utils::secureCompare($knownString, $userString);
```

### 4. 安全加密（Security类）

提供多种加密方式，支持OpenSSL和Sodium引擎。

```php
use Mofei\Security;

// 使用默认引擎和密钥加密
$encrypted = Security::openssl_encrypt('敏感数据');
$decrypted = Security::openssl_decrypt($encrypted);

// 使用Sodium引擎（需要安装Sodium扩展）
$encrypted = Security::sodium_encrypt('敏感数据');
$decrypted = Security::sodium_decrypt($encrypted);

// 实例化使用
$crypto = new Security('your-secure-key', Security::ENGINE_AUTO, Security::MODE_URL_SAFE);
$encrypted = $crypto->encrypt('敏感数据');
$decrypted = $crypto->decrypt($encrypted);

// URL安全的加密解密
$encrypted = Security::encryptForUrl('要在URL中传输的数据');
$decrypted = Security::decryptFromUrl($encrypted);

// 带过期时间的加密
$encrypted = Security::encryptWithExpiry('临时数据', 3600); // 1小时后过期
$decrypted = Security::decryptWithExpiry($encrypted);

// Token加密解密
$token = Security::encryptForToken('用户ID:123', null, 86400); // 24小时有效期
$userId = Security::decryptFromToken($token);

// 验证数据签名
$isValid = Security::verifySignature($data, $signature, $publicKey);

// 获取加密信息
$info = $crypto->getInfo();
```

### 5. 高精度数学计算（Maths类）

提供高精度数学计算功能，适用于金融计算等场景。

```php
use Mofei\Maths;

// 高精度加法
$sum = Maths::add('10.5', '20.3', 2); // 30.80

// 高精度减法
$diff = Maths::sub('100.5', '45.25', 2); // 55.25

// 高精度乘法
$product = Maths::mul('10.5', '2.5', 2); // 26.25

// 高精度除法
$quotient = Maths::div('100', '3', 2); // 33.33

// 取模运算
$modulus = Maths::mod('100', '7'); // 2

// 幂运算
$power = Maths::pow('2', '10', 0); // 1024

// 平方根
$sqrt = Maths::sqrt('16', 2); // 4.00

// 比较数值大小
$isGreater = Maths::comp('10.5', '10.3') > 0; // true
```

### 6. Facade模式使用

Facade模式提供了更简洁的调用方式，类似于Laravel和ThinkPHP的Facade体验。

#### 基础使用

```php
use Mofei\Facade;

// 消息体Facade
$result = Facade::message()->success(['user' => 'mofei']);

// 工具类Facade
$json = Facade::utils()->jsonEncode(['key' => 'value']);

// 数学计算Facade
$sum = Facade::math()->add('1', '2');

// 安全加密Facade
$crypto = Facade::crypto();
$encrypted = $crypto->encrypt('敏感数据');
$decrypted = $crypto->decrypt($encrypted);

// 状态码Facade
$msg = Facade::status()->getMessage(200);
```

#### 链式调用与Facade

```php
// 结合链式调用和Facade
$encrypted = Facade::crypto()->key('your-secret-key')->encrypt('敏感数据');
$decrypted = Facade::crypto()->key('your-secret-key')->decrypt($encrypted);

// 获取结果
$encryptedStr = Facade::getResult();

// 使用链式调用创建响应
$response = Facade::message()
    ->code(2001)
    ->msg('参数错误')
    ->data(['field' => 'missing'])
    ->result();
```

#### 静态方法直接调用

```php
// 直接调用Message类的静态方法
$result = Facade::success(['id' => 1]);

// 直接调用Utils类的静态方法
$json = Facade::jsonEncode(['key' => 'value']);

// 直接调用Maths类的静态方法
$sum = Facade::add('1', '2');

// 直接调用Security类的静态方法
$encrypted = Facade::encryptForUrl('敏感数据');
```

## 架构设计

### 目录结构

```
├── src/
│   ├── Message.php         # 消息体类
│   ├── StatusCodes.php     # 状态码配置类
│   ├── Utils.php           # 工具类
│   ├── Security.php        # 安全加密类
│   ├── Maths.php           # 数学计算类
│   ├── Facade.php          # 基础Facade类
│   ├── facade_aliases.php  # Facade别名定义
│   └── Facade/             # 门面模式实现
├── tests/                  # 测试文件
├── composer.json
├── LICENSE
└── README.md
```

### 命名规范

- 类名：采用帕斯卡命名法（PascalCase）
- 方法名：采用小驼峰命名法（camelCase）
- 常量：采用全大写+下划线（UPPER_CASE_WITH_UNDERSCORE）
- 私有属性：以下划线开头（_privateProperty）

### 错误处理

所有方法都包含适当的错误处理机制，对于关键操作会抛出异常，方便进行错误追踪和调试。

## 测试

运行测试：

```bash
php tests/facade_test.php
```

## 贡献

欢迎提交Issue和Pull Request来帮助改进这个工具包。

## 许可证

本项目采用 MIT 开源许可证。
