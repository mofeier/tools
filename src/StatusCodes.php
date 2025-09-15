<?php

namespace Mofei;

/**
 * 状态码配置类
 * 统一管理系统状态码和对应消息
 * 兼容PHP 7.4+
 */
class StatusCodes
{
    /**
     * 默认状态码映射
     */
    public const DEFAULT_CODES = [
        // 成功状态
        200 => '成功',
        2000 => '成功',
        
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
    private static $customCodes = [];
    
    /**
     * 获取状态码对应的消息
     * 先检查自定义状态码，再检查默认状态码
     */
    public static function getMessage($code): string
    {
        $code = (int)$code;
        
        if (isset(self::$customCodes[$code])) {
            return self::$customCodes[$code];
        }
        
        if (isset(self::DEFAULT_CODES[$code])) {
            return self::DEFAULT_CODES[$code];
        }
        
        return 'Unknown Status';
    }
    
    /**
     * 设置自定义状态码
     * @param array $codes 自定义状态码数组
     */
    public static function setCustomCodes(array $codes): void
    {
        // 确保所有键都是整数
        $intCodes = [];
        foreach ($codes as $code => $message) {
            $intCodes[(int)$code] = $message;
        }
        
        // 直接设置，不使用array_merge避免数字键被重新索引
        self::$customCodes = $intCodes;
    }
    
    /**
     * 获取所有状态码（默认+自定义）
     */
    public static function getAllCodes(): array
    {
        return array_merge(self::DEFAULT_CODES, self::$customCodes);
    }
    
    /**
     * 检查状态码是否存在
     */
    public static function exists($code): bool
    {
        $code = (int)$code;
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
     * 删除自定义状态码
     */
    public static function removeCustomCode($code): void
    {
        $code = (int)$code;
        if (isset(self::$customCodes[$code])) {
            unset(self::$customCodes[$code]);
        }
    }
}
