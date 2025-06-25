<?php

namespace mofei;

/**
 * 消息体类 统一API返回格式
 * 支持链式调用和静态调用
 * 默认字段：code, msg, data
 * 可扩展字段和自定义状态码
 * 支持数组键值对批量设置字段
 * 使用PHP8.1+特性优化
 */
class Message
{

    /**
     * 实例数据
     */
    private array $data = [];

    /**
     * 额外字段定义
     */
    private array $extraFields = [];

    /**
     * 字段映射
     */
    private array $fieldMapping = [];

    /**
     * 输出格式
     */
    private string $outputFormat = 'array';

    /**
     * 开始时间（用于计算执行时间）
     */
    private static float $startTime;

    /**
     * 构造函数 - 使用PHP8.1+特性
     */
    public function __construct(array $fields = [])
    {
        if (!isset(self::$startTime)) {
            self::$startTime = microtime(true);
        }
        
        // 初始化默认字段
        $this->data = [
            'code' => 200,
            'msg' => 'success',
            'data' => null,
            ...$fields // 使用数组解包操作符
        ];
    }

    /**
     * 创建实例 - 支持多种参数形式
     */
    public static function create(int|array $codeOrFields = [], string $msg = '', mixed $data = null): self
    {
        if (is_array($codeOrFields)) {
            return new self($codeOrFields);
        }
        
        return new self([
            'code' => $codeOrFields,
            'msg' => $msg,
            'data' => $data
        ]);
    }

    /**
     * 创建成功消息
     */
    public static function success(mixed $data = null, string $msg = 'success', int $code = 200): self
    {
        return new self([
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ]);
    }

    /**
     * 创建错误消息
     */
    public static function error(string $msg = 'error', int $code = 500, mixed $data = null): self
    {
        return new self([
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ]);
    }

    /**
     * 设置状态码（支持静态和实例调用）
     */
    public static function code(int $code): self
    {
        $instance = new self();
        return $instance->setCode($code);
    }

    /**
     * 设置消息（支持静态和实例调用）
     */
    public static function msg(string $msg): self
    {
        $instance = new self();
        return $instance->setMsg($msg);
    }

    /**
     * 设置数据（支持静态和实例调用）
     */
    public static function data(array $data): self
    {
        $instance = new self();
        return $instance->setData($data);
    }

    /**
     * 实例方法：设置状态码
     */
    public function setCode(int $code): self
    {
        $this->data['code'] = $code;
        $this->data['msg'] = StatusCodes::getMessage($code);
        return $this;
    }

    /**
     * 实例方法：设置消息
     */
    public function setMsg(string $msg): self
    {
        $this->data['msg'] = $msg;
        return $this;
    }

    /**
     * 实例方法：设置数据
     */
    public function setData(mixed $data): self
    {
        $this->data['data'] = $data;
        return $this;
    }

    /**
     * 设置多个字段 - 使用数组解包
     */
    public function setFields(array $fields): self
    {
        foreach ($fields as $key => $value) {
            if ($key === 'time' || $key === 'times') {
                $this->data[$key] = number_format(microtime(true) - self::$startTime, 10);
                $this->extraFields[$key] = $value;
            } else {
                $this->data[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * 添加额外字段
     */
    public function add(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        $this->extraFields[$key] = true;
        return $this;
    }

    /**
     * 设置字段映射（简化方法名）
     */
    public function map(array $mapping): self
    {
        $this->fieldMapping = $mapping;
        return $this;
    }

    /**
     * 替换字段名（自定义字段替换默认值）
     */
    public function replace(array $replacements): self
    {
        $newData = [];
        foreach ($this->data as $key => $value) {
            $newKey = $replacements[$key] ?? $key;
            $newData[$newKey] = $value;
        }
        $this->data = $newData;
        return $this;
    }

    /**
     * 设置输出格式为JSON
     */
    public function json(): string
    {
        $this->outputFormat = 'json';
        return $this->result();
    }

    /**
     * 设置输出格式为XML
     */
    public function xml(): string
    {
        $this->outputFormat = 'xml';
        return $this->result();
    }

    /**
     * 魔术方法，支持动态字段设置和方法转发
     */
    public function __call(string $name, array $arguments)
    {
        // 处理实例调用时的方法转发
        switch ($name) {
            case 'code':
                return $this->setCode(...$arguments);
            case 'msg':
                return $this->setMsg(...$arguments);
            case 'data':
                return $this->setData(...$arguments);
        }
        
        // 处理动态字段设置
        if (count($arguments) === 1) {
            $this->data[$name] = $arguments[0];
            return $this;
        }
        
        throw new \BadMethodCallException("Method {$name} not found");
    }

    /**
     * 静态魔术方法 - 支持所有实例方法的静态调用
     */
    public static function __callStatic(string $name, array $arguments)
    {
        // 对于已定义的静态方法，直接调用
        if (method_exists(self::class, $name) && (new \ReflectionMethod(self::class, $name))->isStatic()) {
            return self::$name(...$arguments);
        }
        
        $instance = new self();
        
        // 处理实例方法
        if (method_exists($instance, $name)) {
            $result = $instance->$name(...$arguments);
            // 如果返回的是实例本身，继续返回实例以支持链式调用
            return $result === $instance ? $result : $result;
        }
        
        // 处理动态字段设置
        if (count($arguments) === 1) {
            $instance->data[$name] = $arguments[0];
            return $instance;
        }
        
        // 处理无参数的动态字段获取
        if (count($arguments) === 0) {
            // 返回字段值
            return $instance->data[$name] ?? null;
        }
        
        throw new \BadMethodCallException("Static method {$name} not found");
    }

    /**
     * 独立调用方法 - 获取字段值
     */
    public function get(string $field)
    {
        return $this->data[$field] ?? null;
    }

    /**
     * 独立调用方法 - 设置单个字段
     */
    public function set(string $field, $value): self
    {
        $this->data[$field] = $value;
        return $this;
    }

    /**
     * 独立调用方法 - 检查字段是否存在
     */
    public function has(string $field): bool
    {
        return isset($this->data[$field]);
    }

    /**
     * 独立调用方法 - 移除字段
     */
    public function remove(string $field): self
    {
        unset($this->data[$field]);
        return $this;
    }

    /**
     * 获取结果
     */
    public function result()
    {
        // 更新时间字段
        foreach ($this->data as $key => $value) {
            if (($key === 'time' || $key === 'times') && isset($this->extraFields[$key])) {
                $this->data[$key] = number_format(microtime(true) - self::$startTime, 10);
            }
        }

        // 应用字段映射
        $result = $this->data;
        if (!empty($this->fieldMapping)) {
            $mappedResult = [];
            foreach ($result as $key => $value) {
                $newKey = $this->fieldMapping[$key] ?? $key;
                $mappedResult[$newKey] = $value;
            }
            $result = $mappedResult;
        }

        // 根据输出格式返回
        switch ($this->outputFormat) {
            case 'json':
                return json_encode($result, JSON_UNESCAPED_UNICODE);
            case 'xml':
                return $this->arrayToXml($result);
            default:
                return $result;
        }
    }

    /**
     * 设置自定义状态码映射
     */
    public static function setCustomCodes(array $codes): void
    {
        StatusCodes::setCustomCodes($codes);
    }

    /**
     * 获取所有可用状态码
     */
    public static function getAllCodes(): array
    {
        return StatusCodes::getAllCodes();
    }

    /**
     * 检查状态码是否存在
     */
    public static function codeExists(int $code): bool
    {
        return StatusCodes::exists($code);
    }

    /**
     * 数组转XML
     */
    private function arrayToXml(array $data, string $rootElement = 'response'): string
    {
        $xml = new \SimpleXMLElement("<{$rootElement}></{$rootElement}>");
        $this->arrayToXmlRecursive($data, $xml);
        return $xml->asXML();
    }

    /**
     * 递归转换数组到XML
     */
    private function arrayToXmlRecursive(array $data, \SimpleXMLElement $xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                $this->arrayToXmlRecursive($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars((string)$value));
            }
        }
    }
}