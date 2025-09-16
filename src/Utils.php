<?php

namespace Mofei;

/**
 * 工具类
 * 提供各种实用工具方法
 */
class Utils
{
    /**
     * 构建许可证密钥
     * @param string $prefix 前缀
     * @return string 许可证密钥
     */
    public static function build_license(string $prefix = ''): string
    {
        // 基于当前毫秒时间戳和随机数生成种子
        $seed = microtime(true) * 1000 . random_int(1000, 9999);
        
        // 使用SHA-256哈希算法生成哈希值
        $hash = hash('sha256', $seed);
        
        // 截取哈希值的前16个字符
        $hashPart = substr($hash, 0, 16);
        
        // 生成8个随机字符
        $randomPart = self::generateSecureRandom(8);
        
        // 合并哈希部分和随机部分
        $combined = $hashPart . $randomPart;
        
        // 插入连字符，格式为XXXX-XXXX-XXXX-XXXX
        $license = '';
        for ($i = 0; $i < strlen($combined); $i += 4) {
            $license .= substr($combined, $i, 4);
            if ($i < strlen($combined) - 4) {
                $license .= '-';
            }
        }
        
        // 如果有前缀，则添加前缀
        if (!empty($prefix)) {
            $license = $prefix . '-' . $license;
        }
        
        return strtoupper($license);
    }
    
    /**
     * 字符串转换函数 - 转整数
     */
    public static function str_to_int(string $string, int $default = 0): int
    {
        return intval($string) === 0 && $string !== '0' ? $default : intval($string);
    }
    
    /**
     * 字符串转换函数 - 转浮点数
     */
    public static function str_to_float(string $string, float $default = 0.0): float
    {
        return floatval($string) === 0.0 && $string !== '0' && $string !== '0.0' ? $default : floatval($string);
    }
    
    /**
     * 字符串转换函数 - 转布尔值
     */
    public static function str_to_bool(string $string, bool $default = false): bool
    {
        $lower = strtolower($string);
        $truthy = ['true', 'yes', '1', 'on', 'y', 't'];
        $falsy = ['false', 'no', '0', 'off', 'n', 'f'];
        
        if (in_array($lower, $truthy)) {
            return true;
        }
        if (in_array($lower, $falsy)) {
            return false;
        }
        return $default;
    }
    
    /**
     * 驼峰命名转下划线命名
     */
    public static function str_camel_to_snake(string $string): string
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $string)), '_');
    }
    
    /**
     * to_snake_case方法 - 作为str_camel_to_snake的别名，便于Facade调用
     */
    public static function to_snake_case(string $string): string
    {
        return self::str_camel_to_snake($string);
    }
    
    /**
     * 下划线命名转驼峰命名
     */
    public static function str_snake_to_camel(string $string, bool $capitalizeFirst = false): string
    {
        $string = str_replace('_', '', ucwords($string, '_'));
        if (!$capitalizeFirst) {
            $string = lcfirst($string);
        }
        return $string;
    }
    
    /**
     * to_camel_case方法 - 作为str_snake_to_camel的别名，便于Facade调用
     */
    public static function to_camel_case(string $string, bool $capitalizeFirst = false): string
    {
        return self::str_snake_to_camel($string, $capitalizeFirst);
    }
    
    /**
     * 生成UUID
     */
    public static function util_generate_uuid(): string
    {
        if (function_exists('uuid_create') && function_exists('uuid_is_valid')) {
            $uuid = uuid_create(UUID_TYPE_RANDOM);
            return $uuid;
        } else {
            // 兼容方式生成UUID
            return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }
    }
    
    /**
     * 验证邮箱格式
     */
    public static function util_validate_email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * 验证手机号格式
     */
    public static function util_validate_mobile(string $mobile): bool
    {
        // 中国大陆手机号验证规则
        return preg_match('/^1[3-9]\d{9}$/', $mobile) === 1;
    }
    
    /**
     * 安全随机数生成（工具方法封装）
     */
    public static function util_secure_random(int $length = 32, bool $urlSafe = false): string
    {
        $random = self::generateSecureRandom($length);
        if ($urlSafe) {
            return rtrim(strtr($random, '+/', '-_'), '=');
        }
        return $random;
    }
    
    /**
     * 安全字符串比较（工具方法封装）
     */
    public static function util_secure_compare(string $knownString, string $userString): bool
    {
        return self::secureCompare($knownString, $userString);
    }

    /**
     * 一维数组转树形结构（非递归实现）
     * @param array $data 一维数组
     * @param string $id 主键字段
     * @param string $pid 父级ID字段
     * @param string $children 子节点字段名
     * @return array 树形结构
     */
    public static function array_to_tree(array $data, string $id = 'id', string $pid = 'parent_id', string $children = 'children'): array
    {
        $items = [];
        $tree = [];
        
        // 将数组转换为以ID为键的关联数组
        foreach ($data as $item) {
            $items[$item[$id]] = $item;
        }
        
        // 构建树形结构
        foreach ($items as $key => $item) {
            if (isset($items[$item[$pid]])) {
                $items[$item[$pid]][$children][] = &$items[$key];
            } else {
                $tree[] = &$items[$key];
            }
        }
        
        return $tree;
    }

    /**
     * 树形结构转一维数组（非递归实现）
     * @param array $tree 树形结构
     * @param string $children 子节点字段名
     * @param bool $preserveKeys 是否保留原始键
     * @return array 一维数组
     */
    public static function tree_to_array(array $tree, string $children = 'children', bool $preserveKeys = false): array
    {
        $result = [];
        $stack = $tree;
        
        while (!empty($stack)) {
            $node = array_shift($stack);
            $childrenNodes = $node[$children] ?? [];
            unset($node[$children]);
            
            if ($preserveKeys) {
                $result[] = $node;
            } else {
                $result[] = $node;
            }
            
            // 将子节点压入栈顶
            array_splice($stack, 0, 0, $childrenNodes);
        }
        
        return $result;
    }

    /**
     * 安全的JSON编码（带错误处理）
     * @param mixed $data 要编码的数据
     * @param int $options JSON选项
     * @param int $depth 最大深度
     * @return string|false JSON字符串或false
     * @throws \Exception
     */
    public static function util_json_encode($data, int $options = JSON_UNESCAPED_UNICODE, int $depth = 512): string
    {
        $json = json_encode($data, $options, $depth);
        
        if ($json === false) {
            $errorMsg = json_last_error_msg();
            throw new \Exception("JSON encoding failed: {$errorMsg}");
        }
        
        return $json;
    }

    /**
     * 安全的JSON解码（带错误处理）
     * @param string $json JSON字符串
     * @param bool $assoc 是否返回关联数组
     * @param int $depth 最大深度
     * @param int $options JSON选项
     * @return mixed 解码后的数据
     * @throws \Exception
     */
    public static function util_json_decode(string $json, bool $assoc = true, int $depth = 512, int $options = 0)
    {
        $data = json_decode($json, $assoc, $depth, $options);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMsg = json_last_error_msg();
            throw new \Exception("JSON decoding failed: {$errorMsg}");
        }
        
        return $data;
    }

    /**
     * 计算字符串长度（支持多字节）
     * @param string $string 输入字符串
     * @param string $encoding 字符编码
     * @return int
     */
    public static function str_length(string $string, string $encoding = 'UTF-8'): int
    {
        return mb_strlen($string, $encoding);
    }

    /**
     * 截取字符串（支持多字节）
     * @param string $string 输入字符串
     * @param int $start 起始位置
     * @param int|null $length 截取长度
     * @param string $encoding 字符编码
     * @return string
     */
    public static function str_substring(string $string, int $start, ?int $length = null, string $encoding = 'UTF-8'): string
    {
        return mb_substr($string, $start, $length, $encoding);
    }

    /**
     * 转换字符串大小写
     * @param string $string 输入字符串
     * @param int $case 转换模式
     * @param string $encoding 字符编码
     * @return string
     */
    public static function str_case(string $string, int $case = MB_CASE_LOWER, string $encoding = 'UTF-8'): string
    {
        return mb_convert_case($string, $case, $encoding);
    }

    /**
     * 查找字符串首次出现的位置（支持多字节）
     * @param string $haystack 主字符串
     * @param string $needle 子字符串
     * @param int $offset 起始位置
     * @param string $encoding 字符编码
     * @return int|false 位置索引或false
     */
    public static function str_pos(string $haystack, string $needle, int $offset = 0, string $encoding = 'UTF-8')
    {
        return mb_strpos($haystack, $needle, $offset, $encoding);
    }

    /**
     * 查找字符串最后出现的位置（支持多字节）
     * @param string $haystack 主字符串
     * @param string $needle 子字符串
     * @param int $offset 起始位置
     * @param string $encoding 字符编码
     * @return int|false 位置索引或false
     */
    public static function str_last_pos(string $haystack, string $needle, int $offset = 0, string $encoding = 'UTF-8')
    {
        return mb_strrpos($haystack, $needle, $offset, $encoding);
    }

    /**
     * PHP 7.4兼容的str_starts_with函数
     * @param string $haystack 主字符串
     * @param string $needle 子字符串
     * @return bool
     */
    public static function str_starts_with(string $haystack, string $needle): bool
    {
        if (function_exists('str_starts_with')) {
            return str_starts_with($haystack, $needle);
        }
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }

    /**
     * PHP 7.4兼容的str_ends_with函数
     * @param string $haystack 主字符串
     * @param string $needle 子字符串
     * @return bool
     */
    public static function str_ends_with(string $haystack, string $needle): bool
    {
        if (function_exists('str_ends_with')) {
            return str_ends_with($haystack, $needle);
        }
        if ($needle === '') {
            return true;
        }
        $len = strlen($needle);
        return $len <= strlen($haystack) && substr_compare($haystack, $needle, -$len) === 0;
    }

    /**
     * PHP 7.4兼容的str_contains函数
     * @param string $haystack 主字符串
     * @param string $needle 子字符串
     * @return bool
     */
    public static function str_contains(string $haystack, string $needle): bool
    {
        if (function_exists('str_contains')) {
            return str_contains($haystack, $needle);
        }
        return $needle === '' || strpos($haystack, $needle) !== false;
    }

    /**
     * 生成安全随机数
     * @param int $length 随机数长度
     * @return string 安全随机数
     */
    public static function generateSecureRandom(int $length = 32): string
    {
        if (extension_loaded('sodium')) {
            return bin2hex(random_bytes($length));
        } elseif (extension_loaded('openssl')) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        } else {
            throw new \RuntimeException('No available random generator found');
        }
    }

    /**
     * 安全比较两个字符串（防止时序攻击）
     * @param string $knownString 已知字符串
     * @param string $userString 用户输入字符串
     * @return bool 是否相等
     */
    public static function secureCompare(string $knownString, string $userString): bool
    {
        return hash_equals($knownString, $userString);
    }

    /**
     * 将数组转换为base64字符串
     * @param array $data 要转换的数组
     * @return string base64字符串
     * @throws \Exception
     */
    public static function util_base64_encode(array $data): string
    {
        $json = self::util_json_encode($data);
        return base64_encode($json);
    }

    /**
     * 将base64字符串转换为数组
     * @param string $base64 base64字符串
     * @return array 转换后的数组
     * @throws \Exception
     */
    public static function util_base64_decode(string $base64): array
    {
        $json = base64_decode($base64);
        if ($json === false) {
            throw new \Exception('Invalid base64 string');
        }
        return self::util_json_decode($json);
    }

    /**
     * URL安全的加密方法
     * @param string $data 要加密的数据
     * @param string|null $key 加密密钥
     * @return string 加密后的字符串
     */
    public static function util_encrypt_url(string $data, ?string $key = null): string
    {
        return Security::encryptForUrl($data, $key);
    }

    /**
     * URL安全的解密方法
     * @param string $encrypted 加密后的字符串
     * @param string|null $key 解密密钥
     * @return string 解密后的数据
     * @throws \Exception
     */
    public static function util_decrypt_url(string $encrypted, ?string $key = null): string
    {
        return Security::decryptFromUrl($encrypted, $key);
    }

    /**
     * Token加密方法
     * @param string $data 要加密的数据
     * @param string|null $key 加密密钥
     * @param int $expiry 过期时间（秒），0表示不过期
     * @param int|null $currentTime 用于测试的当前时间（可选）
     * @return string 加密后的Token
     */
    public static function util_encrypt_token(string $data, ?string $key = null, int $expiry = 0, ?int $currentTime = null): string
    {
        return Security::encryptForToken($data, $key, $expiry, $currentTime);
    }

    /**
     * Token解密方法
     * @param string $encrypted 加密后的Token
     * @param string|null $key 解密密钥
     * @return string 解密后的数据
     * @throws \Exception
     */
    public static function util_decrypt_token(string $encrypted, ?string $key = null): string
    {
        return Security::decryptFromToken($encrypted, $key);
    }

    /**
     * 将数组转换为URL查询字符串
     * @param array $data 要转换的数组
     * @return string URL查询字符串
     */
    public static function util_url_encode(array $data): string
    {
        return http_build_query($data);
    }

    /**
     * 将URL查询字符串转换为数组
     * @param string $url URL查询字符串
     * @return array 转换后的数组
     */
    public static function util_url_decode(string $url): array
    {
        parse_str($url, $result);
        return $result;
    }

    /**
     * 解析URL并返回指定部分
     * @param string $url 要解析的URL
     * @param int $component 要返回的URL部分
     * @return mixed 解析后的URL部分
     */
    public static function util_parse_url(string $url, int $component = -1)
    {
        return parse_url($url, $component);
    }

    /**
     * 数组深度合并
     * @param array $array1 第一个数组
     * @param array $array2 第二个数组
     * @return array 合并后的数组
     */
    public static function util_array_merge_recursive(array $array1, array $array2): array
    {
        foreach ($array2 as $key => $value) {
            if (isset($array1[$key]) && is_array($array1[$key]) && is_array($value)) {
                $array1[$key] = self::util_array_merge_recursive($array1[$key], $value);
            } else {
                $array1[$key] = $value;
            }
        }
        return $array1;
    }

    /**
     * 从数组中获取指定键的值，如果不存在则返回默认值
     * @param array $array 源数组
     * @param string|int $key 要获取的键
     * @param mixed $default 默认值
     * @return mixed 获取的值或默认值
     */
    public static function util_array_get(array $array, $key, $default = null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }
        return $default;
    }

    /**
     * 检查数组中是否存在指定的键
     * @param array $array 源数组
     * @param array $keys 要检查的键数组
     * @return bool 是否全部存在
     */
    public static function util_array_has_keys(array $array, array $keys): bool
    {
        foreach ($keys as $key) {
            if (!isset($array[$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * 从数组中移除指定的键
     * @param array $array 源数组
     * @param array $keys 要移除的键数组
     * @return array 处理后的数组
     */
    public static function util_array_remove_keys(array $array, array $keys): array
    {
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                unset($array[$key]);
            }
        }
        return $array;
    }

    /**
     * 过滤数组中的空值
     * @param array $array 源数组
     * @return array 过滤后的数组
     */
    public static function util_array_filter_empty(array $array): array
    {
        return array_filter($array, function($value) {
            return $value !== null && $value !== false && $value !== '' && $value !== [];
        });
    }
}
