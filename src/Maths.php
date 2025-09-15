<?php

namespace Mofei;

/**
 * 数学计算工具类
 * 提供高精度数学计算功能，避免浮点数精度问题
 * 兼容PHP 7.4+
 */
class Maths
{
    /**
     * 高精度加法
     * @param string|int|float $num1 第一个数
     * @param string|int|float $num2 第二个数
     * @param int $scale 小数位数，默认为2
     * @return string
     */
    public static function add($num1, $num2, int $scale = 2): string
    {
        return bcadd((string)$num1, (string)$num2, $scale);
    }

    /**
     * 高精度减法
     * @param string|int|float $num1 被减数
     * @param string|int|float $num2 减数
     * @param int $scale 小数位数，默认为2
     * @return string
     */
    public static function sub($num1, $num2, int $scale = 2): string
    {
        return bcsub((string)$num1, (string)$num2, $scale);
    }

    /**
     * 高精度乘法
     * @param string|int|float $num1 第一个数
     * @param string|int|float $num2 第二个数
     * @param int $scale 小数位数，默认为2
     * @return string
     */
    public static function mul($num1, $num2, int $scale = 2): string
    {
        return bcmul((string)$num1, (string)$num2, $scale);
    }

    /**
     * 高精度除法
     * @param string|int|float $num1 被除数
     * @param string|int|float $num2 除数
     * @param int $scale 小数位数，默认为2
     * @return string
     * @throws \InvalidArgumentException 当除数为零时抛出异常
     */
    public static function div($num1, $num2, int $scale = 2): string
    {
        if (bccomp((string)$num2, '0', $scale) === 0) {
            throw new \InvalidArgumentException('Division by zero');
        }
        return bcdiv((string)$num1, (string)$num2, $scale);
    }

    /**
     * 高精度取模
     * @param string|int|float $num1 被除数
     * @param string|int|float $num2 除数
     * @param int $scale 小数位数，默认为0
     * @return string
     */
    public static function mod($num1, $num2, int $scale = 0): string
    {
        return bcmod((string)$num1, (string)$num2);
    }

    /**
     * 高精度幂运算
     * @param string|int|float $num 底数
     * @param string|int|float $exponent 指数
     * @param int $scale 小数位数，默认为2
     * @return string
     */
    public static function pow($num, $exponent, int $scale = 2): string
    {
        return bcpow((string)$num, (string)$exponent, $scale);
    }

    /**
     * 高精度平方根
     * @param string|int|float $num 数值
     * @param int $scale 小数位数，默认为2
     * @return string
     */
    public static function sqrt($num, int $scale = 2): string
    {
        return bcsqrt((string)$num, $scale);
    }

    /**
     * 比较两个数值的大小
     * @param string|int|float $left 左操作数
     * @param string|int|float $right 右操作数
     * @param int $scale 小数位数，默认为2
     * @return int 返回-1（小于）、0（等于）或1（大于）
     */
    public static function compare($left, $right, int $scale = 2): int
    {
        return bccomp((string)$left, (string)$right, $scale);
    }

    /**
     * 格式化数值
     * @param string|int|float $num 数值
     * @param int $decimals 小数位数
     * @param string $decimalPoint 小数点字符
     * @param string $thousandsSeparator 千位分隔符
     * @return string
     */
    public static function format($num, int $decimals = 2, string $decimalPoint = '.', string $thousandsSeparator = ','): string
    {
        return number_format((float)$num, $decimals, $decimalPoint, $thousandsSeparator);
    }

    /**
     * 四舍五入到指定位数
     * @param string|int|float $num 数值
     * @param int $precision 小数位数
     * @return float
     */
    public static function round($num, int $precision = 0): float
    {
        return round((float)$num, $precision);
    }

    /**
     * 检查数值是否为整数
     * @param mixed $value 要检查的值
     * @return bool
     */
    public static function isInteger($value): bool
    {
        return is_int($value) || (is_string($value) && ctype_digit($value));
    }

    /**
     * 检查数值是否为浮点数
     * @param mixed $value 要检查的值
     * @return bool
     */
    public static function isFloat($value): bool
    {
        return is_float($value) || (is_string($value) && preg_match('/^\d+\.\d+$/', $value));
    }

    /**
     * 检查数值是否为负数
     * @param string|int|float $num 数值
     * @return bool
     */
    public static function isNegative($num): bool
    {
        return bccomp((string)$num, '0', 10) < 0;
    }

    /**
     * 检查数值是否为正数
     * @param string|int|float $num 数值
     * @return bool
     */
    public static function isPositive($num): bool
    {
        return bccomp((string)$num, '0', 10) > 0;
    }

    /**
     * 计算数组元素的总和
     * @param array $numbers 数值数组
     * @param int $scale 小数位数，默认为2
     * @return string
     */
    public static function sum(array $numbers, int $scale = 2): string
    {
        $result = '0';
        foreach ($numbers as $number) {
            $result = bcadd($result, (string)$number, $scale);
        }
        return $result;
    }

    /**
     * 计算数组元素的平均值
     * @param array $numbers 数值数组
     * @param int $scale 小数位数，默认为2
     * @return string
     */
    public static function average(array $numbers, int $scale = 2): string
    {
        if (empty($numbers)) {
            return '0';
        }
        $sum = self::sum($numbers, $scale);
        return bcdiv($sum, (string)count($numbers), $scale);
    }

    /**
     * 确保数值在指定范围内
     * @param string|int|float $num 数值
     * @param string|int|float $min 最小值
     * @param string|int|float $max 最大值
     * @return string
     */
    public static function clamp($num, $min, $max): string
    {
        $num = (string)$num;
        $min = (string)$min;
        $max = (string)$max;
        
        if (bccomp($num, $min, 10) < 0) {
            return $min;
        }
        if (bccomp($num, $max, 10) > 0) {
            return $max;
        }
        return $num;
    }
}
