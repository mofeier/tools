<?php
require_once 'vendor/autoload.php';

use Mofeier\Tools\StatusCodes;
use Mofeier\Tools\Message;

// 清空自定义状态码
StatusCodes::clearCustomCodes();
echo "After clear, custom codes count: " . count(StatusCodes::getAllCodes()) . "\n";

// 设置自定义状态码 - 使用整数键
StatusCodes::setCustomCodes([
    9001 => '测试状态码'
]);
$allCodes = StatusCodes::getAllCodes();
echo "After set custom codes, total count: " . count($allCodes) . "\n";
echo "Code 9001 in array: " . (array_key_exists(9001, $allCodes) ? 'true' : 'false') . "\n";
echo "Code 9001 value: " . ($allCodes[9001] ?? 'not found') . "\n";

// 检查状态码是否存在
echo "Status code 9001 exists: " . (StatusCodes::exists(9001) ? 'true' : 'false') . "\n";
echo "Status code 9001 message: " . StatusCodes::getMessage(9001) . "\n";

// 直接检查静态变量
echo "\n=== 调试静态变量 ===\n";
// 使用反射来查看私有静态变量
$reflection = new ReflectionClass('Mofeier\\Tools\\StatusCodes');
$customCodesProperty = $reflection->getProperty('customCodes');
$customCodesProperty->setAccessible(true);
$customCodes = $customCodesProperty->getValue();
echo "Custom codes array: " . json_encode($customCodes, JSON_UNESCAPED_UNICODE) . "\n";
echo "Custom codes count: " . count($customCodes) . "\n";
echo "Key 9001 exists in custom codes: " . (array_key_exists(9001, $customCodes) ? 'true' : 'false') . "\n";