<?php

namespace Mofeier\Tools;

/**
 * 常用工具函数类
 * 所有函数名以util_开头
 */
class Utils
{
    /**
     * JSON编码
     */
    public static function util_json_encode($data, int $flags = JSON_UNESCAPED_UNICODE): string|false
    {
        return json_encode($data, $flags);
    }

    /**
     * JSON解码
     */
    public static function util_json_decode(string $json, bool $associative = true, int $depth = 512, int $flags = 0): mixed
    {
        return json_decode($json, $associative, $depth, $flags);
    }

    /**
     * Base64编码
     */
    public static function util_base64_encode($data): string
    {
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        return base64_encode($data);
    }

    /**
     * Base64解码
     */
    public static function util_base64_decode(string $base64, bool $toArray = true): mixed
    {
        $decoded = base64_decode($base64);
        if ($toArray && self::util_is_json($decoded)) {
            return json_decode($decoded, true);
        }
        return $decoded;
    }

    /**
     * URL编码（数组转查询字符串）
     */
    public static function util_url_encode(array $data): string
    {
        return http_build_query($data);
    }

    /**
     * URL解码（查询字符串转数组）
     */
    public static function util_url_decode(string $url): array
    {
        $query = parse_url($url, PHP_URL_QUERY);
        if ($query === null) {
            $query = $url;
        }
        parse_str($query, $result);
        return $result;
    }

    /**
     * 解析URL
     */
    public static function util_parse_url(string $url, int $component = -1): array|string|int|null|false
    {
        return parse_url($url, $component);
    }

    /**
     * 数组合并（深度合并）
     */
    public static function util_array_merge_deep(array ...$arrays): array
    {
        $result = [];
        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                    $result[$key] = self::util_array_merge_deep($result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * 数组去重（多维数组）
     */
    public static function util_array_unique_multi(array $array, string $key = null): array
    {
        if ($key === null) {
            return array_unique($array, SORT_REGULAR);
        }
        
        $temp = [];
        $result = [];
        foreach ($array as $item) {
            if (!in_array($item[$key], $temp)) {
                $temp[] = $item[$key];
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * 数组按指定键排序
     */
    public static function util_array_sort_by_key(array $array, string $key, int $sort = SORT_ASC): array
    {
        $sortArray = [];
        foreach ($array as $item) {
            $sortArray[] = $item[$key] ?? null;
        }
        array_multisort($sortArray, $sort, $array);
        return $array;
    }

    /**
     * 数组分组
     */
    public static function util_array_group_by(array $array, string $key): array
    {
        $result = [];
        foreach ($array as $item) {
            $groupKey = $item[$key] ?? 'undefined';
            $result[$groupKey][] = $item;
        }
        return $result;
    }

    /**
     * 数组提取指定列
     */
    public static function util_array_column_extract(array $array, string $column, string $indexKey = null): array
    {
        return array_column($array, $column, $indexKey);
    }

    /**
     * 生成UUID
     */
    public static function util_generate_uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * 生成随机字符串
     */
    public static function util_generate_random_string(int $length = 10, string $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
    {
        $randomString = '';
        $charactersLength = strlen($characters);
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * 文件大小格式化
     */
    public static function util_format_file_size(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * 时间格式化
     */
    public static function util_format_time_ago(int $timestamp): string
    {
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return $diff . '秒前';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . '分钟前';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . '小时前';
        } elseif ($diff < 2592000) {
            return floor($diff / 86400) . '天前';
        } elseif ($diff < 31536000) {
            return floor($diff / 2592000) . '个月前';
        } else {
            return floor($diff / 31536000) . '年前';
        }
    }

    /**
     * 验证邮箱
     */
    public static function util_validate_email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 验证手机号（中国）
     */
    public static function util_validate_mobile(string $mobile): bool
    {
        return preg_match('/^1[3-9]\d{9}$/', $mobile) === 1;
    }

    /**
     * 验证身份证号（中国）
     */
    public static function util_validate_id_card(string $idCard): bool
    {
        return preg_match('/^[1-9]\d{5}(18|19|20)\d{2}((0[1-9])|(1[0-2]))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/', $idCard) === 1;
    }

    /**
     * 检查是否为JSON字符串
     */
    public static function util_is_json(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * 获取客户端IP
     */
    public static function util_get_client_ip(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * 获取当前毫秒时间戳
     */
    public static function util_get_millisecond(): int
    {
        return (int)(microtime(true) * 1000);
    }

    /**
     * 获取当前微秒时间戳
     */
    public static function util_get_microsecond(): int
    {
        return (int)(microtime(true) * 1000000);
    }

    /**
     * 密码加密
     */
    public static function util_password_hash(string $password, int $algo = PASSWORD_DEFAULT): string
    {
        return password_hash($password, $algo);
    }

    /**
     * 密码验证
     */
    public static function util_password_verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * 安全的随机数生成
     */
    public static function util_secure_random_int(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    /**
     * 安全的随机字节生成
     */
    public static function util_secure_random_bytes(int $length): string
    {
        return random_bytes($length);
    }
}