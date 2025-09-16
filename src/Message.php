<?php

namespace Mofei;

/**
 * 消息体类 统一API返回格式
 * 支持链式调用和静态调用
 * 默认字段：code, msg, time
 * 条件显示字段：data (存在时显示)
 * 可扩展字段和自定义状态码
 * 支持数组键值对批量设置字段
 * 兼容PHP 7.4+
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
     * 全局字段映射
     */
    private static array $globalFieldMapping = [];

    /**
     * 构造函数 - 兼容PHP 7.4+
     */
    public function __construct(array $fields = [])
    {
        if (!isset(self::$startTime)) {
            self::$startTime = microtime(true);
        }
        
        // 初始化默认字段
        $this->data = [
            'code' => 2000,
            'msg' => StatusCodes::getMessage(2000),
            'time' => number_format(microtime(true) - self::$startTime, 10),
            'data' => []
        ];
        
        // 合并额外字段
        if (!empty($fields)) {
            $this->setFields($fields);
        }
    }

    /**
     * 创建实例 - 支持多种参数形式
     */
    public static function create($codeOrFields = [], string $msg = '', $data = null): self
    {
        if (is_array($codeOrFields)) {
            return new self($codeOrFields);
        }
        
        $fields = ['code' => $codeOrFields];
        if (!empty($msg)) {
            $fields['msg'] = $msg;
        }
        if ($data !== null) {
            $fields['data'] = $data;
        }
        
        return new self($fields);
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
        // 自动设置对应的消息
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
     * 设置多个字段
     */
    public function setFields(array $fields): self
    {
        foreach ($fields as $key => $value) {
            if ($key === 'time' || $key === 'times') {
                $this->data[$key] = number_format(microtime(true) - self::$startTime, 10);
                $this->extraFields[$key] = true;
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
     * 设置字段映射
     */
    public function map(array $mapping): self
    {
        $this->fieldMapping = $mapping;
        return $this;
    }

    /**
     * 设置全局字段映射
     */
    public static function setGlobalMapping(array $mapping): void
    {
        self::$globalFieldMapping = $mapping;
    }

    /**
     * 替换字段名（自定义字段替换默认值）
     */
    public function replace(array $replacements): self
    {
        $this->fieldMapping = array_merge($this->fieldMapping, $replacements);
        return $this;
    }

    /**
     * 设置输出格式为JSON
     */
    public function json()
    {
        $this->outputFormat = 'json';
        return $this->result();
    }

    /**
     * 设置输出格式为XML
     */
    public function xml()
    {
        $this->outputFormat = 'xml';
        return $this->result();
    }

    /**
     * 魔术方法，支持动态字段设置和方法转发
     */
    public function __call($name, $arguments)
    {
        // 处理实例调用时的方法转发
        $forwardMethods = ['code', 'msg', 'data', 'setCode', 'setMsg', 'setData'];
        if (in_array($name, $forwardMethods)) {
            return call_user_func_array([$this, $name], $arguments);
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
     * 确保所有方法都能正确支持静态调用
     */
    public static function __callStatic($name, $arguments)
    {
        // 创建一个新的实例
        $instance = new self();
        
        // 处理实例方法调用
        if (method_exists($instance, $name)) {
            $result = call_user_func_array([$instance, $name], $arguments);
            return $result;
        }
        
        // 处理动态字段设置 (单个参数时视为设置字段值)
        if (count($arguments) === 1) {
            $instance->data[$name] = $arguments[0];
            return $instance;
        }
        
        // 方法不存在时抛出异常
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
        if (isset($this->data['time']) || isset($this->data['times'])) {
            $timeKey = isset($this->data['times']) ? 'times' : 'time';
            $this->data[$timeKey] = number_format(microtime(true) - self::$startTime, 10);
        }

        // 准备结果数据
        $result = $this->data;
        
        // 条件显示data字段（只有当值不为null时才显示）
        if (isset($result['data']) && $result['data'] === null) {
            unset($result['data']);
        }

        // 应用全局字段映射和实例字段映射
        $allMappings = array_merge(self::$globalFieldMapping, $this->fieldMapping);
        if (!empty($allMappings)) {
            $mappedResult = [];
            foreach ($result as $key => $value) {
                $newKey = $allMappings[$key] ?? $key;
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
