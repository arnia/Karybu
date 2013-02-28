<?php
require_once dirname(__FILE__) . '/../lib/Bootstrap.php';

class RandomGeneratorTest extends PHPUnit_Framework_TestCase
{

    public function testOne()
    {
        $output = RandomGenerator::generateOne();
        $this->assertRegExp('/^([a-zA-Z0-9]{10})$/', $output, 'First match did not happen');
        $output = RandomGenerator::generateOne(7, RandomGenerator::TYPE_ALPHA, 'blaXbla');
        $this->assertRegExp('/^bla([a-zA-Z]{7})bla$/', $output, 'Second match did not happen');
        $output = RandomGenerator::generateOne(6, RandomGenerator::TYPE_NUM, 'prefix-X-suffix', 2, '-');
        $this->assertRegExp('/^prefix-([0-9-]{8})-suffix$/', $output, 'Separator match did not happen');
        $output = RandomGenerator::generateOne(1200, RandomGenerator::TYPE_NUM);
        $this->assertEquals(1200, strlen($output), 'Long string generated correctly');
    }

    public function testMany()
    {
        $output = RandomGenerator::generate();
        $this->assertFalse(is_array($output), 'Output is array');
        $this->assertRegExp('/^([a-zA-Z0-9]{10})$/', $output, 'Single match not ok');
        $output = RandomGenerator::generate(3, 120, RandomGenerator::TYPE_ALPHANUM, 'start:X:end', 8, '-');
        $this->assertTrue(is_array($output), 'Not array');
        if (is_array($output)) $this->assertEquals(count($output), 3, 'Array count not 3');
        foreach ($output as $out) {
            $this->assertRegExp('/^start:([a-zA-Z0-9-]{134}):end$/', $out, 'Element not ok');
        }
    }

}