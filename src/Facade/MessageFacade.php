<?php

namespace Mofei\Facade;

use Mofei\Message;

/**
 * Message类的Facade门面实现
 * 支持静态调用和链式调用
 * 提供与ThinkPHP、Laravel类似的Facade体验
 */
class MessageFacade
{
    /**
     * 静态魔术方法 - 代理所有Message类的静态方法
     */
    public static function __callStatic(string $name, array $arguments)
    {
        // 直接调用Message类的对应方法
        if (method_exists(Message::class, $name)) {
            return Message::$name(...$arguments);
        }
        
        throw new \BadMethodCallException("Static method {$name} not found in " . Message::class);
    }
}
