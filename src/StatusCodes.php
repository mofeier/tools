<?php

namespace Mofeier\Tools;

/**
 * 状态码配置类
 * 统一管理系统状态码和对应消息
 */
class StatusCodes
{
    /**
     * 默认状态码映射
     */
    public const DEFAULT_CODES = [
        // 成功状态
        200 => 'Success',
        2000 => 'Success',
        
        // 客户端错误
        2001 => '参数错误',
        2002 => '用户不存在',
        2003 => '密码错误',
        2004 => '用户已冻结',
        2005 => '用户未登录',
        2006 => '用户已登录',
        2007 => '用户已存在',
        2008 => '用户不存在',
        2009 => '用户已存在',
        2010 => '用户不存在',
        2011 => '用户已存在',
        
        // 服务器错误
        5000 => '服务器内部错误',
        5001 => '数据库连接失败',
        5002 => '文件操作失败',
        5003 => '网络请求失败',
        5004 => '缓存操作失败',
        
        // 业务错误
        4000 => '业务处理失败',
        4001 => '权限不足',
        4002 => '资源不存在',
        4003 => '操作频繁',
        4004 => '数据格式错误'
    ];
    
    /**
     * 自定义状态码映射
     */
    private static array $customCodes = [];
    
    /**
     * 获取状态码对应的消息
     */
    public static function getMessage(int $code): string
    {
        // 首先检查自定义状态码
        if (isset(self::$customCodes[$code])) {
            return self::$customCodes[$code];
        }
        
        // 然后检查默认状态码
        if (isset(self::DEFAULT_CODES[$code])) {
            return self::DEFAULT_CODES[$code];
        }
        
        // 都没有找到返回默认消息
        return 'Unknown Status';
    }
    
    /**
     * 设置自定义状态码映射
     */
    public static function setCustomCodes(array $codes): void
    {
        // 确保键是整数类型
        $intCodes = [];
        foreach ($codes as $code => $message) {
            $intCodes[(int)$code] = $message;
        }
        // 使用 + 操作符保持键值关系，而不是 array_merge
        self::$customCodes = self::$customCodes + $intCodes;
    }
    
    /**
     * 获取所有状态码
     */
    public static function getAllCodes(): array
    {
        // 使用 + 操作符保持键值关系
        return self::DEFAULT_CODES + self::$customCodes;
    }
    
    /**
     * 检查状态码是否存在
     */
    public static function exists(int $code): bool
    {
        return isset(self::$customCodes[$code]) || isset(self::DEFAULT_CODES[$code]);
    }
    
    /**
     * 清空自定义状态码
     */
    public static function clearCustomCodes(): void
    {
        self::$customCodes = [];
    }
    
    /**
     * 移除指定的自定义状态码
     */
    public static function removeCustomCode(int $code): void
    {
        unset(self::$customCodes[$code]);
    }
}