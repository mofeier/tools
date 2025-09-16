<?php

/**
 * Facade别名定义文件
 * 定义常用类的简短别名，方便使用
 */

// 定义Facade类的别名映射
$aliases = [
    // 主要Facade类
    'Facade' => 'Mofei\Facade',
    'Message' => 'Mofei\Message',
    'Utils' => 'Mofei\Utils',
    'Maths' => 'Mofei\Maths',
    'Security' => 'Mofei\Security',
    'StatusCodes' => 'Mofei\StatusCodes',
];

// 注册别名
foreach ($aliases as $alias => $class) {
    if (!class_exists($alias, false)) {
        class_alias($class, $alias);
    }
}
