<?php

namespace Mofeier\Tools;

/**
 * 字符数字互转类型函数类
 * 
 */
class StringConverter
{
    /**
     * 字符串转整数
     */
    public static function str_to_int(string $str): int
    {
        return (int) $str;
    }

    /**
     * 字符串转浮点数
     */
    public static function str_to_float(string $str): float
    {
        return (float) $str;
    }

    /**
     * 字符串转布尔值
     */
    public static function str_to_bool(string $str): bool
    {
        return filter_var($str, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * 数字转字符串
     */
    public static function str_from_number($number): string
    {
        return (string) $number;
    }

    /**
     * 一维数组转树形结构（非递归实现）
     */
    public static function array_to_tree(array $items, string $idKey = 'id', string $parentKey = 'parent_id', string $childrenKey = 'children'): array
    {
        $tree = [];
        $indexed = [];
        
        // 第一步：建立索引
        foreach ($items as $item) {
            $indexed[$item[$idKey]] = $item;
            $indexed[$item[$idKey]][$childrenKey] = [];
        }
        
        // 第二步：构建树形结构
        foreach ($indexed as $id => $item) {
            if (isset($item[$parentKey]) && $item[$parentKey] && isset($indexed[$item[$parentKey]])) {
                $indexed[$item[$parentKey]][$childrenKey][] = &$indexed[$id];
            } else {
                $tree[] = &$indexed[$id];
            }
        }
        
        return $tree;
    }

    /**
     * 树形结构转一维数组（非递归实现）
     */
    public static function tree_to_array(array $tree, string $childrenKey = 'children'): array
    {
        $result = [];
        $stack = $tree;
        
        while (!empty($stack)) {
            $node = array_shift($stack);
            $children = $node[$childrenKey] ?? [];
            unset($node[$childrenKey]);
            
            $result[] = $node;
            
            if (!empty($children)) {
                $stack = array_merge($children, $stack);
            }
        }
        
        return $result;
    }

    /**
     * 字符串转数组（按分隔符）
     */
    public static function str_to_array(string $str, string $delimiter = ','): array
    {
        if (empty($str)) {
            return [];
        }
        return array_map('trim', explode($delimiter, $str));
    }

    /**
     * 数组转字符串（按分隔符）
     */
    public static function str_from_array(array $array, string $delimiter = ','): string
    {
        return implode($delimiter, $array);
    }

    /**
     * 驼峰转下划线
     */
    public static function str_camel_to_snake(string $str): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $str));
    }

    /**
     * 下划线转驼峰
     */
    public static function str_snake_to_camel(string $str, bool $capitalizeFirst = false): string
    {
        $str = str_replace('_', '', ucwords($str, '_'));
        return $capitalizeFirst ? $str : lcfirst($str);
    }

    /**
     * 字符串转十六进制
     */
    public static function str_to_hex(string $str): string
    {
        return bin2hex($str);
    }

    /**
     * 十六进制转字符串
     */
    public static function str_from_hex(string $hex): string
    {
        return hex2bin($hex);
    }

    /**
     * 字符串转二进制
     */
    public static function str_to_binary(string $str): string
    {
        $binary = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $binary .= sprintf('%08b', ord($str[$i]));
        }
        return $binary;
    }

    /**
     * 二进制转字符串
     */
    public static function str_from_binary(string $binary): string
    {
        $str = '';
        for ($i = 0; $i < strlen($binary); $i += 8) {
            $byte = substr($binary, $i, 8);
            $str .= chr(bindec($byte));
        }
        return $str;
    }

    /**
     * 字符串反转
     */
    public static function str_reverse(string $str): string
    {
        return strrev($str);
    }

    /**
     * 字符串重复
     */
    public static function str_repeat(string $str, int $times): string
    {
        return str_repeat($str, $times);
    }

    /**
     * 字符串填充
     */
    public static function str_pad(string $str, int $length, string $padString = ' ', int $padType = STR_PAD_RIGHT): string
    {
        return str_pad($str, $length, $padString, $padType);
    }

    /**
     * 字符串截取（支持中文）
     */
    public static function str_substr(string $str, int $start, ?int $length = null, string $encoding = 'UTF-8'): string
    {
        return mb_substr($str, $start, $length, $encoding);
    }

    /**
     * 字符串长度（支持中文）
     */
    public static function str_length(string $str, string $encoding = 'UTF-8'): int
    {
        return mb_strlen($str, $encoding);
    }

    /**
     * 字符串查找位置（支持中文）
     */
    public static function str_position(string $haystack, string $needle, int $offset = 0, string $encoding = 'UTF-8'): int|false
    {
        return mb_strpos($haystack, $needle, $offset, $encoding);
    }

    /**
     * 字符串替换（支持中文）
     */
    public static function str_replace_mb(string $search, string $replace, string $subject, string $encoding = 'UTF-8'): string
    {
        return mb_ereg_replace($search, $replace, $subject);
    }
}