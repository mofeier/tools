<?php

namespace mofei;

/**
 * 常用工具函数类
 * 所有函数名以util_开头
 * 使用PHP8.1+特性优化
 */
class Utils
{
    /**
     * JSON编码
     */
    public static function util_json_encode(mixed $data, int $flags = JSON_UNESCAPED_UNICODE): string|false
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
    public static function util_base64_encode(mixed $data): string
    {
        if (is_array($data) || is_object($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        return base64_encode((string)$data);
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
     * URL安全加密 - 适用于URL参数
     * @param string $data 要加密的数据
     * @param string|null $key 加密密钥
     * @return string URL安全的加密字符串
     */
    public static function util_encrypt_url(string $data, ?string $key = null): string
    {
        return SecureCrypto::encryptForUrl($data, $key);
    }

    /**
     * URL安全解密
     * @param string $encrypted 加密的数据
     * @param string|null $key 解密密钥
     * @return string 解密后的字符串
     */
    public static function util_decrypt_url(string $encrypted, ?string $key = null): string
    {
        return SecureCrypto::decryptFromUrl($encrypted, $key);
    }

    /**
     * Token加密 - 适用于JWT等场景
     * @param string $data 要加密的数据
     * @param string|null $key 加密密钥
     * @param int $expiry 过期时间（秒），0表示不过期
     * @return string Token格式的加密字符串
     */
    public static function util_encrypt_token(string $data, ?string $key = null, int $expiry = 0): string
    {
        return SecureCrypto::encryptForToken($data, $key, $expiry);
    }

    /**
     * Token解密
     * @param string $encrypted 加密的Token
     * @param string|null $key 解密密钥
     * @return string 解密后的字符串
     * @throws \Exception 当Token过期时抛出异常
     */
    public static function util_decrypt_token(string $encrypted, ?string $key = null): string
    {
        return SecureCrypto::decryptFromToken($encrypted, $key);
    }

    /**
     * 生成安全随机字符串
     * @param int $length 长度
     * @param bool $urlSafe 是否URL安全
     * @return string 随机字符串
     */
    public static function util_secure_random(int $length = 32, bool $urlSafe = false): string
    {
        return SecureCrypto::generateSecureRandom($length, $urlSafe);
    }

    /**
     * 安全字符串比较（防时序攻击）
     * @param string $known 已知字符串
     * @param string $user 用户输入字符串
     * @return bool 比较结果
     */
    public static function util_secure_compare(string $known, string $user): bool
    {
        return SecureCrypto::secureCompare($known, $user);
    }

    /**
     * 生成随机盐值
     */
    public static function util_generate_salt(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
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
     * 使用 PHP 8 特性生成随机码：可数字，大小写字母混合
     * 
     * @param int $length 验证码长度，范围 4-12
     * @param bool $includeLetters 是否包含字母
     * @param bool $useUppercase 是否包含大写字母（仅在包含字母时有效）
     * @return string 生成的随机验证码
     */
    public static function util_generate_random_code( int $length = 6, bool $includeLetters = false, bool $useUppercase = false): string 
    {
        // 验证参数合法性
        if ($length < 4 || $length > 12) {
            // throw new \InvalidArgumentException("验证码长度必须在 4-12 之间");
            return  "验证码长度必须在 4-12 之间";
        }
        
        // 定义基础字符集
        $charSets = [
            'numeric' => '0123456789',
            'lowercase' => 'abcdefghijklmnopqrstuvwxyz',
            'uppercase' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        ];
        
        // 构建可用字符集
        $availableChars = $charSets['numeric'];
        if ($includeLetters) {
            $availableChars .= $charSets['lowercase'];
            if ($useUppercase) {
                $availableChars .= $charSets['uppercase'];
            }
        }
        // 使用箭头函数和数组填充生成验证码
        return implode('', array_map(
            fn() => $availableChars[random_int(0, strlen($availableChars) - 1)],
            array_fill(0, $length, null)
        ));
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

    /**
     * 生成唯一订单号
     * @param string $prefix 订单号前缀，默认为 ''
     * @param int $length 订单号总长度（包括前缀），默认为 12
     * @param bool $separator 是否使用连接符，默认为 ''
     * @return string 订单号
     */
    public static function build_order_sn(string $prefix = '', string $separator = '', int $length = 12): string {
        // 验证长度（不包括前缀、日期和连接符，建议至少8位以保证唯一性）
        $length = max(8, min($length, 20));
        
        // 获取当前日期 (YYYYMMDD)
        $dateStr = date('YmdHis');
        
        // 生成唯一标识符（基于微秒时间戳和随机数）
        $microTime = (string) (int) (microtime(true) * 1_000_000); // PHP 8 更精确的微秒处理
        $randomBytes = random_bytes(4); // PHP 8 推荐的强随机数生成
        $randomPart = sprintf('%04d', unpack('N', $randomBytes)[1] % 10_000);
        
        // 使用 uniqid 结合微秒时间戳增强唯一性
        $uniquePart = substr(hash('sha256', $microTime . $randomPart), 0, 16);
        
        // 组合并使用 Base36 编码
        $rawId = $uniquePart . $randomPart;
        $uniqueId = strtoupper(base_convert($rawId, 16, 36));
        
        // 截取到指定长度
        $uniqueId = substr($uniqueId, 0, $length);
        
        // 返回格式化的订单号
        return "{$prefix}{$separator}{$dateStr}{$separator}{$uniqueId}";
    }
    /**
     * 生成推荐码字母数字，默认6,长度可定
     * @param int $length 推荐码总长度（包括前缀），默认为 6
     * @return string 推荐码
     */
    public static function build_redcode(int $length = 6): string {
        // 验证长度（建议至少6位以保证唯一性）
        $length = max(6, min($length, 12));
        
        // 获取微秒时间戳增强唯一性
        $microTime = (string) (int) (microtime(true) * 1_000_000);
        
        // 生成随机字节
        $randomBytes = random_bytes(8); // PHP 8 强随机数生成
        $randomPart = substr(hash('sha256', $randomBytes . $microTime), 0, 16);
        
        // 使用 Base36 编码（生成英文大写字母+数字）
        $code = strtoupper(base_convert($randomPart, 16, 36));
        
        // 截取到指定长度
        $code = substr($code, 0, $length);
        
        return $code;
    }

    /**
     * 验证中国车牌号（包括燃油车、新能源车及特殊车牌）
     * 
     * @param string $plateNumber 待验证的车牌号
     * @return bool|string 验证成功返回 true，失败返回错误信息
     */
    public static function validate_car_no(string $plateNumber): bool|string
    {
        // 去除空格和分隔符
        $plateNumber = preg_replace('/\s|-|\./', '', $plateNumber);
        $length = strlen($plateNumber);
        
        // 省份简称列表（34个省级行政区）
        $provinces = [
            '京', '津', '沪', '渝', '冀', '晋', '辽', '吉', '黑', '苏', 
            '浙', '皖', '闽', '赣', '鲁', '豫', '鄂', '湘', '粤', '琼', 
            '川', '贵', '云', '陕', '甘', '青', '藏', '桂', '宁', '新', 
            '蒙', '港', '澳'
        ];
        
        // 特殊车牌前缀列表
        $specialPrefixes = [
            '使',  // 使馆车牌
            '领',  // 领事馆车牌
            '警',  // 警车
            '学',  // 教练车
            '挂',  // 挂车
            '港',  // 港澳入出境车
            '澳',  // 港澳入出境车
        ];
        
        // 军警车牌前缀（2位字母）
        $militaryPrefixes = [
            'VA', 'VB', 'VC', 'VD', 'VE', 'VF', 'VG', 'VH', 'VK', 'VM', 'VO',
            'WA', 'WB', 'WC', 'WD', 'WE', 'WF', 'WG', 'WH', 'WK', 'WL', 'WM', 'WN', 'WO',
            'XA', 'XB', 'XC', 'XD', 'XE', 'XF', 'XG', 'XH', 'XK', 'XL', 'XM', 'XN', 'XO',
            'YA', 'YB', 'YC', 'YD', 'YE', 'YF', 'YG', 'YH', 'YK', 'YL', 'YM', 'YN', 'YO',
            'ZA', 'ZB', 'ZC', 'ZD', 'ZE', 'ZF', 'ZG', 'ZH', 'ZK', 'ZL', 'ZM', 'ZN', 'ZO',
        ];
        
        // 验证普通民用车牌（传统燃油车和新能源车）
        if (in_array(mb_substr($plateNumber, 0, 1, 'UTF-8'), $provinces)) {
            // 传统燃油车牌（7位）
            if ($length === 7) {
                $province = mb_substr($plateNumber, 0, 1, 'UTF-8');
                $cityCode = mb_substr($plateNumber, 1, 1, 'UTF-8');
                $numberPart = mb_substr($plateNumber, 2, null, 'UTF-8');
                
                if (!in_array($province, $provinces)) {
                    return "省份简称错误";
                }
                
                if (!preg_match('/^[A-HJ-NP-Z]$/', $cityCode)) {
                    return "地级市代码错误（不含I、O）";
                }
                
                if (!preg_match('/^[0-9A-HJ-NP-Z]{5}$/', $numberPart)) {
                    return "传统车牌号码部分包含非法字符";
                }
                
                return true;
            }
            
            // 新能源车牌（8位）
            elseif ($length === 8) {
                $province = mb_substr($plateNumber, 0, 1, 'UTF-8');
                $cityCode = mb_substr($plateNumber, 1, 1, 'UTF-8');
                $numberPart = mb_substr($plateNumber, 2, null, 'UTF-8');
                $energyType = mb_substr($numberPart, 0, 1, 'UTF-8');
                if (!in_array($province, $provinces)) {
                    return "省份简称错误";
                }
                if (!preg_match('/^[A-HJ-NP-Z]$/', $cityCode)) {
                    return "地级市代码错误（不含I、O）";
                }
                if (!in_array($energyType, ['D', 'F'])) {
                    return "新能源车牌第3位必须为D（纯电）或F（非纯电）";
                }
                if (!preg_match('/^[0-9A-HJ-NP-Z]{5}$/', mb_substr($numberPart, 1, null, 'UTF-8'))) {
                    return "新能源车牌号码部分包含非法字符";
                }
                return true;
            }
        }
        
        // 验证使馆/领事馆车牌（如：使12345、沪领1234）
        elseif (mb_substr($plateNumber, 0, 1, 'UTF-8') === '使') {
            if ($length !== 6) {
                return "使馆车牌长度应为6位";
            }
            
            if (!preg_match('/^使[0-9]{5}$/', $plateNumber)) {
                return "使馆车牌格式应为：使+5位数字";
            }
            
            return true;
        }
        elseif ((mb_substr($plateNumber, 0, 2, 'UTF-8') === '港澳' && mb_substr($plateNumber, 2, 1, 'UTF-8') === '入') || 
                (in_array(mb_substr($plateNumber, 0, 1, 'UTF-8'), ['港', '澳']) && 
                in_array(mb_substr($plateNumber, -1, 1, 'UTF-8'), ['港', '澳']))) {
            // 港澳入出境车牌（如：港澳入粤Z1234港）
            return true;
        }
        elseif (mb_substr($plateNumber, -1, 1, 'UTF-8') === '警') {
            // 警车车牌（如：京A1234警）
            if ($length !== 7) {
                return "警车车牌长度应为7位";
            }
            
            if (!in_array(mb_substr($plateNumber, 0, 1, 'UTF-8'), $provinces)) {
                return "警车车牌省份简称错误";
            }
            
            if (!preg_match('/^[A-HJ-NP-Z]$/', mb_substr($plateNumber, 1, 1, 'UTF-8'))) {
                return "警车车牌地级市代码错误";
            }
            
            if (!preg_match('/^[0-9A-HJ-NP-Z]{4}警$/', mb_substr($plateNumber, 1, null, 'UTF-8'))) {
                return "警车车牌格式错误";
            }
            
            return true;
        }
        elseif (mb_substr($plateNumber, -1, 1, 'UTF-8') === '学') {
            // 教练车车牌（如：京A1234学）
            if ($length !== 7) {
                return "教练车车牌长度应为7位";
            }
            
            if (!in_array(mb_substr($plateNumber, 0, 1, 'UTF-8'), $provinces)) {
                return "教练车车牌省份简称错误";
            }
            
            if (!preg_match('/^[A-HJ-NP-Z]$/', mb_substr($plateNumber, 1, 1, 'UTF-8'))) {
                return "教练车车牌地级市代码错误";
            }
            
            if (!preg_match('/^[0-9A-HJ-NP-Z]{4}学$/', mb_substr($plateNumber, 1, null, 'UTF-8'))) {
                return "教练车车牌格式错误";
            }
            
            return true;
        }
        elseif (mb_substr($plateNumber, -1, 1, 'UTF-8') === '挂') {
            // 挂车车牌（如：京A1234挂）
            if ($length !== 7) {
                return "挂车车牌长度应为7位";
            }
            
            if (!in_array(mb_substr($plateNumber, 0, 1, 'UTF-8'), $provinces)) {
                return "挂车车牌省份简称错误";
            }
            
            if (!preg_match('/^[A-HJ-NP-Z]$/', mb_substr($plateNumber, 1, 1, 'UTF-8'))) {
                return "挂车车牌地级市代码错误";
            }
            
            if (!preg_match('/^[0-9A-HJ-NP-Z]{4}挂$/', mb_substr($plateNumber, 1, null, 'UTF-8'))) {
                return "挂车车牌格式错误";
            }
            
            return true;
        }
        // 验证军用车牌（如：VA12345）
        elseif (in_array(mb_substr($plateNumber, 0, 2, 'UTF-8'), $militaryPrefixes)) {
            if ($length !== 7) {
                return "军用车牌长度应为7位";
            }
            
            if (!preg_match('/^[A-Z]{2}[0-9A-Z]{5}$/', $plateNumber)) {
                return "军用车牌格式错误";
            }
            
            return true;
        }
        
        return "未知格式的车牌号码";
    }
    /**
     * 高随机性数字ID生成器（支持动态位数增长）
     * @param int $minLength 最小位数，默认8位
     * @param int $maxLength 最大位数，默认16位
     * @param bool $autoIncrement 是否根据时间自动增加位数
     * @return string 生成的随机数字字符串
     */
    function build_number(int $minLength = 8, int $maxLength = 16, bool $autoIncrement = true): string {
        // 参数验证
        if ($minLength < 6) $minLength = 6;
        if ($maxLength > 32) $maxLength = 32;
        if ($minLength > $maxLength) $minLength = $maxLength;
        // 动态计算长度
        $length = $minLength;
        if ($autoIncrement) {
            $daysSince2020 = (time() - strtotime('2025-06-06')) / (3600 * 24);
            $growthFactor = min(floor($daysSince2020 / 180), $maxLength - $minLength);
            $length += $growthFactor;
        }
        // 时间分量（提供基础唯一性和时序性）
        $timeComponent = (string)time();
        $timeLength = strlen($timeComponent);
        // 随机分量（提供不可预测性）
        $randomComponent = '';
        $remainingLength = $length;
        
        // 从时间戳中提取并混淆
        if ($timeLength >= 4 && $remainingLength > 0) {
            $timeOffset = random_int(0, max(0, $timeLength - 4));
            $timeFragment = substr($timeComponent, $timeOffset, min(4, $remainingLength));
            $randomComponent .= str_shuffle($timeFragment);
            $remainingLength -= strlen($timeFragment);
        }
        // 补充加密随机数
        if ($remainingLength > 0) {
            $randomStr = '';
            for ($i = 0; $i < $remainingLength; $i++) {
                // 使用加密安全随机数生成0-9的数字
                $randomStr .= (string)random_int(0, 9);
            }
            $randomComponent .= $randomStr;
        }
        // 最终混淆（确保没有可识别的模式）
        $finalNumber = str_shuffle($randomComponent);
        
        // 确保首位非零
        if ($finalNumber[0] === '0') {
            $nonZeroPos = strpbrk($finalNumber, '123456789');
            if ($nonZeroPos !== false) {
                $nonZeroIndex = strpos($finalNumber, $nonZeroPos);
                $finalNumber[0] = $finalNumber[$nonZeroIndex];
                $finalNumber[$nonZeroIndex] = '0';
            }
        }
        return $finalNumber;
    }
    /**
     * Generates a robust, unique license key using a millisecond timestamp.
     * Format: XXXX-XXXX-XXXX-XXXX (16 chars, 4 groups of 4 chars)
     * @throws Exception if cryptographic functions fail
     * @return string License key
     */
    public function build_license(): string {
        // Character set: A-Z, 0-9 (36 chars, safe for readability)
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charLength = strlen($chars);

        try {
            // Get millisecond timestamp and ensure uniqueness with counter
            static $counter = 0; // Static counter for same-millisecond calls
            $timestamp = (string)(int)(microtime(true) * 1000); // e.g., 1635781234567
            $counter++; //  // Increment for concurrent calls

            // Generate 8 chars from timestamp + counter
            $seed = $timestamp . $counter;
            $hash = substr(hash('sha256', $seed), 0, 8); // 8-char SHA-256 hash
            $hashChars = '';
            foreach (str_split($hash, 2) as $pair) {
                $index = hexdec($pair) % $charLength; // Map hex to char index
                $hashChars .= $chars[$index];
            }

            // Generate 7 random chars
            $random = '';
            for ($i = 0; $i < 7; $i++) {
                $random .= $chars[random_int(0, $charLength - 1)];
            }

            // Combine hash (8) + random (7) = 15 chars
            $rawKey = strtoupper($hashChars . $random);

            // Generate check digit using SHA-256
            $checkSum = hexdec(substr(hash('sha256', $rawKey), 0, 4));
            $checkDigit = $chars[$checkSum % $charLength];

            // Format: XXXX-XXXX-XXXX-XXXX
            return substr($rawKey, 0, 4) . '-' .
                   substr($rawKey, 4, 4) . '-' .
                   substr($rawKey, 8, 4) . '-' .
                   substr($rawKey, 12, 3) . $checkDigit;
        } catch (\Exception $e) {
            throw new \Exception('Failed to generate license key: ' . $e->getMessage());
        }
    }
}