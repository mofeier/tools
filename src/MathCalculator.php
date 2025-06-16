<?php

namespace Mofeier\Tools;

/**
 * 数学计算类
 * 所有函数名以math_开头
 * 支持高精度计算
 */
class MathCalculator
{
    /**
     * 高精度加法
     */
    public static function math_bcadd(string|int|float $left, string|int|float $right, int $scale = 2): string
    {
        return bcadd((string)$left, (string)$right, $scale);
    }

    /**
     * 高精度减法
     */
    public static function math_bcsub(string|int|float $left, string|int|float $right, int $scale = 2): string
    {
        return bcsub((string)$left, (string)$right, $scale);
    }

    /**
     * 高精度乘法
     */
    public static function math_bcmul(string|int|float $left, string|int|float $right, int $scale = 2): string
    {
        return bcmul((string)$left, (string)$right, $scale);
    }

    /**
     * 高精度除法
     */
    public static function math_bcdiv(string|int|float $left, string|int|float $right, int $scale = 2): string
    {
        if ((float)$right == 0) {
            throw new \InvalidArgumentException('Division by zero');
        }
        return bcdiv((string)$left, (string)$right, $scale);
    }

    /**
     * 高精度取模
     */
    public static function math_bcmod(string|int|float $left, string|int|float $right, int $scale = 0): string
    {
        if ((float)$right == 0) {
            throw new \InvalidArgumentException('Division by zero');
        }
        return bcmod((string)$left, (string)$right, $scale);
    }

    /**
     * 高精度幂运算
     */
    public static function math_bcpow(string|int|float $base, string|int|float $exponent, int $scale = 2): string
    {
        return bcpow((string)$base, (string)$exponent, $scale);
    }

    /**
     * 高精度平方根
     */
    public static function math_bcsqrt(string|int|float $operand, int $scale = 2): string
    {
        if ((float)$operand < 0) {
            throw new \InvalidArgumentException('Square root of negative number');
        }
        return bcsqrt((string)$operand, $scale);
    }

    /**
     * 高精度比较
     */
    public static function math_bccomp(string|int|float $left, string|int|float $right, int $scale = 2): int
    {
        return bccomp((string)$left, (string)$right, $scale);
    }

    /**
     * 普通加法
     */
    public static function math_add(int|float $left, int|float $right): int|float
    {
        return $left + $right;
    }

    /**
     * 普通减法
     */
    public static function math_sub(int|float $left, int|float $right): int|float
    {
        return $left - $right;
    }

    /**
     * 普通乘法
     */
    public static function math_mul(int|float $left, int|float $right): int|float
    {
        return $left * $right;
    }

    /**
     * 普通除法
     */
    public static function math_div(int|float $left, int|float $right): float
    {
        if ($right == 0) {
            throw new \InvalidArgumentException('Division by zero');
        }
        return $left / $right;
    }

    /**
     * 取模
     */
    public static function math_mod(int $left, int $right): int
    {
        if ($right == 0) {
            throw new \InvalidArgumentException('Division by zero');
        }
        return $left % $right;
    }

    /**
     * 幂运算
     */
    public static function math_pow(int|float $base, int|float $exponent): int|float
    {
        return pow($base, $exponent);
    }

    /**
     * 平方根
     */
    public static function math_sqrt(int|float $operand): float
    {
        if ($operand < 0) {
            throw new \InvalidArgumentException('Square root of negative number');
        }
        return sqrt($operand);
    }

    /**
     * 绝对值
     */
    public static function math_abs(int|float $number): int|float
    {
        return abs($number);
    }

    /**
     * 向上取整
     */
    public static function math_ceil(float $value): float
    {
        return ceil($value);
    }

    /**
     * 向下取整
     */
    public static function math_floor(float $value): float
    {
        return floor($value);
    }

    /**
     * 四舍五入
     */
    public static function math_round(float $value, int $precision = 0): float
    {
        return round($value, $precision);
    }

    /**
     * 最大值
     */
    public static function math_max(...$values): int|float
    {
        return max($values);
    }

    /**
     * 最小值
     */
    public static function math_min(...$values): int|float
    {
        return min($values);
    }

    /**
     * 随机数
     */
    public static function math_rand(int $min = 0, int $max = null): int
    {
        if ($max === null) {
            $max = getrandmax();
        }
        return rand($min, $max);
    }

    /**
     * 更好的随机数
     */
    public static function math_mt_rand(int $min = 0, int $max = null): int
    {
        if ($max === null) {
            $max = mt_getrandmax();
        }
        return mt_rand($min, $max);
    }

    /**
     * 正弦值
     */
    public static function math_sin(float $arg): float
    {
        return sin($arg);
    }

    /**
     * 余弦值
     */
    public static function math_cos(float $arg): float
    {
        return cos($arg);
    }

    /**
     * 正切值
     */
    public static function math_tan(float $arg): float
    {
        return tan($arg);
    }

    /**
     * 反正弦值
     */
    public static function math_asin(float $arg): float
    {
        return asin($arg);
    }

    /**
     * 反余弦值
     */
    public static function math_acos(float $arg): float
    {
        return acos($arg);
    }

    /**
     * 反正切值
     */
    public static function math_atan(float $arg): float
    {
        return atan($arg);
    }

    /**
     * 自然对数
     */
    public static function math_log(float $arg, float $base = M_E): float
    {
        return log($arg, $base);
    }

    /**
     * 以10为底的对数
     */
    public static function math_log10(float $arg): float
    {
        return log10($arg);
    }

    /**
     * e的幂
     */
    public static function math_exp(float $arg): float
    {
        return exp($arg);
    }

    /**
     * 角度转弧度
     */
    public static function math_deg2rad(float $number): float
    {
        return deg2rad($number);
    }

    /**
     * 弧度转角度
     */
    public static function math_rad2deg(float $number): float
    {
        return rad2deg($number);
    }

    /**
     * 计算百分比
     */
    public static function math_percentage(int|float $part, int|float $total, int $precision = 2): float
    {
        if ($total == 0) {
            return 0;
        }
        return round(($part / $total) * 100, $precision);
    }

    /**
     * 计算平均值
     */
    public static function math_average(array $numbers): float
    {
        if (empty($numbers)) {
            return 0;
        }
        return array_sum($numbers) / count($numbers);
    }

    /**
     * 计算中位数
     */
    public static function math_median(array $numbers): float
    {
        if (empty($numbers)) {
            return 0;
        }
        
        sort($numbers);
        $count = count($numbers);
        $middle = floor($count / 2);
        
        if ($count % 2 == 0) {
            return ($numbers[$middle - 1] + $numbers[$middle]) / 2;
        } else {
            return $numbers[$middle];
        }
    }

    /**
     * 计算方差
     */
    public static function math_variance(array $numbers): float
    {
        if (empty($numbers)) {
            return 0;
        }
        
        $mean = self::math_average($numbers);
        $sum = 0;
        
        foreach ($numbers as $number) {
            $sum += pow($number - $mean, 2);
        }
        
        return $sum / count($numbers);
    }

    /**
     * 计算标准差
     */
    public static function math_standard_deviation(array $numbers): float
    {
        return sqrt(self::math_variance($numbers));
    }

    /**
     * 阶乘
     */
    public static function math_factorial(int $n): int
    {
        if ($n < 0) {
            throw new \InvalidArgumentException('Factorial of negative number');
        }
        if ($n <= 1) {
            return 1;
        }
        
        $result = 1;
        for ($i = 2; $i <= $n; $i++) {
            $result *= $i;
        }
        return $result;
    }

    /**
     * 最大公约数
     */
    public static function math_gcd(int $a, int $b): int
    {
        while ($b != 0) {
            $temp = $b;
            $b = $a % $b;
            $a = $temp;
        }
        return abs($a);
    }

    /**
     * 最小公倍数
     */
    public static function math_lcm(int $a, int $b): int
    {
        return abs($a * $b) / self::math_gcd($a, $b);
    }
}