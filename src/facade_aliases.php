<?php

/**
 * Facade别名定义文件
 * 定义常用类的简短别名，方便使用
 */

// 定义Facade类的别名映射
$aliases = [
    // 消息类Facade
    'Message' => 'Mofei\Facade\MessageFacade',
    
    // 工具类Facade
    'Utils' => 'Mofei\Facade\UtilsFacade',
    
    // 数学计算类Facade
    'Math' => 'Mofei\Facade\MathsFacade',
    
    // 安全加密类Facade
    'Security' => 'Mofei\Facade\SecurityFacade',
    
    // 状态码类Facade
    'Status' => 'Mofei\Facade\StatusCodesFacade',
];

// 注册别名
foreach ($aliases as $alias => $class) {
    if (!class_exists($alias, false)) {
        class_alias($class, $alias);
    }
}
