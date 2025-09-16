<?php

namespace Mofei;

use Mofei\Message;
use Mofei\Utils;
use Mofei\Maths;
use Mofei\Security;
use Mofei\StatusCodes;

/**
 * 主工具类 - 整合所有工具类功能
 * 支持所有方法可链式调用和静态+链式调用
 * 使用PHP8.1+特性优化
 * 统一调用入口：Tools::xxx()->xxx()->result()
 */
class Tools
{
    /**
     * 当前实例
     */
    private static ?self $instance = null;

    /**
     * 消息体实例
     */
    private ?Message $message = null;

    /**
     * 上一次操作的结果
     */
    private mixed $lastResult = null;

    /**
     * 构造函数
     */
    private function __construct()
    {
        $this->message = new Message();
    }

    /**
     * 获取实例 - 用于链式调用
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 创建成功消息
     */
    public static function success(mixed $data = null, string $msg = 'success', int $code = 200): Message
    {
        return Message::success($data, $msg, $code);
    }

    /**
     * 创建错误消息
     */
    public static function error(string $msg = 'error', int $code = 500, mixed $data = null): Message
    {
        return Message::error($msg, $code, $data);
    }

    /**
     * 创建自定义消息
     */
    public static function message(int $code = 200, string $msg = '', mixed $data = null): Message
    {
        return Message::create($code, $msg, $data);
    }

    /**
     * 获取消息结果 - 支持静态和链式调用
     */
    public static function result()
    {
        $result = Message::create()->result();
        self::getInstance()->lastResult = $result;
        return $result;
    }

    /**
     * 获取JSON格式结果 - 支持静态和链式调用
     */
    public static function json(array $fields = []): string
    {
        $result = Message::create($fields)->json();
        self::getInstance()->lastResult = $result;
        return $result;
    }

    /**
     * 获取XML格式结果 - 支持静态和链式调用
     */
    public static function xml(array $fields = []): string
    {
        $result = Message::create($fields)->xml();
        self::getInstance()->lastResult = $result;
        return $result;
    }

    /**
     * 字符串转换工具 - 返回工具类实例，支持链式调用
     */
    public static function string(): self
    {
        return self::getInstance();
    }

    /**
     * 实用工具 - 返回工具类实例，支持链式调用
     */
    public static function utils(): self
    {
        return self::getInstance();
    }

    /**
     * 数学计算工具 - 返回工具类实例，支持链式调用
     */
    public static function math(): self
    {
        return self::getInstance();
    }

    /**
     * 加密工具 - 返回工具类实例，支持链式调用
     */
    public static function crypto(): self
    {
        return self::getInstance();
    }

    /**
     * 魔术方法，支持直接调用各个工具类的静态方法
     * 确保所有方法都能支持链式调用和静态+链式调用
     */
    public static function __callStatic(string $name, array $arguments)
    {
        // 检查是否是调用工具类的入口方法
        if (in_array($name, ['string', 'utils', 'math', 'crypto'])) {
            return self::getInstance();
        }

        // 消息体相关方法 - 优先处理，因为它们需要特殊的链式调用支持
        if (method_exists(Message::class, $name)) {
            $instance = self::getInstance();
            
            // 如果是直接调用静态方法，返回Message实例以支持链式调用
            $result = Message::$name(...$arguments);
            
            // 保存结果到lastResult以便通过getResult()获取
            $instance->lastResult = $result;
            
            return $result;
        }

        // 字符串转换相关方法
        if ((Utils::str_starts_with($name, 'str_') || Utils::str_starts_with($name, 'to') || Utils::str_ends_with($name, 'Case'))) {
            if (method_exists(Utils::class, $name)) {
                $result = Utils::$name(...$arguments);
                // 保存结果到lastResult
                self::getInstance()->lastResult = $result;
                return $result;
            }
            // 对于str_array_to_tree等方法，去掉str_前缀
            if (Utils::str_starts_with($name, 'str_')) {
                $methodName = substr($name, 4);
                if (method_exists(Utils::class, $methodName)) {
                    $result = Utils::$methodName(...$arguments);
                    // 保存结果到lastResult
                    self::getInstance()->lastResult = $result;
                    return $result;
                }
            }
        }

        // 工具函数相关方法
        if (str_starts_with($name, 'util_') && method_exists(Utils::class, $name)) {
            $result = Utils::$name(...$arguments);
            // 保存结果到lastResult
            self::getInstance()->lastResult = $result;
            return $result;
        }

        // 数学计算相关方法
        if (str_starts_with($name, 'math_')) {
            // 移除math_前缀
            $methodName = substr($name, 5);
            
            // 特殊处理带bc前缀的方法，例如math_bcadd映射到add
            if (str_starts_with($methodName, 'bc')) {
                $bcMethodName = substr($methodName, 2);
                if (method_exists(Maths::class, $bcMethodName)) {
                    $result = Maths::$bcMethodName(...$arguments);
                    // 保存结果到lastResult
                    self::getInstance()->lastResult = $result;
                    return $result;
                }
            }
            
            // 普通方法映射
            if (method_exists(Maths::class, $methodName)) {
                $result = Maths::$methodName(...$arguments);
                // 保存结果到lastResult
                self::getInstance()->lastResult = $result;
                return $result;
            }
        }

        // 加密相关方法
        if (str_starts_with($name, 'crypto_') && method_exists(Utils::class, 'util_' . $name)) {
            $result = Utils::{'util_' . $name}(...$arguments);
            // 保存结果到lastResult
            self::getInstance()->lastResult = $result;
            return $result;
        }

        throw new \BadMethodCallException("Method {$name} not found");
    }

    /**
     * 实例魔术方法 - 支持所有静态方法的实例调用，并确保链式调用一致性
     * 实例方法调用时总是返回实例本身，以支持连续链式调用
     * 当连续调用工具函数时，会自动使用上一次操作的结果作为输入参数（如果没有提供参数）
     */
    public function __call(string $name, array $arguments)
    {
        // 对于消息体方法，委托给Message实例处理
        if (method_exists(Message::class, $name) && !in_array($name, ['__construct', '__call', '__callStatic'])) {
            // 如果参数为空，直接返回实例以支持静态+链式调用
            if (empty($arguments) && in_array($name, ['code', 'msg', 'data', 'success', 'error', 'message'])) {
                return $this;
            }
            
            // 调用Message类的同名方法
            $result = Message::$name(...$arguments);
            
            // 保存结果到lastResult
            $this->lastResult = $result;
            
            // 返回实例本身以支持继续链式调用
            return $this;
        }

        // 检查是否是Utils类的方法
        if (method_exists(Utils::class, $name)) {
            $result = Utils::$name(...$arguments);
            $this->lastResult = $result;
            return $result;
        }

        // 对于工具函数方法，如果没有提供参数但有lastResult，使用lastResult作为参数
        if (empty($arguments) && $this->lastResult !== null && Utils::str_starts_with($name, 'util_')) {
            if (method_exists(Utils::class, $name)) {
                $result = Utils::$name($this->lastResult);
                $this->lastResult = $result;
                return $this;
            }
        }

        // 对于数学计算方法，如果没有提供参数但有lastResult，使用lastResult作为参数
        if (empty($arguments) && $this->lastResult !== null && Utils::str_starts_with($name, 'math_')) {
            if (method_exists(Maths::class, $name)) {
                $result = Maths::$name($this->lastResult);
                $this->lastResult = $result;
                return $this;
            }
        }

        // 对于其他方法，直接调用实际的类方法
        if (Utils::str_starts_with($name, 'util_') && method_exists(Utils::class, $name)) {
            $result = Utils::$name(...$arguments);
            $this->lastResult = $result;
            return $result;
        }
        
        if (Utils::str_starts_with($name, 'math_') && method_exists(Maths::class, $name)) {
            $result = Maths::$name(...$arguments);
            $this->lastResult = $result;
            return $result;
        }

        // 对于加密相关方法
        if (str_starts_with($name, 'crypto_') && method_exists(Utils::class, 'util_' . $name)) {
            $result = Utils::{'util_' . $name}(...$arguments);
            $this->lastResult = $result;
            return $result;
        }

        throw new \BadMethodCallException("Method {$name} not found");
    }

    /**
     * 获取上一次操作的结果
     */
    public static function getResult()
    {
        $instance = self::getInstance();
        return $instance->lastResult ?? null;
    }

    /**
     * 重置实例和状态
     * 清除所有之前的操作结果和状态，返回新的实例以支持链式调用
     * 
     * @return self 返回新的Tools实例，支持链式调用
     */
    public static function reset(): self
    {
        // 清除实例引用，强制下一次调用getInstance()时创建新实例
        self::$instance = null;
        // 返回新的实例以支持链式调用
        return self::getInstance();
    }

    /**
     * 版本信息
     */
    public static function version(): string
    {
        return '1.0.0';
    }

    /**
     * 获取所有可用的方法列表
     */
    public static function getMethods(): array
    {
        $methods = [
            'message' => [],
            'string' => [],
            'utils' => [],
            'math' => []
        ];

        // 获取消息体方法
        $messageMethods = get_class_methods(Message::class);
        foreach ($messageMethods as $method) {
            if (!str_starts_with($method, '__')) {
                $methods['message'][] = $method;
            }
        }

        // 获取字符串转换方法
        $stringMethods = get_class_methods(Utils::class);
        foreach ($stringMethods as $method) {
            if (str_starts_with($method, 'str_')) {
                $methods['string'][] = $method;
            }
        }

        // 获取工具方法
        $utilsMethods = get_class_methods(Utils::class);
        foreach ($utilsMethods as $method) {
            if (str_starts_with($method, 'util_')) {
                $methods['utils'][] = $method;
            }
        }

        // 获取数学方法
        $mathMethods = get_class_methods(Maths::class);
        foreach ($mathMethods as $method) {
            if (!str_starts_with($method, '__')) {
                // 特殊处理bc前缀方法
                if (str_starts_with($method, 'bc')) {
                    $methods['math'][] = 'math_' . $method;
                } else {
                    $methods['math'][] = 'math_' . $method;
                }
            }
        }

        return $methods;
    }

    /**
     * 获取帮助信息
     */
    public static function help(): array
    {
        return [
            'description' => '通用工具包 - 提供消息体、字符转换、实用工具和数学计算功能',
            'version' => self::version(),
            'usage' => [
                '消息体' => [
                    'description' => '提供统一的API响应格式',
                    'examples' => [
                        'Tools::message()->result()' => '返回默认成功响应',
                        'Tools::message()->code(2002)->msg("失败")->result()' => '返回自定义响应',
                        'Tools::code(200)->data(["name" => "test"])->json()' => '直接静态调用并返回JSON格式'
                    ]
                ],
                '字符串转换' => [
                    'description' => '提供各种字符串和数据类型转换功能',
                    'examples' => [
                        'Tools::str_to_int("123")' => '字符串转整数',
                        'Tools::str_array_to_tree($array)' => '一维数组转树形结构',
                        'Tools::str_camel_to_snake("camelCase")' => '驼峰转下划线'
                    ]
                ],
                '实用工具' => [
                    'description' => '提供常用的工具函数',
                    'examples' => [
                        'Tools::util_json_encode($data)' => 'JSON编码',
                        'Tools::util_generate_uuid()' => '生成UUID',
                        'Tools::util_validate_email($email)' => '验证邮箱格式'
                    ]
                ],
                '数学计算' => [
                    'description' => '提供数学计算功能，支持高精度计算',
                    'examples' => [
                        'Tools::math_bcadd("0.1", "0.2", 2)' => '高精度加法',
                        'Tools::math_average([1, 2, 3, 4, 5])' => '计算平均值',
                        'Tools::math_percentage(25, 100)' => '计算百分比'
                    ]
                ]
            ],
            'methods' => self::getMethods()
        ];
    }
}
