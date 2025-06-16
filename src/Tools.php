<?php

namespace Mofeier\Tools;

/**
 * 主工具类
 * 整合所有工具功能的统一入口
 */
class Tools
{
    /**
     * 获取消息体实例
     */
    public static function message(array $fields = []): Message
    {
        return Message::create($fields);
    }

    /**
     * 消息体相关方法（简化版）
     */
    public static function msg(array $fields = []): Message
    {
        return Message::create($fields);
    }

    public static function code(int $code): Message
    {
        return Message::create()->code($code);
    }

    public static function data(array $data): Message
    {
        return Message::create()->data($data);
    }

    public static function result(): array
    {
        return Message::create()->result();
    }

    public static function json(array $fields = []): string
    {
        return Message::create($fields)->json();
    }

    public static function xml(array $fields = []): string
    {
        return Message::create($fields)->xml();
    }

    /**
     * 字符串转换工具
     */
    public static function string(): string
    {
        return StringConverter::class;
    }

    /**
     * 实用工具
     */
    public static function utils(): string
    {
        return Utils::class;
    }

    /**
     * 数学计算工具
     */
    public static function math(): string
    {
        return MathCalculator::class;
    }

    /**
     * 魔术方法，支持直接调用各个工具类的静态方法
     */
    public static function __callStatic(string $name, array $arguments)
    {
        // 消息体相关方法
        if (method_exists(Message::class, $name)) {
            return Message::$name(...$arguments);
        }

        // 字符串转换相关方法
        if (str_starts_with($name, 'str_') && method_exists(StringConverter::class, $name)) {
            return StringConverter::$name(...$arguments);
        }

        // 工具函数相关方法
        if (str_starts_with($name, 'util_') && method_exists(Utils::class, $name)) {
            return Utils::$name(...$arguments);
        }

        // 数学计算相关方法
        if (str_starts_with($name, 'math_') && method_exists(MathCalculator::class, $name)) {
            return MathCalculator::$name(...$arguments);
        }

        throw new \BadMethodCallException("Method {$name} not found");
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
        $stringMethods = get_class_methods(StringConverter::class);
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
        $mathMethods = get_class_methods(MathCalculator::class);
        foreach ($mathMethods as $method) {
            if (str_starts_with($method, 'math_')) {
                $methods['math'][] = $method;
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