<?php

// 引入自动加载文件
require_once __DIR__ . '/../vendor/autoload.php';

use Mofei\Maths;

// 直接测试Maths类的sub方法
$result = Maths::sub('10', '3.333', 2);
echo "Maths::sub('10', '3.333', 2) 结果: {$result}\n";
echo "类型: " . gettype($result) . "\n";
