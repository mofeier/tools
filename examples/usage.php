<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Mofeier\Tools\Tools;
use Mofeier\Tools\Message;
use Mofeier\Tools\StringConverter;
use Mofeier\Tools\Utils;
use Mofeier\Tools\MathCalculator;

echo "=== 通用工具包使用示例 ===\n\n";

// 1. 消息体使用示例
echo "1. 消息体使用示例:\n";
echo "-------------------\n";

// 基本用法
$result1 = Tools::message()->result();
echo "默认响应: " . json_encode($result1, JSON_UNESCAPED_UNICODE) . "\n";

// 链式调用
$result2 = Tools::message()->code(2002)->msg('用户不存在')->data(['id' => 123])->result();
echo "链式调用: " . json_encode($result2, JSON_UNESCAPED_UNICODE) . "\n";

// 直接实例化调用
$message = new Message();
$result3 = $message->code(200)->data(['name' => 'mofeier', 'age' => 25])->json();
echo "实例调用JSON: " . $result3 . "\n";

// 构造时传入字段
echo "构造时设置字段: " . Message::create(['total' => 100, 'page' => 1])->code(200)->json() . "\n";

// 批量设置字段
echo "批量设置字段: " . (new Message())->setFields(['total' => 200, 'page' => 2, 'limit' => 10])->json() . "\n";

// 添加时间字段
echo "添加时间字段: " . (new Message())->add('time')->code(2005)->msg('用户未登录')->json() . "\n";

// 字段替换
echo "字段替换: " . (new Message())->code(200)->replace(['code' => 'status', 'msg' => 'message'])->json() . "\n";

// 静态调用
echo "静态调用: " . Message::code(200)->data(['test' => 'static'])->json() . "\n";

// 独立方法调用
$msg = new Message();
$msg->set('custom', 'value')->add('time');
echo "独立方法: " . $msg->json() . "\n";
echo "获取字段: code=" . $msg->get('code') . ", custom=" . $msg->get('custom') . "\n";

// XML输出
echo "XML输出: " . (new Message())->code(200)->data(['test' => 'value'])->xml() . "\n";

// 使用Tools类简化调用
echo "Tools简化调用: " . Tools::code(200)->data(['from' => 'tools'])->json() . "\n";
echo "Tools直接JSON: " . Tools::json(['code' => 201, 'msg' => 'Created']) . "\n";

// 添加分页字段
$result5 = Tools::message()->add('total', 100)->add('page', 1)->add('limit', 10)
    ->total(150)->page(2)->limit(20)->result();
echo "分页示例: " . json_encode($result5, JSON_UNESCAPED_UNICODE) . "\n";



// 自定义状态码示例
Message::setCustomCodes([3001 => '自定义成功', 3002 => '自定义失败']);
echo "自定义状态码: " . Message::code(3001)->json() . "\n";
echo "检查状态码存在: " . (Message::codeExists(3001) ? '存在' : '不存在') . "\n";

echo "\n";

// 2. 字符串转换示例
echo "2. 字符串转换示例:\n";
echo "-------------------\n";

// 基本类型转换
echo "字符串转整数: " . Tools::str_to_int('123') . "\n";
echo "字符串转浮点数: " . Tools::str_to_float('123.45') . "\n";
echo "驼峰转下划线: " . Tools::str_camel_to_snake('userName') . "\n";
echo "下划线转驼峰: " . Tools::str_snake_to_camel('user_name') . "\n";

// 数组转树形结构
$flatArray = [
    ['id' => 1, 'parent_id' => 0, 'name' => '根节点'],
    ['id' => 2, 'parent_id' => 1, 'name' => '子节点1'],
    ['id' => 3, 'parent_id' => 1, 'name' => '子节点2'],
    ['id' => 4, 'parent_id' => 2, 'name' => '孙节点1']
];
$tree = Tools::str_array_to_tree($flatArray);
echo "数组转树形: " . json_encode($tree, JSON_UNESCAPED_UNICODE) . "\n";

// 树形转数组
$flatAgain = Tools::str_tree_to_array($tree);
echo "树形转数组: " . json_encode($flatAgain, JSON_UNESCAPED_UNICODE) . "\n";

echo "\n";

// 3. 实用工具示例
echo "3. 实用工具示例:\n";
echo "-------------------\n";

// JSON操作
$data = ['name' => '张三', 'age' => 25, 'city' => '北京'];
$json = Tools::util_json_encode($data);
echo "JSON编码: " . $json . "\n";
$decoded = Tools::util_json_decode($json);
echo "JSON解码: " . json_encode($decoded, JSON_UNESCAPED_UNICODE) . "\n";

// Base64操作
$base64 = Tools::util_base64_encode($data);
echo "Base64编码: " . $base64 . "\n";
$base64Decoded = Tools::util_base64_decode($base64);
echo "Base64解码: " . json_encode($base64Decoded, JSON_UNESCAPED_UNICODE) . "\n";

// URL操作
$urlData = ['name' => '张三', 'age' => 25, 'city' => '北京'];
$urlEncoded = Tools::util_url_encode($urlData);
echo "URL编码: " . $urlEncoded . "\n";
$urlDecoded = Tools::util_url_decode('name=%E5%BC%A0%E4%B8%89&age=25&city=%E5%8C%97%E4%BA%AC');
echo "URL解码: " . json_encode($urlDecoded, JSON_UNESCAPED_UNICODE) . "\n";

// 生成UUID和随机字符串
echo "UUID: " . Tools::util_generate_uuid() . "\n";
echo "随机字符串: " . Tools::util_generate_random_string(10) . "\n";

// 验证功能
echo "邮箱验证: " . (Tools::util_validate_email('test@example.com') ? '有效' : '无效') . "\n";
echo "手机号验证: " . (Tools::util_validate_mobile('13800138000') ? '有效' : '无效') . "\n";

// 文件大小格式化
echo "文件大小格式化: " . Tools::util_format_file_size(1024 * 1024 * 2.5) . "\n";

// 时间格式化
echo "时间格式化: " . Tools::util_format_time_ago(time() - 3600) . "\n";

echo "\n";

// 4. 数学计算示例
echo "4. 数学计算示例:\n";
echo "-------------------\n";

// 高精度计算
echo "高精度加法: " . Tools::math_bcadd('0.1', '0.2', 4) . "\n";
echo "高精度乘法: " . Tools::math_bcmul('0.1', '0.3', 4) . "\n";
echo "高精度除法: " . Tools::math_bcdiv('1', '3', 6) . "\n";

// 基本数学运算
echo "普通加法: " . Tools::math_add(10, 20) . "\n";
echo "幂运算: " . Tools::math_pow(2, 8) . "\n";
echo "平方根: " . Tools::math_sqrt(16) . "\n";

// 统计计算
$numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
echo "平均值: " . Tools::math_average($numbers) . "\n";
echo "中位数: " . Tools::math_median($numbers) . "\n";
echo "方差: " . Tools::math_variance($numbers) . "\n";
echo "标准差: " . Tools::math_standard_deviation($numbers) . "\n";

// 百分比计算
echo "百分比: " . Tools::math_percentage(25, 100) . "%\n";

// 阶乘
echo "5的阶乘: " . Tools::math_factorial(5) . "\n";

// 最大公约数和最小公倍数
echo "12和18的最大公约数: " . Tools::math_gcd(12, 18) . "\n";
echo "12和18的最小公倍数: " . Tools::math_lcm(12, 18) . "\n";

echo "\n";

// 5. 帮助信息
echo "5. 帮助信息:\n";
echo "-------------------\n";
$help = Tools::help();
echo "工具包版本: " . $help['version'] . "\n";
echo "描述: " . $help['description'] . "\n";
echo "可用方法数量:\n";
foreach ($help['methods'] as $category => $methods) {
    echo "  {$category}: " . count($methods) . " 个方法\n";
}

echo "\n=== 示例结束 ===\n";