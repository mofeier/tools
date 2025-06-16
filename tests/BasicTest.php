<?php

namespace Mofeier\Tools\Tests;

use PHPUnit\Framework\TestCase;
use Mofeier\Tools\Tools;
use Mofeier\Tools\Message;
use Mofeier\Tools\StringConverter;
use Mofeier\Tools\Utils;
use Mofeier\Tools\MathCalculator;

class BasicTest extends TestCase
{
    public function testMessageBasicUsage()
    {
        $message = new Message();
        $result = $message->result();
        
        $this->assertIsArray($result);
        $this->assertEquals(2000, $result['code']);
        $this->assertEquals('Success', $result['msg']);
        $this->assertIsArray($result['data']);
    }

    public function testMessageChaining()
    {
        $message = new Message();
        $result = $message->setCode(200)->setMsg('测试成功')->setData(['test' => 'value'])->result();
        
        $this->assertEquals(200, $result['code']);
        $this->assertEquals('测试成功', $result['msg']);
        $this->assertEquals(['test' => 'value'], $result['data']);
    }

    public function testMessageStaticCall()
    {
        $result = Message::code(404)->result();
        
        $this->assertEquals(404, $result['code']);
        $this->assertIsString($result['msg']);
    }

    public function testMessageCreateWithFields()
    {
        $message = Message::create(['total' => 100, 'page' => 1]);
        $result = $message->result();
        
        $this->assertEquals(100, $result['total']);
        $this->assertEquals(1, $result['page']);
    }

    public function testMessageSetFields()
    {
        $message = new Message();
        $result = $message->setFields(['total' => 200, 'page' => 2])->result();
        
        $this->assertEquals(200, $result['total']);
        $this->assertEquals(2, $result['page']);
    }

    public function testMessageIndependentMethods()
    {
        $message = new Message();
        $message->set('custom', 'value');
        
        $this->assertEquals('value', $message->get('custom'));
        $this->assertTrue($message->has('custom'));
        
        $message->remove('custom');
        $this->assertFalse($message->has('custom'));
    }

    public function testMessageFieldReplacement()
    {
        $message = new Message();
        $result = $message->code(200)->replace(['code' => 'status', 'msg' => 'message'])->result();
        
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(200, $result['status']);
    }

    public function testMessageJsonOutput()
    {
        $message = new Message();
        $json = $message->code(200)->json();
        
        $this->assertIsString($json);
        $decoded = json_decode($json, true);
        $this->assertEquals(200, $decoded['code']);
    }

    public function testMessageCustomStatusCodes()
    {
        // 先清空自定义状态码
        \Mofeier\Tools\StatusCodes::clearCustomCodes();
        
        // 测试设置自定义状态码
        Message::setCustomCodes([9001 => '测试状态码']);
        
        // 测试状态码是否存在
        $this->assertTrue(Message::codeExists(9001));
        
        // 测试获取所有状态码
        $allCodes = Message::getAllCodes();
        $this->assertIsArray($allCodes);
        $this->assertArrayHasKey(9001, $allCodes);
    }

    public function testStringConverter()
    {
        $this->assertEquals(123, Tools::str_to_int('123'));
        $this->assertEquals(123.45, Tools::str_to_float('123.45'));
        $this->assertTrue(Tools::str_to_bool('true'));
        $this->assertEquals('user_name', Tools::str_camel_to_snake('userName'));
        $this->assertEquals('userName', Tools::str_snake_to_camel('user_name'));
    }

    public function testArrayToTree()
    {
        $flatArray = [
            ['id' => 1, 'parent_id' => 0, 'name' => '根节点'],
            ['id' => 2, 'parent_id' => 1, 'name' => '子节点1'],
            ['id' => 3, 'parent_id' => 1, 'name' => '子节点2']
        ];
        
        $tree = Tools::str_array_to_tree($flatArray);
        
        $this->assertIsArray($tree);
        $this->assertCount(1, $tree); // 只有一个根节点
        $this->assertEquals('根节点', $tree[0]['name']);
        $this->assertCount(2, $tree[0]['children']); // 根节点有两个子节点
    }

    public function testUtils()
    {
        $data = ['name' => '测试', 'value' => 123];
        
        // JSON测试
        $json = Tools::util_json_encode($data);
        $this->assertIsString($json);
        $decoded = Tools::util_json_decode($json);
        $this->assertEquals($data, $decoded);
        
        // Base64测试
        $base64 = Tools::util_base64_encode($data);
        $this->assertIsString($base64);
        $decodedBase64 = Tools::util_base64_decode($base64);
        $this->assertEquals($data, $decodedBase64);
        
        // UUID测试
        $uuid = Tools::util_generate_uuid();
        $this->assertIsString($uuid);
        $this->assertEquals(36, strlen($uuid)); // UUID标准长度
        
        // 验证测试
        $this->assertTrue(Tools::util_validate_email('test@example.com'));
        $this->assertFalse(Tools::util_validate_email('invalid-email'));
        $this->assertTrue(Tools::util_validate_mobile('13800138000'));
        $this->assertFalse(Tools::util_validate_mobile('12345'));
    }

    public function testMathCalculator()
    {
        // 高精度计算测试
        $this->assertEquals('0.30', Tools::math_bcadd('0.1', '0.2', 2));
        $this->assertEquals('0.03', Tools::math_bcmul('0.1', '0.3', 2));
        $this->assertEquals('0.333333', Tools::math_bcdiv('1', '3', 6));
        
        // 基本数学运算测试
        $this->assertEquals(30, Tools::math_add(10, 20));
        $this->assertEquals(256, Tools::math_pow(2, 8));
        $this->assertEquals(4, Tools::math_sqrt(16));
        
        // 统计函数测试
        $numbers = [1, 2, 3, 4, 5];
        $this->assertEquals(3, Tools::math_average($numbers));
        $this->assertEquals(3, Tools::math_median($numbers));
        $this->assertEquals(25, Tools::math_percentage(25, 100));
        $this->assertEquals(120, Tools::math_factorial(5));
        $this->assertEquals(6, Tools::math_gcd(12, 18));
        $this->assertEquals(36, Tools::math_lcm(12, 18));
    }

    public function testToolsHelp()
    {
        $help = Tools::help();
        
        $this->assertIsArray($help);
        $this->assertArrayHasKey('version', $help);
        $this->assertArrayHasKey('description', $help);
        $this->assertArrayHasKey('methods', $help);
        $this->assertEquals('1.0.0', $help['version']);
    }

    public function testToolsVersion()
    {
        $version = Tools::version();
        $this->assertEquals('1.0.0', $version);
    }

    public function testToolsMethods()
    {
        $methods = Tools::getMethods();
        
        $this->assertIsArray($methods);
        $this->assertArrayHasKey('message', $methods);
        $this->assertArrayHasKey('string', $methods);
        $this->assertArrayHasKey('utils', $methods);
        $this->assertArrayHasKey('math', $methods);
        
        $this->assertGreaterThan(0, count($methods['message']));
        $this->assertGreaterThan(0, count($methods['string']));
        $this->assertGreaterThan(0, count($methods['utils']));
        $this->assertGreaterThan(0, count($methods['math']));
    }
}