<?php

namespace Mofei;

use Mofei\Message;
use Mofei\Utils;
use Mofei\Maths;
use Mofei\Security;
use Mofei\StatusCodes;

/**
 * Facade门面类
 * 实现类似ThinkPHP、Laravel中的Facade模式调用方式
 * 支持所有工具类方法的统一链式调用和静态+链式调用
 * 使用PHP8.1+特性优化
 */
class Facade
{
    /**
     * 当前实例
     */
    private static ?self $instance = null;

    /**
     * 上一次操作的结果
     */
    private mixed $lastResult = null;

    /**
     * 当前代理的类名
     */
    private ?string $currentClass = null;

    /**
     * 支持的工具类映射
     */
    private static array $classMap = [
        'message' => Message::class,
        'utils' => Utils::class,
        'math' => Maths::class,
        'crypto' => Security::class,
        'status' => StatusCodes::class
    ];

    /**
     * 构造函数
     */
    private function __construct()
    {
        // 私有构造函数，防止直接实例化
    }

    /**
     * 获取实例 - 单例模式
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 重置实例状态
     */
    public static function reset(): self
    {
        self::$instance = null;
        return self::getInstance();
    }

    /**
     * 消息体工具入口
     */
    public static function message(): self
    {
        $instance = self::getInstance();
        $instance->currentClass = Message::class;
        return $instance;
    }

    /**
     * 字符串转换工具入口
     */
    public static function string(): self
    {
        $instance = self::getInstance();
        $instance->currentClass = Utils::class;
        return $instance;
    }

    /**
     * 实用工具入口
     */
    public static function utils(): self
    {
        $instance = self::getInstance();
        $instance->currentClass = Utils::class;
        return $instance;
    }

    /**
     * 数学计算工具入口
     */
    public static function math(): self
    {
        $instance = self::getInstance();
        $instance->currentClass = Maths::class;
        return $instance;
    }

    /**
     * 加密工具入口
     */
    public static function crypto(): self
    {
        $instance = self::getInstance();
        $instance->currentClass = Security::class;
        return $instance;
    }

    /**
     * 静态魔术方法 - 支持所有工具类方法的静态调用
     * 实现类似ThinkPHP、Laravel的Facade调用方式
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $instance = self::getInstance();
        $result = null;

        // 尝试直接调用工具类方法
        foreach (self::$classMap as $prefix => $className) {
            // 检查是否有带前缀的方法
            if ($prefix === 'string' && method_exists($className, $name)) {
                // 对于StringConverter类，直接使用方法名（因为它的方法命名不符合前缀约定）
                $result = $className::$name(...$arguments);
                $instance->lastResult = $result;
                return $result;
            }
            
            $prefixedMethod = $prefix === 'crypto' ? 'util_' . $name : ($prefix !== 'message' ? $prefix . '_' . $name : $name);
            
            if (method_exists($className, $prefixedMethod)) {
                $result = $className::$prefixedMethod(...$arguments);
                $instance->lastResult = $result;
                return $result;
            }
        }

        // 如果是工具类入口方法
        if (array_key_exists($name, self::$classMap)) {
            $instance->currentClass = self::$classMap[$name];
            return $instance;
        }

        throw new \BadMethodCallException("Method {$name} not found in any tool class");
    }

    /**
     * 实例魔术方法 - 支持所有工具类方法的实例调用和链式调用
     */
    public function __call(string $name, array $arguments)
    {
        // 处理加密相关方法（特殊处理）
        if ($name === 'key' && count($arguments) === 1) {
            // 设置加密密钥（这里简化处理，实际项目中可能需要更复杂的实现）
            // 存储密钥供后续加密解密使用
            $this->cryptoKey = $arguments[0];
            $this->lastResult = $arguments[0];
            return $this;
        }

        if ($name === 'encrypt') {
            // 获取要加密的数据
            $data = count($arguments) > 0 ? $arguments[0] : $this->lastResult;
            // 检查数据是否存在
            if ($data === null) {
                throw new \InvalidArgumentException('No data to encrypt');
            }
            // 检查是否设置了密钥
            $key = property_exists($this, 'cryptoKey') ? $this->cryptoKey : null;
            // 确保数据是字符串
            $dataStr = is_array($data) || is_object($data) ? json_encode($data) : (string)$data;
            // 执行加密
            $result = Security::encryptForUrl($dataStr, $key);
            $this->lastResult = $result;
            return $this;
        }

        if ($name === 'decrypt') {
            // 获取要解密的数据
            $data = count($arguments) > 0 ? $arguments[0] : $this->lastResult;
            // 检查数据是否存在
            if ($data === null) {
                throw new \InvalidArgumentException('No data to decrypt');
            }
            // 检查是否设置了密钥
            $key = property_exists($this, 'cryptoKey') ? $this->cryptoKey : null;
            // 执行解密
            $result = Security::decryptFromUrl((string)$data, $key);
            $this->lastResult = $result;
            return $this;
        }

        // 如果指定了当前类，优先调用该类的方法
        if ($this->currentClass !== null) {
            // 处理加密相关方法
            if (Utils::str_starts_with($name, 'crypto_') && $this->currentClass === Utils::class) {
                $method = 'util_' . $name;
                if (method_exists($this->currentClass, $method)) {
                    $result = $this->currentClass::$method(...$arguments);
                    $this->lastResult = $result;
                    return $this;
                }
            }

            // 检查是否存在该方法
            if (method_exists($this->currentClass, $name)) {
                // 如果有存储的参数，则使用存储的参数
                $useArgs = empty($arguments) && !empty($this->arguments) ? $this->arguments : $arguments;
                // 重置参数存储
                $this->arguments = [];
                
                $result = $this->currentClass::$name(...$useArgs);
                $this->lastResult = $result;
                // 特殊处理Message类的返回值
                if ($this->currentClass === Message::class) {
                    return is_object($result) && $result instanceof Message ? $result : $this;
                }
                // 特殊处理Utils类的返回值
                if ($this->currentClass === Utils::class) {
                    return $result;
                }
                // 特殊处理Maths类的返回值
                if ($this->currentClass === Maths::class) {
                    return $result;
                }
                // 其他所有类都返回Facade实例，以支持链式调用
                return $this;
            }
        }

        // 尝试调用所有支持的工具类方法
        foreach (self::$classMap as $prefix => $className) {
            // 处理命名约定
            if ($prefix === 'message') {
                // Message类方法不需要前缀
                if (method_exists($className, $name)) {
                    $result = $className::$name(...$arguments);
                    $this->lastResult = $result;
                    return $result instanceof Message ? $result : $this;
                }
            } elseif ($prefix === 'crypto') {
                // 加密方法前缀为util_
                $method = 'util_' . $name;
                if (method_exists($className, $method)) {
                    $result = $className::$method(...$arguments);
                    $this->lastResult = $result;
                    return $this;
                }
            } else {
                // 对于Utils类，直接使用方法名（因为它的方法命名不符合前缀约定）
                if ($prefix === 'string' && method_exists($className, $name)) {
                    $result = $className::$name(...$arguments);
                    $this->lastResult = $result;
                    return $result;
                }
                
                // 其他类方法前缀为类名_方法名
                $method = $prefix . '_' . $name;
                if (method_exists($className, $method)) {
                    $result = $className::$method(...$arguments);
                    $this->lastResult = $result;
                    return $this;
                }
            }
        }

        throw new \BadMethodCallException("Method {$name} not found in any tool class");
    }

    /**
     * 获取上一次操作的结果
     */
    public static function getResult()
    {
        return self::getInstance()->lastResult;
    }

    /**
     * 使用上一次操作的结果作为当前操作的参数
     * 支持链式调用中使用前一步的结果
     */
    public static function useLastResult()
    {
        $instance = self::getInstance();
        $instance->arguments = [$instance->lastResult];
        return $instance;
    }

    /**
     * 存储当前方法的参数
     */
    private array $arguments = [];

    /**
     * 设置当前代理的类
     */
    public function setCurrentClass(string $className): self
    {
        if (class_exists($className)) {
            $this->currentClass = $className;
        }
        return $this;
    }

    /**
     * 获取支持的所有工具类
     */
    public static function getSupportedClasses(): array
    {
        return self::$classMap;
    }
}
