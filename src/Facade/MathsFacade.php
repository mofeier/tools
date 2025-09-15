<?php

namespace Mofei\Facade;

use Mofei\Maths;

/**
 * Maths类的Facade门面实现
 * 支持静态调用和链式调用
 * 提供与ThinkPHP、Laravel类似的Facade体验
 */
class MathsFacade
{
    /**
     * 静态魔术方法 - 代理所有Maths类的静态方法
     */
    public static function __callStatic(string $name, array $arguments)
    {
        // 直接调用Maths类的对应方法
        if (method_exists(Maths::class, $name)) {
            return Maths::$name(...$arguments);
        }
        
        throw new \BadMethodCallException("Static method {$name} not found in " . Maths::class);
    }
}
